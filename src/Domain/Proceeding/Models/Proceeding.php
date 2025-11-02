<?php

declare(strict_types=1);

namespace Src\Domain\Proceeding\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Src\Domain\Process\Models\Process;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Shared\Traits\InteractsWithCustomMedia;

/**
 * @property-read int $id
 * @property-read int $process_id
 * @property-read string $description
 * @property-read Carbon $proceeding_date
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class Proceeding extends Model implements \Spatie\MediaLibrary\HasMedia
{
    use HasFactory;
    use InteractsWithCustomMedia;

    protected $fillable = [
        'process_id',
        'description',
        'proceeding_date',
    ];

    public function getMediaCollectionName(): string
    {
        return FileType::PROCEEDING_DOCUMENT->value;
    }

    /**
     * @return BelongsTo<Process, $this>
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    protected function casts(): array
    {
        return [
            'proceeding_date' => 'date',
        ];
    }
}
