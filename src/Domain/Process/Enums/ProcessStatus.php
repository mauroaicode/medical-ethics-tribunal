<?php

declare(strict_types=1);

namespace Src\Domain\Process\Enums;

enum ProcessStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';
}

