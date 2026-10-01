<?php

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Company profile for the SPA. Field names follow the approved registration
 * form / src/domain/types.ts. No sector field (PLAN-CONTRACT §2 Phase 2A).
 *
 * @mixin Organization
 */
class OrganizationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->display_name,
            'countryCode' => $this->country_code,
            'sizeBand' => $this->size_band,
            'registrationId' => $this->registration_id,
            'active' => $this->active,
        ];
    }
}
