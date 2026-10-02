<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Phase 2B — per-domain projection of a snapshot, for portfolio queries. */
class SubmissionDomainResult extends Model
{
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
