<?php

declare(strict_types=1);

namespace Src\Domain\Template\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Src\Domain\Process\Models\Process;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Shared\Traits\InteractsWithCustomMedia;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class Template extends Model implements \Spatie\MediaLibrary\HasMedia
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory, InteractsWithCustomMedia, SoftDeletes;

    public function getMediaCollectionName(): string
    {
        return FileType::TEMPLATE_FILE->value;
    }

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return HasMany<Process, $this>
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }
}
