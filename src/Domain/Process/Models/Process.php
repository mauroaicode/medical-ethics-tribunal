<?php

declare(strict_types=1);

namespace Src\Domain\Process\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Enums\ProcessStatus;
use Src\Domain\Template\Models\Template;

/**
 * @property-read int $id
 * @property-read int $complainant_id
 * @property-read int $doctor_id
 * @property-read int $magistrate_instructor_id
 * @property-read int $magistrate_ponente_id
 * @property-read int|null $template_id
 * @property-read string $name
 * @property-read string $process_number
 * @property-read Carbon $start_date
 * @property-read ProcessStatus $status
 * @property-read string $description
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class Process extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'complainant_id',
        'doctor_id',
        'magistrate_instructor_id',
        'magistrate_ponente_id',
        'template_id',
        'name',
        'process_number',
        'start_date',
        'status',
        'description',
    ];

    /**
     * @return BelongsTo<Complainant, $this>
     */
    public function complainant(): BelongsTo
    {
        return $this->belongsTo(Complainant::class);
    }

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * @return BelongsTo<Magistrate, $this>
     */
    public function magistrateInstructor(): BelongsTo
    {
        return $this->belongsTo(Magistrate::class, 'magistrate_instructor_id');
    }

    /**
     * @return BelongsTo<Magistrate, $this>
     */
    public function magistratePonente(): BelongsTo
    {
        return $this->belongsTo(Magistrate::class, 'magistrate_ponente_id');
    }

    /**
     * @return BelongsTo<Template, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return HasMany<Proceeding, $this>
     */
    public function proceedings(): HasMany
    {
        return $this->hasMany(Proceeding::class);
    }

    /**
     * Get all audit logs for this process.
     *
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'status' => ProcessStatus::class,
        ];
    }
}
