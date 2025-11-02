<?php

declare(strict_types=1);

namespace Src\Domain\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Doctor\QueryBuilders\DoctorQueryBuilder;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $specialty_id
 * @property-read MedicalSpecialty|null $specialty
 * @property-read User|null $user
 * @property-read string $faculty
 * @property-read string $medical_registration_number
 * @property-read string $medical_registration_place
 * @property-read Carbon $medical_registration_date
 * @property-read string|null $main_practice_company
 * @property-read string|null $other_practice_company
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @method static DoctorQueryBuilder query()
 * @method DoctorQueryBuilder withUser()
 * @method DoctorQueryBuilder withSpecialty()
 * @method DoctorQueryBuilder withRelations()
 * @method DoctorQueryBuilder withoutTrashed()
 * @method DoctorQueryBuilder orderedByCreatedAt()
 */
class Doctor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'faculty',
        'medical_registration_number',
        'medical_registration_place',
        'medical_registration_date',
        'main_practice_company',
        'other_practice_company',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<MedicalSpecialty, $this>
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    /**
     * @return HasMany<Process, $this>
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    /**
     * Get all audit logs for this doctor.
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
    public function newEloquentBuilder(mixed $query): DoctorQueryBuilder
    {
        return new DoctorQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'medical_registration_date' => 'date',
        ];
    }
}
