<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Pause or re-enable an organization (openapi-v1.yaml PATCH /staff/organizations/{id}/access).
 * Super Admins only. A paused organization's Champions can sign in but cannot
 * start or submit assessments (enforcement arrives with the assessment flow).
 */
class UpdateOrganizationAccessRequest extends FormRequest
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
            'active' => ['required', 'boolean'],
        ];
    }
}
