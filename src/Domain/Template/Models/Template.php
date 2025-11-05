<?php

declare(strict_types=1);

namespace Src\Domain\Template\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Shared\Traits\InteractsWithCustomMedia;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read string|null $google_drive_id
 * @property-read string|null $google_drive_file_id
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class Template extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithCustomMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'google_drive_id',
        'google_drive_file_id',
    ];

    public function getMediaCollectionName(): string
    {
        return FileType::TEMPLATE_FILE->value;
    }

    /**
     * @return HasMany<Process, $this>
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    /**
     * @return HasMany<ProcessTemplateDocument, $this>
     */
    public function processTemplateDocuments(): HasMany
    {
        return $this->hasMany(ProcessTemplateDocument::class);
    }
}
