<?php

declare(strict_types=1);

namespace Src\Domain\Doctor\Models;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string $specialty
 * @property-read string $faculty
 * @property-read string $medical_registration_number
 * @property-read string $medical_registration_place
 * @property-read Carbon $medical_registration_date
 * @property-read string|null $main_practice_company
 * @property-read string|null $other_practice_company
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialty',
        'faculty',
        'medical_registration_number',
        'medical_registration_place',
        'medical_registration_date',
        'main_practice_company',
        'other_practice_company',
    ];

    protected function casts(): array
    {
        return [
            'medical_registration_date' => 'date',
        ];
    }

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
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    /**
     * Get all audit logs for this doctor.
     *
     * @return MorphMany<AuditLog>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
