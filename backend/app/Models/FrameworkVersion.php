<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/** Phase 2B — a framework version. Once published it is immutable.
 *
 * @property string $id
 * @property string $semantic_version
 * @property string $status
 * @property string $title
 * @property string $content_sha256
 * @property string|null $approval_reference
 * @property array<string, string> $interpretations
 */
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

    /** @return HasMany<FrameworkDomain, $this> */
    public function domains(): HasMany
    {
        return $this->hasMany(FrameworkDomain::class)->orderBy('position');
    }

    /** @return HasMany<FrameworkQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(FrameworkQuestion::class);
    }

    /** @return HasMany<RecommendationAction, $this> */
    public function actions(): HasMany
    {
        return $this->hasMany(RecommendationAction::class);
    }

    /** @return HasMany<AssessmentCycle, $this> */
    public function cycles(): HasMany
    {
        return $this->hasMany(AssessmentCycle::class);
    }
}
