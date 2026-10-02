<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/** Phase 2B — idempotency receipt per actor/operation/key. */
class IdempotencyRequest extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime'];
}
