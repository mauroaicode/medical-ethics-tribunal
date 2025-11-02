<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'email_not_verified' => 'Your email address has not been verified.',
    'password_reset_subject' => 'Password Reset Code',
    'password_reset_line_1' => 'We received a request to reset the password for your account.',
    'password_reset_code' => 'Your verification code is: :code',
    'password_reset_line_2' => 'This code will expire in '.config('auth.expiration_time_code_forgot_password', 10).' minutes. If you did not request this change, please ignore this message.',
    'unauthorized' => 'You are not authorized to perform this action.',
];
