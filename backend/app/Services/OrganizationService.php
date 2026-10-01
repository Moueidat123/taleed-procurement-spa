<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates and updates the single company tied to a Champion (decisions.md
 * D-31): one Champion per company. A duplicate company name or registration id
 * within the same country is blocked with a "contact Taleed" message rather
 * than silently attaching to, or leaking, an existing company.
 */
class OrganizationService
{
    /**
     * Create the Champion's organization or update it in place. The uniqueness
     * check and write happen in one transaction so two concurrent first-time
     * profiles cannot both succeed.
     *
     * @param  array{displayName:string,countryCode:string,sizeBand:string,registrationId:?string}  $data
     */
    public function upsertForChampion(AppUser $champion, array $data): Organization
    {
        $country = strtoupper($data['countryCode']);
        $normalizedName = Organization::normalize($data['displayName']);
        $normalizedReg = $data['registrationId'] !== null && $data['registrationId'] !== ''
            ? Organization::normalize($data['registrationId'])
            : null;

        return DB::transaction(function () use ($champion, $data, $country, $normalizedName, $normalizedReg) {
            $existingId = $champion->organization_id;

            $this->guardDuplicates($country, $normalizedName, $normalizedReg, $existingId);

            $org = $existingId !== null
                ? Organization::query()->lockForUpdate()->findOrFail($existingId)
                : new Organization;

            $org->display_name = $data['displayName'];
            $org->normalized_name = $normalizedName;
            $org->country_code = $country;
            $org->size_band = $data['sizeBand'];
            $org->registration_id = $data['registrationId'] ?: null;
            $org->normalized_registration_id = $normalizedReg;
            if ($existingId === null) {
                $org->active = true;
                $org->is_test = false;
            }
            $org->save();

            if ($existingId === null) {
                $champion->forceFill(['organization_id' => $org->id])->save();
            }

            return $org;
        });
    }

    /**
     * Block a duplicate normalized name or registration id within the country,
     * ignoring the Champion's own organization on update.
     */
    private function guardDuplicates(
        string $country,
        string $normalizedName,
        ?string $normalizedReg,
        ?string $ignoreId,
    ): void {
        $nameClash = Organization::query()
            ->where('country_code', $country)
            ->where('normalized_name', $normalizedName)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        $regClash = $normalizedReg !== null && Organization::query()
            ->where('country_code', $country)
            ->where('normalized_registration_id', $normalizedReg)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        if ($nameClash || $regClash) {
            throw ValidationException::withMessages([
                'displayName' => __('This company appears to be registered already. Please contact Taleed.'),
            ]);
        }
    }
}
