<?php

declare(strict_types=1);

namespace Src\Domain\Session\Models;

use Database\Factories\SessionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\Session\QueryBuilders\SessionQueryBuilder;
use Src\Domain\User\Models\User;

/**
 * @property-read string $id
 * @property-read int|null $user_id
 * @property-read User|null $user
 * @property-read string|null $ip_address
 * @property-read string|null $user_agent
 * @property-read string|null $location
 * @property-read string $payload
 * @property-read int $last_activity
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 *
 * @method static SessionQueryBuilder query()
 * @method SessionQueryBuilder forUser(int $userId)
 * @method SessionQueryBuilder orderedByLastActivity()
 * @method SessionQueryBuilder active()
 */
class Session extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $table = 'sessions';

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'location',
        'payload',
        'last_activity',
        'login_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): SessionQueryBuilder
    {
        return new SessionQueryBuilder($query);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return SessionFactory::new();
    }

    protected function casts(): array
    {
        return [
            'last_activity' => 'integer',
        ];
    }
}
