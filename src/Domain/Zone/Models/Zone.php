<?php

declare(strict_types=1);

namespace Src\Domain\Zone\Models;

use Database\Factories\ZoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Src\Domain\Department\Models\Department;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $description
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class Zone extends Model
{
    /** @use HasFactory<ZoneFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'idZona');
    }
}

