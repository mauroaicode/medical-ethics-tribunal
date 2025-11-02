<?php

declare(strict_types=1);

namespace Src\Application\Shared\Traits;

use RuntimeException;

trait DoctorMagistratePasswordTrait
{
    /**
     * Get password for doctor or magistrate based on configuration
     *
     * @return array{password: string, should_send_email: bool}
     */
    private function getDoctorMagistratePassword(): array
    {
        $emailNotificationEnabled = config()->boolean('auth.doctor_magistrate.email_notification_enabled', false);

        if ($emailNotificationEnabled) {
            // Generate random password and send email
            return [
                'password' => $this->generateSecurePassword(),
                'should_send_email' => true,
            ];
        }

        // Use fixed password from configuration, no email
        $fixedPassword = config('auth.doctor_magistrate.fixed_password');

        if (empty($fixedPassword)) {
            throw new RuntimeException('DOCTOR_MAGISTRATE_FIXED_PASSWORD must be set when email_notification_enabled is false');
        }

        return [
            'password' => $fixedPassword,
            'should_send_email' => false,
        ];
    }
}
