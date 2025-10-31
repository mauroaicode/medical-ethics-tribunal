<?php

declare(strict_types=1);

namespace Src\Domain\Complainant\Models;

use Src\Domain\User\Models\User;
use Database\Factories\ComplainantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Src\Domain\City\Models\City;
use Src\Domain\Process\Models\Process;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $city_id
 * @property-read string|null $municipality
 * @property-read string|null $company
 * @property-read bool $is_anonymous
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class Complainant extends Model
{
    /** @use HasFactory<ComplainantFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'city_id',
        'municipality',
        'company',
        'is_anonymous',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
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
}

