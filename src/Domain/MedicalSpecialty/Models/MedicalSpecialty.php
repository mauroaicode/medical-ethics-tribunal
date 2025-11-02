<?php

declare(strict_types=1);

namespace Src\Domain\MedicalSpecialty\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\MedicalSpecialty\QueryBuilders\MedicalSpecialtyQueryBuilder;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 *
 * @method static MedicalSpecialtyQueryBuilder query()
 * @method MedicalSpecialtyQueryBuilder orderedByName()
 */
class MedicalSpecialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return HasMany<Doctor, $this>
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'specialty_id');
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): MedicalSpecialtyQueryBuilder
    {
        return new MedicalSpecialtyQueryBuilder($query);
    }
}
