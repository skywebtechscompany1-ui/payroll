<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the backup system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The storage disk to use for storing backups. This should be configured
    | in config/filesystems.php. Recommended to use a cloud storage
    | like s3 for production environments.
    |
    */
    'disk' => env('BACKUP_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Backup Directory
    |--------------------------------------------------------------------------
    |
    | The directory within the storage disk where backups will be stored.
    |
    */
    'directory' => env('BACKUP_DIRECTORY', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Backups
    |--------------------------------------------------------------------------
    |
    | The maximum number of backups to keep for each type. Older backups
    | will be automatically deleted when this limit is exceeded.
    |
    */
    'max_backups' => env('BACKUP_MAX_BACKUPS', 10),

    /*
    |--------------------------------------------------------------------------
    | Auto Backup Schedule
    |--------------------------------------------------------------------------
    |
    | Configure automatic backup schedules. These can be set up in your
    | app/Console/Kernel.php file.
    |
    | Example:
    | $schedule->job(new BackupJob('full'))->daily()->at('02:00');
    | $schedule->job(new BackupJob('database'))->daily()->at('03:00');
    |
    */
    'schedules' => [
        'full' => env('BACKUP_FULL_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
        'database' => env('BACKUP_DATABASE_SCHEDULE', '0 3 * * *'), // Daily at 3 AM
        'files' => env('BACKUP_FILES_SCHEDULE', '0 4 * * 0'), // Weekly on Sunday at 4 AM
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure backup notification settings.
    |
    */
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        'email' => env('BACKUP_NOTIFICATION_EMAIL', null),
        'on_success' => env('BACKUP_NOTIFY_ON_SUCCESS', false),
        'on_failure' => env('BACKUP_NOTIFY_ON_FAILURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Exclusions
    |--------------------------------------------------------------------------
    |
    | Files and directories to exclude from file backups.
    |
    */
    'exclude' => [
        'node_modules',
        'vendor',
        '.git',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/testing',
        'storage/app/backups',
        'bootstrap/cache',
        '.env',
        '.DS_Store',
        '*.log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Backup Options
    |--------------------------------------------------------------------------
    |
    | Database specific backup options.
    |
    */
    'database' => [
        'compress' => env('BACKUP_DATABASE_COMPRESS', true),
        'exclude_tables' => [
            // Tables to exclude from database backups
            // 'telescope_entries',
            // 'telescope_entries_tags',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security-related backup settings.
    |
    */
    'security' => [
        'encrypt_backups' => env('BACKUP_ENCRYPT', false),
        'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),
        'require_authentication' => env('BACKUP_REQUIRE_AUTH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | How long to keep backups before automatic deletion.
    |
    */
    'retention' => [
        'days' => env('BACKUP_RETENTION_DAYS', 30),
        'weekly' => env('BACKUP_RETENTION_WEEKS', 12),
        'monthly' => env('BACKUP_RETENTION_MONTHS', 12),
        'yearly' => env('BACKUP_RETENTION_YEARS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    |
    | Performance-related settings for backup operations.
    |
    */
    'performance' => [
        'timeout' => env('BACKUP_TIMEOUT', 3600), // 1 hour
        'memory_limit' => env('BACKUP_MEMORY_LIMIT', '512M'),
        'compression_level' => env('BACKUP_COMPRESSION_LEVEL', 6),
    ],
];