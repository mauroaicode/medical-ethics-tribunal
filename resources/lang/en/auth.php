<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'email_not_verified' => 'Your email address has not been verified.',
    'user_inactive' => 'Your account has been deactivated. Please contact the administrator.',
    'password_reset_subject' => 'Password Reset Code',
    'password_reset_line_1' => 'We received a request to reset the password for your account.',
    'password_reset_code' => 'Your verification code is: :code',
    'password_reset_line_2' => 'This code will expire in '.config('auth.expiration_time_code_forgot_password', 10).' minutes. If you did not request this change, please ignore this message.',
    'unauthorized' => 'You are not authorized to perform this action.',
    'account_created_subject' => 'Account Created - Access Credentials',
    'account_created_line_1' => 'An account has been created for you in '.config('app.name').'. Below you will find your temporary access credentials:',
    'account_created_warning' => '⚠️ For security reasons, change your password immediately after logging in for the first time.',
    'account_created_line_2' => 'If you did not expect to receive this email, please contact the system administrator.',
];
