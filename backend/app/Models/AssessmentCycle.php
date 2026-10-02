<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** Phase 2B — an assessment cycle pinned to one published framework version.
 *
 * @property string $id
 * @property string $framework_version_id
 * @property string $title
 * @property string $status
 * @property Carbon $opens_at
 * @property Carbon $closes_at
 * @property string $business_timezone
 * @property-read FrameworkVersion|null $frameworkVersion
 */
class AssessmentCycle extends Model
{
    use HasUlids;

    protected $guarded = ['open_flag'];

    protected $casts = ['opens_at' => 'datetime', 'closes_at' => 'datetime'];

    /** @return BelongsTo<FrameworkVersion, $this> */
    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && now()->between($this->opens_at, $this->closes_at);
    }
}
