<?php

namespace App\Http\Resources;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Staff-facing view of an application user for the Super Admin people & access
 * list (openapi-v1.yaml GET /staff/users). Never exposes secrets; `canExport`
 * is the effective permission the SPA renders.
 *
 * @mixin AppUser
 */
class StaffUserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'jobTitle' => $this->job_title,
            'role' => $this->role,
            'active' => $this->active,
            'canExport' => $this->role === 'admin' || ($this->role === 'analyst' && $this->can_export),
            'twoFactorEnabled' => $this->two_factor_confirmed_at !== null,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
