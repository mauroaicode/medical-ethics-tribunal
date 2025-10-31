<?php

declare(strict_types=1);

namespace Src\Domain\Shared\Enums;

enum FileType: string
{
    case PROFILE_IMAGE = 'profile_image';
    case TEMPLATE_FILE = 'template_file';
    case PROCEEDING_DOCUMENT = 'proceeding_document';
}

