<?php

declare(strict_types=1);

namespace Src\Domain\User\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get the label for the user status
     *
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => __('enums.user_status.active'),
            self::INACTIVE => __('enums.user_status.inactive'),
        };
    }
}
