<?php

declare(strict_types=1);

namespace Src\Domain\Magistrate\Models;

use Database\Factories\MagistrateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Models\User;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class Magistrate extends Model
{
    /** @use HasFactory<MagistrateFactory> */
    use HasFactory, SoftDeletes;

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
}
