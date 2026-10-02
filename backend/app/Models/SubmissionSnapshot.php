<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/** Phase 2B — immutable submitted result with canonical checksum.
 *
 * @property string $revision_id
 * @property int $overall_yes_count
 * @property float $overall_percent
 * @property string $band
 * @property array<string, mixed> $snapshot
 * @property string $canonical_sha256
 */
class SubmissionSnapshot extends Model
{
    protected $primaryKey = 'revision_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'submitted_at' => 'datetime', 'overall_percent' => 'float'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Submission snapshots are immutable.'));
        static::deleting(fn () => throw new LogicException('Submission snapshots are immutable.'));
    }
}
