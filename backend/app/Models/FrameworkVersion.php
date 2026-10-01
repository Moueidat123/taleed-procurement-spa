<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/** Phase 2B — a framework version. Once published it is immutable. */
class FrameworkVersion extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected $casts = ['interpretations' => 'array', 'published_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(function (FrameworkVersion $v) {
            if ($v->getOriginal('status') === 'published') {
                throw new LogicException('Published framework versions are immutable.');
            }
        });
        static::deleting(function (FrameworkVersion $v) {
            if ($v->status === 'published') {
                throw new LogicException('Published framework versions cannot be deleted.');
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function domains(): HasMany
    {
        return $this->hasMany(FrameworkDomain::class)->orderBy('position');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(FrameworkQuestion::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(RecommendationAction::class);
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(AssessmentCycle::class);
    }
}
