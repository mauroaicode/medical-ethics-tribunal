<?php

declare(strict_types=1);

namespace Src\Domain\ProcessTemplateDocument\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Process\Models\Process;
use Src\Domain\Template\Models\Template;

/**
 * @property-read int $id
 * @property-read int $process_id
 * @property-read int $template_id
 * @property-read string $google_drive_file_id
 * @property-read string $file_name
 * @property-read string|null $local_path
 * @property-read string $google_docs_name
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class ProcessTemplateDocument extends Model
{
    use HasFactory;

    protected $table = 'process_template_documents';

    protected $fillable = [
        'process_id',
        'template_id',
        'google_drive_file_id',
        'file_name',
        'local_path',
        'google_docs_name',
    ];

    /**
     * @return BelongsTo<Process, $this>
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * @return BelongsTo<Template, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get all audit logs for this document.
     *
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Database\Factories\ProcessTemplateDocumentFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\ProcessTemplateDocumentFactory::new();
    }
}
