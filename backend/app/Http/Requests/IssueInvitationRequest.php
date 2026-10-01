<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Issue a staff invitation (decisions.md D-10). Super Admins only. Role is
 * limited to the two staff roles; export only applies to Analysts.
 */
class IssueInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->role === 'admin' && $user->active;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'role' => ['required', Rule::in(['analyst', 'admin'])],
            'canExport' => ['sometimes', 'boolean'],
        ];
    }
}
