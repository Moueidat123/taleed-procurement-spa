<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Phase 2B — per-domain projection of a snapshot, for portfolio queries.
 *
 * @property string $revision_id
 * @property int $yes_count
 * @property int $score_percent
 * @property string $band
 * @property-read string $key
 * @property-read string $title
 */
class SubmissionDomainResult extends Model
{
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
