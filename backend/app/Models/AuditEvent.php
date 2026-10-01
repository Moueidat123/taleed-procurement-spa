<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only security and domain audit log. Records the actor, action,
 * target, organization and outcome with minimal metadata only — never
 * passwords, tokens or full answer payloads (03-DATABASE-SCHEMA.md).
 *
 * Rows are only ever inserted, never updated or deleted by application code.
 *
 * @property string $id
 * @property string $action
 */
class AuditEvent extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Append one audit row. Metadata must contain no secrets or answer content.
     *
     * @param  array<string, scalar|null>  $metadata
     */
    public static function record(
        string $action,
        ?string $actorId = null,
        ?string $targetType = null,
        ?string $targetId = null,
        ?string $organizationId = null,
        string $outcome = 'ok',
        array $metadata = [],
        ?string $ipAddress = null,
    ): self {
        return self::create([
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'organization_id' => $organizationId,
            'outcome' => $outcome,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => $ipAddress,
        ]);
    }
}
