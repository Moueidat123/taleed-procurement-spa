<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 2B — one answer row (revision/question). answer: true = yes,
 * false = no, NULL = unanswered (never treated as no). Written with upserts.
 */
class AssessmentAnswer extends Model
{
    protected $primaryKey = null;

    public $incrementing = false;

    const CREATED_AT = null;

    protected $guarded = [];

    protected $casts = ['answer' => 'boolean'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(FrameworkQuestion::class, 'question_id');
    }
}
