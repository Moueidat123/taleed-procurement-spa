<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Phase 2B — immutable framework content row (recommendation_actions).
 *
 * @property string $id
 * @property string $band
 * @property string $source_action_id
 * @property string $text
 * @property string $source_cell
 */
class RecommendationAction extends Model
{
    use HasUlids;

    protected $table = 'recommendation_actions';

    public $timestamps = false;

    protected $guarded = [];

    /** @return BelongsTo<FrameworkVersion, $this> */
    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }
}
