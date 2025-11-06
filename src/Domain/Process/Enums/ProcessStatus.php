<?php

declare(strict_types=1);

namespace Src\Domain\Process\Enums;

enum ProcessStatus: string
{
    case DRAFT = 'borrador';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';

    /**
     * Get the label for the process status
     *
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => __('enums.process_status.borrador'),
            self::IN_PROGRESS => __('enums.process_status.in_progress'),
            self::CLOSED => __('enums.process_status.closed'),
        };
    }
}
