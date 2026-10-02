<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The approved company profile fields (PLAN-CONTRACT §2 Phase 2A) — NO sector.
 * Used for both the initial profile (create) and later edits (PATCH). Only a
 * verified Champion may submit; privileged fields (active, is_test, authority
 * confirmation) are never settable from request input.
 */
class OrganizationProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Verified Champions only; staff manage organizations through other flows.
        return $user !== null
            && $user->role === 'champion'
            && $user->active
            && $user->email_verified_at !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'displayName' => ['required', 'string', 'min:2', 'max:200'],
            'countryCode' => ['required', 'string', 'size:2', 'alpha'],
            'sizeBand' => ['required', 'string', 'max:40'],
            'registrationId' => ['nullable', 'string', 'max:120'],
            'authorityConfirmed' => ['sometimes', 'boolean'],
        ];
    }
}
