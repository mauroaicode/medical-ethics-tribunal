<?php

declare(strict_types=1);

namespace Src\Domain\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\QueryBuilders\AuditLogQueryBuilder;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string $action
 * @property-read string $auditable_type
 * @property-read int $auditable_id
 * @property-read Model|null $auditable
 * @property-read array|null $old_values
 * @property-read array|null $new_values
 * @property-read string $ip_address
 * @property-read string|null $user_agent
 * @property-read string|null $location
 * @property-read Carbon $created_at
 *
 * @method static AuditLogQueryBuilder query()
 * @method AuditLogQueryBuilder forUser(int $userId)
 * @method AuditLogQueryBuilder forAction(string $action)
 * @method AuditLogQueryBuilder forAuditableType(string $auditableType)
 * @method AuditLogQueryBuilder orderedByCreatedAt()
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'location',
        'created_at',
        'context_action',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent auditable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): AuditLogQueryBuilder
    {
        return new AuditLogQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
