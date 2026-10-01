<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Change a staff user's access (openapi-v1.yaml PATCH /staff/users/{id}/access).
 * Super Admins only. At least one of active / canExport must be supplied; the
 * self and last-admin invariants are enforced by StaffAccessService.
 */
class UpdateStaffAccessRequest extends FormRequest
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
            'active' => ['sometimes', 'boolean'],
            'canExport' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->hasAny(['active', 'canExport'])) {
            abort(422, 'Provide at least one of active or canExport.');
        }
    }
}
