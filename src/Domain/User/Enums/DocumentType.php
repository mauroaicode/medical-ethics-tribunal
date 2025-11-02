<?php

declare(strict_types=1);

namespace Src\Domain\User\Enums;

enum DocumentType: string
{
    case CEDULA_CIUDADANIA = 'cedula_ciudadania';
    case CEDULA_EXTRANJERIA = 'cedula_extranjeria';

    /**
     * Get the label for the document type
     *
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::CEDULA_CIUDADANIA => __('enums.document_type.cedula_ciudadania'),
            self::CEDULA_EXTRANJERIA => __('enums.document_type.cedula_extranjeria'),
        };
    }
}
