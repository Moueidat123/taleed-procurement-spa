<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\CurrentUserResource;
use App\Models\AppUser;
use App\Models\AuditEvent;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Champion self-registration (decisions.md D-31).
 *
 * Creates an unverified Champion account, issues a six-digit email code and
 * signs the user in so they can complete verification and their company
 * profile. Privileged fields (role, organization, export, verification,
 * active) are set by server code only — never from request input.
 */
class RegisterController
{
    public function __invoke(RegisterRequest $request, EmailVerificationService $verification): CurrentUserResource
    {
        $email = Str::lower($request->string('email')->trim()->value());

        // Non-enumerating at the API envelope level: a duplicate email returns a
        // generic validation error, not a distinct "already exists" signal.
        if (AppUser::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => __('We could not complete registration with those details.'),
            ]);
        }

        $user = new AppUser;
        $user->fill([
            'name' => $request->string('name')->trim()->value(),
            'email' => $email,
            'job_title' => $request->string('job_title')->trim()->value(),
        ]);
        // Explicit, never mass assigned.
        $user->password = $request->string('password')->value();
        $user->role = 'champion';
        $user->organization_id = null;
        $user->can_export = false;
        $user->active = true;
        $user->email_verified_at = null;
        $user->save();

        $verification->issue($user);

        AuditEvent::record(
            action: 'champion.registered',
            actorId: $user->id,
            targetType: 'app_user',
            targetId: $user->id,
            ipAddress: $request->ip(),
        );

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return new CurrentUserResource($user->refresh());
    }
}
