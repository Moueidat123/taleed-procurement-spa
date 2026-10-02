<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

/** Phase 2B — a numbered draft or submitted revision. Submitted revisions are immutable. */
class AssessmentRevision extends Model
{
    use HasUlids;

    protected $guarded = ['open_draft_for'];

    protected $casts = ['submitted_at' => 'datetime', 'lock_version' => 'integer', 'revision_number' => 'integer'];

    protected static function booted(): void
    {
        static::updating(function (AssessmentRevision $r) {
            if ($r->getOriginal('status') === 'submitted') {
                throw new LogicException('Submitted revisions are immutable.');
            }
        });
        static::deleting(fn () => throw new LogicException('Revisions are never deleted.'));
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_revision_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class, 'revision_id');
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(SubmissionSnapshot::class, 'revision_id');
    }

    public function domainResults(): HasMany
    {
        return $this->hasMany(SubmissionDomainResult::class, 'revision_id');
    }
}
