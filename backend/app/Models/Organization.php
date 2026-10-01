<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A participating company. Created only through the authorized registration /
 * profile flow (decisions.md D-31). Privileged fields are never mass assigned
 * from request input; the controllers set them explicitly.
 *
 * @property string $id
 * @property string $display_name
 * @property bool $active
 */
class Organization extends Model
{
    use HasUlids;

    /** @var list<string> */
    protected $fillable = ['display_name', 'country_code', 'size_band', 'registration_id'];

    protected function casts(): array
    {
        return [
            'authority_confirmed_at' => 'datetime',
            'active' => 'boolean',
            'is_test' => 'boolean',
        ];
    }

    /** @return HasMany<AppUser, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(AppUser::class, 'organization_id');
    }

    /** Lower-cased, single-spaced form used for duplicate detection. */
    public static function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }
}
