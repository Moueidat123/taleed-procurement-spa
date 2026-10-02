<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Phase 2B — one assessment per organization per cycle; lock target for revisions. */
class Assessment extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AssessmentCycle::class, 'cycle_id');
    }

    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(AssessmentRevision::class)->orderBy('revision_number');
    }

    public function currentSubmission(): BelongsTo
    {
        return $this->belongsTo(AssessmentRevision::class, 'current_submission_id');
    }
}
