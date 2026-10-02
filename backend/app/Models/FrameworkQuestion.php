<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Phase 2B — immutable framework content row (framework_questions).
 *
 * @property string $id
 * @property string $source_question_id
 * @property string $text
 * @property string $source_cell
 * @property int $position
 */
class FrameworkQuestion extends Model
{
    use HasUlids;

    protected $table = 'framework_questions';

    public $timestamps = false;

    protected $guarded = [];

    /** @return BelongsTo<FrameworkVersion, $this> */
    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }
}
