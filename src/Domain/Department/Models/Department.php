<?php

declare(strict_types=1);

namespace Src\Domain\Department\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Src\Domain\City\Models\City;
use Src\Domain\Zone\Models\Zone;

/**
 * @property-read int $id
 * @property-read string $codigo
 * @property-read string $descripcion
 * @property-read int $idZona
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class Department extends Model
{
    protected $fillable = [
        'codigo',
        'descripcion',
        'idZona',
    ];

    /**
     * @return BelongsTo<Zone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'idZona');
    }

    /**
     * @return HasMany<City, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'iddepartamento');
    }
}

