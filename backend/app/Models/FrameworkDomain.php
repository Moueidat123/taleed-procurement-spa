<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Phase 2B — immutable framework content row (framework_domains).
 *
 * @property string $id
 * @property string $key
 * @property string $title
 * @property int $position
 */
class FrameworkDomain extends Model
{
    use HasUlids;

    protected $table = 'framework_domains';

    public $timestamps = false;

    protected $guarded = [];

    /** @return BelongsTo<FrameworkVersion, $this> */
    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }
}
