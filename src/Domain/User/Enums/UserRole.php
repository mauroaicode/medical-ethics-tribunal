<?php

declare(strict_types=1);

namespace Src\Domain\User\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case SECRETARY = 'secretary';

    /**
     * Get the label for the role
     *
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('enums.user_role.super_admin'),
            self::ADMIN => __('enums.user_role.admin'),
            self::SECRETARY => __('enums.user_role.secretary'),
        };
    }

    /**
     * Get label for a role by string value
     */
    public static function getLabelFor(string $roleName): ?string
    {
        $role = self::tryFrom($roleName);

        return $role?->getLabel();
    }

    /**
     * Get all role values as array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role values that can create users
     *
     * @return array<string>
     */
    public static function canCreateUsers(): array
    {
        return [
            self::SUPER_ADMIN->value,
            self::ADMIN->value,
        ];
    }
}
