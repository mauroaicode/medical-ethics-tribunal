<?php

declare(strict_types=1);

namespace Src\Domain\City\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Department\Models\Department;

/**
 * @property-read int $id
 * @property-read string $codigo
 * @property-read int $iddepartamento
 * @property-read string $descripcion
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class City extends Model
{
    protected $fillable = [
        'codigo',
        'iddepartamento',
        'descripcion',
    ];

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'iddepartamento');
    }

    /**
     * @return HasMany<Complainant, $this>
     */
    public function complainants(): HasMany
    {
        return $this->hasMany(Complainant::class);
    }
}

