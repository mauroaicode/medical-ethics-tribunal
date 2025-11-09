<?php

return [
    'email_subject' => 'Código de Verificación para Acción Crítica',
    'email_line_1' => 'Has solicitado realizar una acción crítica: :action. Para continuar, necesitas verificar tu identidad con el siguiente código.',
    'email_line_2' => 'Este código expirará en :minutes minutos. No compartas este código con nadie.',
    'email_warning' => 'Si no solicitaste esta acción, ignora este mensaje de forma segura. Tu cuenta permanecerá segura.',
    'code_sent' => 'Código de verificación enviado a tu correo electrónico.',
    'code_sent_please_verify' => 'Se ha enviado un código de verificación a tu correo electrónico para realizar la acción: :action. Por favor, ingresa el código para continuar.',
    'code_verified' => 'Código verificado correctamente.',
    'code_invalid' => 'El código proporcionado es inválido o ha expirado.',
    'code_invalid_with_attempts' => 'El código proporcionado es inválido o ha expirado. :attempts_text',
    'attempts_remaining_singular' => 'Te queda :count intento',
    'attempts_remaining_plural' => 'Te quedan :count intentos',
    'blocked' => 'Tu sesión ha sido bloqueada por :minutes minutos debido a múltiples intentos fallidos. Por favor, intenta nuevamente más tarde.',
    'verification_required' => 'Debes verificar tu identidad con un código para realizar la acción: :action. Por favor, solicita un código primero.',
    'unauthorized' => 'No tienes autorización para realizar esta acción.',
    'actions' => [
        'process.update' => 'Editar Proceso',
        'process.delete' => 'Eliminar Proceso',
    ],
];

