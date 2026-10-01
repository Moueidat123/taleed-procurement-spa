<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Champion self-registration (decisions.md D-31). Only these allow-listed
 * fields are accepted; role, organization, export permission and verification
 * state are never settable from request input (mass-assignment protection).
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint; throttled by the 'register' limiter.
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'job_title' => ['required', 'string', 'min:2', 'max:160'],
            // Approved rule: 12+ chars with upper, lower and a digit.
            'password' => ['required', 'string', 'confirmed',
                Password::min(12)->mixedCase()->numbers()],
            // Consent to the prototype/privacy notice must be explicitly true.
            'consent' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'consent.accepted' => 'You must accept the privacy notice to register.',
        ];
    }
}
