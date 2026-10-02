<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/** Phase 2B — transactional outbox event, written in the same transaction as the change. */
class OutboxEvent extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'available_at' => 'datetime', 'delivered_at' => 'datetime'];
}
