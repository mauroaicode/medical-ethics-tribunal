<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Step-Up Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file manages the step-up authentication settings
    | for sensitive operations like editing and deleting processes.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | OTP Code Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the OTP (One-Time Password) code generation
    | and validation.
    |
    */

    'code' => [
        // Validity duration in minutes
        'validity_minutes' => (int) env('STEP_UP_CODE_VALIDITY_MINUTES', 5),

        // Code length (number of digits)
        'length' => (int) env('STEP_UP_CODE_LENGTH', 6),

        // Cache key prefix for OTP codes
        'cache_key_prefix' => env('STEP_UP_CODE_CACHE_PREFIX', 'step_up_code'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how long a verified action remains valid
    | before requiring a new code.
    |
    */

    'verification' => [
        'validity_minutes' => (int) env('STEP_UP_VERIFICATION_VALIDITY_MINUTES', 10),
        'cache_key_prefix' => env('STEP_UP_VERIFICATION_CACHE_PREFIX', 'step_up_verified'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attempts Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the maximum number of failed attempts before
    | blocking the session.
    |
    */

    'attempts' => [
        'duration_minutes' => (int) env('STEP_UP_ATTEMPTS_DURATION', 10),
        'max_attempts' => (int) env('STEP_UP_MAX_ATTEMPTS', 3),
        'cache_key_prefix' => env('STEP_UP_ATTEMPTS_CACHE_PREFIX', 'step_up_attempts'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Block Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the session blocking behavior after
    | exceeding maximum attempts.
    |
    */

    'block' => [
        'duration_minutes' => (int) env('STEP_UP_BLOCK_DURATION_MINUTES', 60),
    ],
];

