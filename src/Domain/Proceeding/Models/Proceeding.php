<?php

declare(strict_types=1);

namespace Src\Domain\Proceeding\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Src\Domain\Proceeding\QueryBuilders\ProceedingQueryBuilder;
use Src\Domain\Process\Models\Process;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Shared\Traits\InteractsWithCustomMedia;

/**
 * @property-read int $id
 * @property-read int $process_id
 * @property-read string $name
 * @property-read string $description
 * @property-read Carbon $proceeding_date
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Process $process
 *
 * @method static ProceedingQueryBuilder query()
 * @method ProceedingQueryBuilder forProcess(int $processId)
 * @method ProceedingQueryBuilder withProcess()
 * @method ProceedingQueryBuilder orderedByProceedingDate()
 */
class Proceeding extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithCustomMedia;

    protected $fillable = [
        'process_id',
        'name',
        'description',
        'proceeding_date',
    ];

    public function getMediaCollectionName(): string
    {
        return FileType::PROCEEDING_DOCUMENT->value;
    }

    /**
     * Register media collections.
     * Each proceeding has a single PDF file.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->getMediaCollectionName())
            ->singleFile();
    }

    /**
     * @return BelongsTo<Process, $this>
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): ProceedingQueryBuilder
    {
        return new ProceedingQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'proceeding_date' => 'date',
        ];
    }
}
