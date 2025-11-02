<?php

return [
    'failed' => 'Las credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña proporcionada es incorrecta.',
    'throttle' => 'Demasiados intentos de acceso. Por favor intente nuevamente en :seconds segundos.',
    'email_not_verified' => 'Su correo electrónico no ha sido verificado.',
    'user_inactive' => 'Su cuenta ha sido desactivada. Contacte al administrador.',
    'password_reset_subject' => 'Código de Restablecimiento de Contraseña',
    'password_reset_line_1' => 'Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.',
    'password_reset_code' => 'Tu código de verificación es: :code',
    'password_reset_line_2' => 'Este código expirará en '.config('auth.expiration_time_code_forgot_password', 10).' minutos. Si no solicitaste este cambio, ignora este mensaje.',
    'unauthorized' => 'No tienes autorización para realizar esta acción.',
];
