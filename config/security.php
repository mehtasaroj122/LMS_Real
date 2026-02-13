<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting behavior for login attempts and account lockout.
    |
    */

    'rate_limiting' => [
        /*
         * Maximum number of failed login attempts before account is locked
         * Default: 5 attempts
         */
        'max_attempts' => env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),

        /*
         * Lockout duration in minutes
         * Default: 60 minutes (1 hour)
         */
        'lockout_duration' => env('SECURITY_LOCKOUT_DURATION', 60),

        /*
         * Enable/disable rate limiting
         * Default: true (enabled)
         */
        'enabled' => env('SECURITY_RATE_LIMITING_ENABLED', true),

        /*
         * Enable email unlock links
         * Default: true (enabled)
         */
        'email_unlock_enabled' => env('SECURITY_EMAIL_UNLOCK_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Event Logging
    |--------------------------------------------------------------------------
    |
    | Log security-related events for audit trails
    |
    */

    'logging' => [
        /*
         * Log failed login attempts
         */
        'log_failed_attempts' => env('SECURITY_LOG_FAILED_ATTEMPTS', true),

        /*
         * Log account lockouts
         */
        'log_lockouts' => env('SECURITY_LOG_LOCKOUTS', true),

        /*
         * Log unlock events
         */
        'log_unlocks' => env('SECURITY_LOG_UNLOCKS', true),
    ],
];
