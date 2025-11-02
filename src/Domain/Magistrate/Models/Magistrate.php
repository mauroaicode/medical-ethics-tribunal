<?php

declare(strict_types=1);

namespace Src\Domain\Magistrate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Magistrate\QueryBuilders\MagistrateQueryBuilder;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read User|null $user
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @method static MagistrateQueryBuilder query()
 * @method MagistrateQueryBuilder withUser()
 * @method MagistrateQueryBuilder withRelations()
 * @method MagistrateQueryBuilder withoutTrashed()
 * @method MagistrateQueryBuilder orderedByCreatedAt()
 */
class Magistrate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Process, $this>
     */
    public function processesAsInstructor(): HasMany
    {
        return $this->hasMany(Process::class, 'magistrate_instructor_id');
    }

    /**
     * @return HasMany<Process, $this>
     */
    public function processesAsPonente(): HasMany
    {
        return $this->hasMany(Process::class, 'magistrate_ponente_id');
    }

    /**
     * Get all audit logs for this magistrate.
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
    public function newEloquentBuilder(mixed $query): MagistrateQueryBuilder
    {
        return new MagistrateQueryBuilder($query);
    }
}
