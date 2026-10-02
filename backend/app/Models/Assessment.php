<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Phase 2B — one assessment per organization per cycle; lock target for revisions.
 *
 * @property string $id
 * @property string $organization_id
 * @property string $cycle_id
 * @property string $framework_version_id
 * @property string|null $current_submission_id
 * @property-read AssessmentCycle $cycle
 * @property-read Organization $organization
 * @property-read FrameworkVersion $frameworkVersion
 */
class Assessment extends Model
{
    use HasUlids;

    protected $guarded = [];

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<AssessmentCycle, $this> */
    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AssessmentCycle::class, 'cycle_id');
    }

    /** @return BelongsTo<FrameworkVersion, $this> */
    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }

    /** @return HasMany<AssessmentRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(AssessmentRevision::class)->orderBy('revision_number');
    }

    /** @return BelongsTo<AssessmentRevision, $this> */
    public function currentSubmission(): BelongsTo
    {
        return $this->belongsTo(AssessmentRevision::class, 'current_submission_id');
    }
}
