<?php

namespace App\Http\Resources;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimal identity for the SPA. Field names follow src/domain/types.ts `User`.
 *
 * @mixin AppUser
 */
class CurrentUserResource extends JsonResource
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
            'orgId' => $this->organization_id,
            'verified' => $this->email_verified_at !== null,
            'active' => $this->active,
            'canExport' => $this->role === 'admin' || ($this->role === 'analyst' && $this->can_export),
            'twoFactorEnabled' => $this->two_factor_confirmed_at !== null,
        ];
    }
}
