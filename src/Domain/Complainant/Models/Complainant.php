<?php

declare(strict_types=1);

namespace Src\Domain\Complainant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\City\Models\City;
use Src\Domain\Complainant\QueryBuilders\ComplainantQueryBuilder;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $city_id
 * @property-read string|null $municipality
 * @property-read string|null $company
 * @property-read bool $is_anonymous
 * @property-read User|null $user
 * @property-read City|null $city
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @method static ComplainantQueryBuilder query()
 * @method ComplainantQueryBuilder withUser()
 * @method ComplainantQueryBuilder withCity()
 * @method ComplainantQueryBuilder withRelations()
 * @method ComplainantQueryBuilder withoutTrashed()
 * @method ComplainantQueryBuilder orderedByCreatedAt()
 */
class Complainant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'city_id',
        'municipality',
        'company',
        'is_anonymous',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return HasMany<Process, $this>
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    /**
     * Get all audit logs for this complainant.
     *
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): ComplainantQueryBuilder
    {
        return new ComplainantQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }
}
