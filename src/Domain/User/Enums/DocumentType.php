<?php

declare(strict_types=1);

namespace Src\Domain\User\Enums;

enum DocumentType: string
{
    case CEDULA_CIUDADANIA = 'cedula_ciudadania';
    case CEDULA_EXTRANJERIA = 'cedula_extranjeria';
}

