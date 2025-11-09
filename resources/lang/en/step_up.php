<?php

return [
    'email_subject' => 'Verification Code for Critical Action',
    'email_line_1' => 'You have requested to perform a critical action: :action. To continue, you need to verify your identity with the following code.',
    'email_line_2' => 'This code will expire in :minutes minutes. Do not share this code with anyone.',
    'email_warning' => 'If you did not request this action, safely ignore this message. Your account will remain secure.',
    'code_sent' => 'Verification code sent to your email address.',
    'code_sent_please_verify' => 'A verification code has been sent to your email address to perform the action: :action. Please enter the code to continue.',
    'code_verified' => 'Code verified successfully.',
    'code_invalid' => 'The provided code is invalid or has expired.',
    'code_invalid_with_attempts' => 'The provided code is invalid or has expired. :attempts_text',
    'attempts_remaining_singular' => 'You have :count attempt remaining',
    'attempts_remaining_plural' => 'You have :count attempts remaining',
    'blocked' => 'Your session has been blocked for :minutes minutes due to multiple failed attempts. Please try again later.',
    'verification_required' => 'You must verify your identity with a code to perform the action: :action. Please request a code first.',
    'unauthorized' => 'You are not authorized to perform this action.',
    'actions' => [
        'process.update' => 'Edit Process',
        'process.delete' => 'Delete Process',
    ],
];

