<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Phase 2B — an assessment cycle pinned to one published framework version. */
class AssessmentCycle extends Model
{
    use HasUlids;

    protected $guarded = ['open_flag'];

    protected $casts = ['opens_at' => 'datetime', 'closes_at' => 'datetime'];

    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && now()->between($this->opens_at, $this->closes_at);
    }
}
