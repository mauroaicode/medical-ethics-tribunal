<?php

declare(strict_types=1);

namespace Src\Domain\SessionBlock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\SessionBlock\QueryBuilders\SessionBlockQueryBuilder;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string|null $session_id
 * @property-read string $ip_address
 * @property-read string|null $user_agent
 * @property-read string $action
 * @property-read Carbon $blocked_until
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read User $user
 *
 * @method static SessionBlockQueryBuilder query()
 * @method SessionBlockQueryBuilder forUser(int $userId)
 * @method SessionBlockQueryBuilder forAction(string $action)
 * @method SessionBlockQueryBuilder active()
 * @method SessionBlock|null activeForUserAndAction(int $userId, string $action)
 * @method SessionBlockQueryBuilder orderedByBlockedUntil()
 */
class SessionBlock extends Model
{
    use HasFactory;

    protected $table = 'session_blocks';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'action',
        'blocked_until',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the block is still active
     */
    public function isActive(): bool
    {
        return $this->blocked_until->isFuture();
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): SessionBlockQueryBuilder
    {
        return new SessionBlockQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'blocked_until' => 'datetime',
        ];
    }
}
