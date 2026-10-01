<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Accept a staff invitation (decisions.md D-10). Public endpoint, throttled by
 * the 'invitation' limiter. The caller proves possession of the emailed token
 * and sets their own name and password. Email and role come from the
 * invitation, never from request input.
 */
class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:64'],
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'password' => ['required', 'string', 'confirmed',
                Password::min(12)->mixedCase()->numbers()],
        ];
    }
}
