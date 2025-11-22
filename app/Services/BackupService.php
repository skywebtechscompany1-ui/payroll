<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;
use Exception;

class BackupService
{
    /**
     * Storage disk for backups
     */
    protected string $disk;

    /**
     * Backup directory
     */
    protected string $backupDirectory;

    /**
     * Maximum number of backups to keep
     */
    protected int $maxBackups;

    public function __construct()
    {
        $this->disk = config('backup.disk', 'local');
        $this->backupDirectory = config('backup.directory', 'backups');
        $this->maxBackups = config('backup.max_backups', 10);
    }

    /**
     * Create a full system backup
     */
    public function createFullBackup(string $description = null): array
    {
        $backupName = 'full_backup_' . Carbon::now()->format('Y_m_d_His');
        $tempDir = storage_path("app/temp_backups/{$backupName}");

        try {
            // Create temporary directory
            File::makeDirectory($tempDir, 0755, true, true);

            // Backup database
            $databaseFile = $this->backupDatabase($tempDir);

            // Backup files
            $this->backupFiles($tempDir);

            // Create backup metadata
            $this->createMetadata($tempDir, $backupName, $description, 'full');

            // Create zip archive
            $zipFile = $this->createZipArchive($tempDir, $backupName);

            // Move to backup directory
            $finalPath = $this->moveBackupToStorage($zipFile, $backupName, 'full');

            // Clean up temporary directory
            File::deleteDirectory($tempDir);

            // Clean up old backups
            $this->cleanupOldBackups('full');

            return [
                'success' => true,
                'backup_name' => $backupName,
                'file_path' => $finalPath,
                'file_size' => Storage::disk($this->disk)->size($finalPath),
                'created_at' => Carbon::now()->toISOString(),
                'type' => 'full',
                'description' => $description,
            ];

        } catch (Exception $e) {
            // Clean up on failure
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            throw new Exception("Backup failed: " . $e->getMessage());
        }
    }

    /**
     * Create a database-only backup
     */
    public function createDatabaseBackup(string $description = null): array
    {
        $backupName = 'database_backup_' . Carbon::now()->format('Y_m_d_His');
        $tempDir = storage_path("app/temp_backups/{$backupName}");

        try {
            // Create temporary directory
            File::makeDirectory($tempDir, 0755, true, true);

            // Backup database
            $databaseFile = $this->backupDatabase($tempDir);

            // Create backup metadata
            $this->createMetadata($tempDir, $backupName, $description, 'database');

            // Create zip archive
            $zipFile = $this->createZipArchive($tempDir, $backupName);

            // Move to backup directory
            $finalPath = $this->moveBackupToStorage($zipFile, $backupName, 'database');

            // Clean up temporary directory
            File::deleteDirectory($tempDir);

            // Clean up old backups
            $this->cleanupOldBackups('database');

            return [
                'success' => true,
                'backup_name' => $backupName,
                'file_path' => $finalPath,
                'file_size' => Storage::disk($this->disk)->size($finalPath),
                'created_at' => Carbon::now()->toISOString(),
                'type' => 'database',
                'description' => $description,
            ];

        } catch (Exception $e) {
            // Clean up on failure
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            throw new Exception("Database backup failed: " . $e->getMessage());
        }
    }

    /**
     * Create a files-only backup
     */
    public function createFilesBackup(string $description = null): array
    {
        $backupName = 'files_backup_' . Carbon::now()->format('Y_m_d_His');
        $tempDir = storage_path("app/temp_backups/{$backupName}");

        try {
            // Create temporary directory
            File::makeDirectory($tempDir, 0755, true, true);

            // Backup files
            $this->backupFiles($tempDir);

            // Create backup metadata
            $this->createMetadata($tempDir, $backupName, $description, 'files');

            // Create zip archive
            $zipFile = $this->createZipArchive($tempDir, $backupName);

            // Move to backup directory
            $finalPath = $this->moveBackupToStorage($zipFile, $backupName, 'files');

            // Clean up temporary directory
            File::deleteDirectory($tempDir);

            // Clean up old backups
            $this->cleanupOldBackups('files');

            return [
                'success' => true,
                'backup_name' => $backupName,
                'file_path' => $finalPath,
                'file_size' => Storage::disk($this->disk)->size($finalPath),
                'created_at' => Carbon::now()->toISOString(),
                'type' => 'files',
                'description' => $description,
            ];

        } catch (Exception $e) {
            // Clean up on failure
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            throw new Exception("Files backup failed: " . $e->getMessage());
        }
    }

    /**
     * Backup the database
     */
    protected function backupDatabase(string $tempDir): string
    {
        $databaseFile = $tempDir . '/database.sql';

        // Get database configuration
        $database = config('database.connections.' . config('database.default'));

        switch ($database['driver']) {
            case 'mysql':
                $this->backupMySQL($database, $databaseFile);
                break;
            case 'pgsql':
                $this->backupPostgreSQL($database, $databaseFile);
                break;
            case 'sqlite':
                $this->backupSQLite($database, $databaseFile);
                break;
            default:
                throw new Exception("Unsupported database driver: " . $database['driver']);
        }

        return $databaseFile;
    }

    /**
     * Backup MySQL database
     */
    protected function backupMySQL(array $config, string $outputFile): void
    {
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
            $config['username'],
            $config['password'],
            $config['host'],
            $config['port'] ?? 3306,
            $config['database'],
            $outputFile
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("MySQL backup failed");
        }
    }

    /**
     * Backup PostgreSQL database
     */
    protected function backupPostgreSQL(array $config, string $outputFile): void
    {
        $envVars = sprintf(
            'PGPASSWORD=%s PGHOST=%s PGPORT=%s PGUSER=%s PGDATABASE=%s',
            $config['password'],
            $config['host'],
            $config['port'] ?? 5432,
            $config['username'],
            $config['database']
        );

        $command = sprintf(
            '%s pg_dump > %s',
            $envVars,
            $outputFile
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("PostgreSQL backup failed");
        }
    }

    /**
     * Backup SQLite database
     */
    protected function backupSQLite(array $config, string $outputFile): void
    {
        if (!file_exists($config['database'])) {
            throw new Exception("SQLite database file not found: " . $config['database']);
        }

        copy($config['database'], $outputFile);
    }

    /**
     * Backup application files
     */
    protected function backupFiles(string $tempDir): void
    {
        $filesToBackup = [
            'app',
            'config',
            'database/migrations',
            'database/seeders',
            'resources',
            'routes',
            'storage/app',
            'public',
        ];

        foreach ($filesToBackup as $path) {
            $sourcePath = base_path($path);
            $targetPath = $tempDir . '/' . $path;

            if (File::exists($sourcePath)) {
                // Exclude certain directories and files
                $this->copyDirectory($sourcePath, $targetPath, [
                    'node_modules',
                    'vendor',
                    '.git',
                    'storage/logs',
                    'storage/framework/cache',
                    'storage/framework/sessions',
                    'storage/framework/testing',
                    'storage/app/backups',
                    'bootstrap/cache',
                ]);
            }
        }
    }

    /**
     * Copy directory with exclusions
     */
    protected function copyDirectory(string $source, string $target, array $exclude = []): void
    {
        if (!File::exists($target)) {
            File::makeDirectory($target, 0755, true, true);
        }

        $items = File::allFiles($source);

        foreach ($items as $item) {
            $relativePath = $item->getRelativePathname();

            // Skip excluded paths
            $shouldExclude = false;
            foreach ($exclude as $excludePath) {
                if (str_contains($relativePath, $excludePath)) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            $targetFile = $target . '/' . $relativePath;

            // Create directory if it doesn't exist
            $targetDir = dirname($targetFile);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
            }

            // Copy file
            File::copy($item->getPathname(), $targetFile);
        }
    }

    /**
     * Create backup metadata
     */
    protected function createMetadata(string $tempDir, string $backupName, ?string $description, string $type): void
    {
        $metadata = [
            'backup_name' => $backupName,
            'type' => $type,
            'description' => $description,
            'created_at' => Carbon::now()->toISOString(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'database_connection' => config('database.default'),
            'url' => config('app.url'),
        ];

        File::put($tempDir . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
    }

    /**
     * Create zip archive
     */
    protected function createZipArchive(string $sourceDir, string $backupName): string
    {
        $zipFile = storage_path("app/temp_backups/{$backupName}.zip");
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip file: " . $zipFile);
        }

        // Add all files from source directory
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $zipFile;
    }

    /**
     * Move backup to storage
     */
    protected function moveBackupToStorage(string $zipFile, string $backupName, string $type): string
    {
        $storagePath = "{$this->backupDirectory}/{$type}/{$backupName}.zip";

        Storage::disk($this->disk)->makeDirectory(dirname($storagePath));
        Storage::disk($this->disk)->put($storagePath, file_get_contents($zipFile));

        // Clean up temporary zip file
        File::delete($zipFile);

        return $storagePath;
    }

    /**
     * Clean up old backups
     */
    protected function cleanupOldBackups(string $type): void
    {
        $backupPath = "{$this->backupDirectory}/{$type}";

        if (!Storage::disk($this->disk)->exists($backupPath)) {
            return;
        }

        $backups = Storage::disk($this->disk)->files($backupPath);

        // Sort by creation time (newest first)
        usort($backups, function ($a, $b) {
            $timeA = Storage::disk($this->disk)->lastModified($a);
            $timeB = Storage::disk($this->disk)->lastModified($b);
            return $timeB <=> $timeA;
        });

        // Keep only the newest backups
        if (count($backups) > $this->maxBackups) {
            $toDelete = array_slice($backups, $this->maxBackups);

            foreach ($toDelete as $backup) {
                Storage::disk($this->disk)->delete($backup);
            }
        }
    }

    /**
     * List all backups
     */
    public function listBackups(): array
    {
        $backups = [];
        $types = ['full', 'database', 'files'];

        foreach ($types as $type) {
            $backupPath = "{$this->backupDirectory}/{$type}";

            if (!Storage::disk($this->disk)->exists($backupPath)) {
                continue;
            }

            $files = Storage::disk($this->disk)->files($backupPath);

            foreach ($files as $file) {
                if (str_ends_with($file, '.zip')) {
                    $metadata = $this->getBackupMetadata($file);

                    $backups[] = [
                        'name' => basename($file, '.zip'),
                        'type' => $type,
                        'file_path' => $file,
                        'file_size' => Storage::disk($this->disk)->size($file),
                        'created_at' => Storage::disk($this->disk)->lastModified($file),
                        'metadata' => $metadata,
                    ];
                }
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $backups;
    }

    /**
     * Get backup metadata
     */
    protected function getBackupMetadata(string $backupPath): ?array
    {
        try {
            $tempDir = storage_path('app/temp_extract/' . uniqid());
            File::makeDirectory($tempDir, 0755, true, true);

            $zip = new ZipArchive();
            $zipFile = Storage::disk($this->disk)->path($backupPath);

            if ($zip->open($zipFile) === true) {
                $zip->extractTo($tempDir);
                $zip->close();

                $metadataFile = $tempDir . '/metadata.json';
                if (File::exists($metadataFile)) {
                    $metadata = json_decode(File::get($metadataFile), true);
                    File::deleteDirectory($tempDir);
                    return $metadata;
                }
            }

            File::deleteDirectory($tempDir);
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Restore a backup
     */
    public function restoreBackup(string $backupPath): array
    {
        try {
            $tempDir = storage_path('app/temp_restore/' . uniqid());
            File::makeDirectory($tempDir, 0755, true, true);

            $zip = new ZipArchive();
            $zipFile = Storage::disk($this->disk)->path($backupPath);

            if ($zip->open($zipFile) !== true) {
                throw new Exception("Cannot open backup file");
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Get metadata
            $metadataFile = $tempDir . '/metadata.json';
            $metadata = null;
            if (File::exists($metadataFile)) {
                $metadata = json_decode(File::get($metadataFile), true);
            }

            // Restore database if exists
            if (File::exists($tempDir . '/database.sql')) {
                $this->restoreDatabase($tempDir . '/database.sql');
            }

            // Restore files if it's a full or files backup
            if ($metadata && in_array($metadata['type'], ['full', 'files'])) {
                $this->restoreFiles($tempDir);
            }

            // Clean up
            File::deleteDirectory($tempDir);

            return [
                'success' => true,
                'message' => 'Backup restored successfully',
                'metadata' => $metadata,
            ];

        } catch (Exception $e) {
            // Clean up on failure
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            throw new Exception("Restore failed: " . $e->getMessage());
        }
    }

    /**
     * Restore database from backup
     */
    protected function restoreDatabase(string $sqlFile): void
    {
        $database = config('database.connections.' . config('database.default'));

        switch ($database['driver']) {
            case 'mysql':
                $command = sprintf(
                    'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                    $database['username'],
                    $database['password'],
                    $database['host'],
                    $database['port'] ?? 3306,
                    $database['database'],
                    $sqlFile
                );
                break;
            case 'pgsql':
                $envVars = sprintf(
                    'PGPASSWORD=%s PGHOST=%s PGPORT=%s PGUSER=%s PGDATABASE=%s',
                    $database['password'],
                    $database['host'],
                    $database['port'] ?? 5432,
                    $database['username'],
                    $database['database']
                );
                $command = sprintf('%s psql < %s', $envVars, $sqlFile);
                break;
            case 'sqlite':
                if (!file_exists($sqlFile)) {
                    throw new Exception("SQLite backup file not found");
                }
                copy($sqlFile, $database['database']);
                return;
            default:
                throw new Exception("Unsupported database driver: " . $database['driver']);
        }

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Database restore failed");
        }
    }

    /**
     * Restore files from backup
     */
    protected function restoreFiles(string $tempDir): void
    {
        $restorePaths = [
            $tempDir . '/app' => base_path('app'),
            $tempDir . '/config' => base_path('config'),
            $tempDir . '/database/migrations' => base_path('database/migrations'),
            $tempDir . '/database/seeders' => base_path('database/seeders'),
            $tempDir . '/resources' => base_path('resources'),
            $tempDir . '/routes' => base_path('routes'),
            $tempDir . '/public' => base_path('public'),
        ];

        foreach ($restorePaths as $source => $target) {
            if (File::exists($source)) {
                if (File::exists($target)) {
                    File::deleteDirectory($target);
                }
                File::copyDirectory($source, $target);
            }
        }
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(string $backupPath): bool
    {
        return Storage::disk($this->disk)->delete($backupPath);
    }

    /**
     * Download a backup
     */
    public function downloadBackup(string $backupPath)
    {
        return Storage::disk($this->disk)->download($backupPath);
    }

    /**
     * Get backup statistics
     */
    public function getStatistics(): array
    {
        $backups = $this->listBackups();

        $stats = [
            'total_backups' => count($backups),
            'total_size' => 0,
            'by_type' => [
                'full' => 0,
                'database' => 0,
                'files' => 0,
            ],
            'oldest_backup' => null,
            'newest_backup' => null,
        ];

        foreach ($backups as $backup) {
            $stats['total_size'] += $backup['file_size'];
            $stats['by_type'][$backup['type']]++;

            if (!$stats['oldest_backup'] || $backup['created_at'] < $stats['oldest_backup']) {
                $stats['oldest_backup'] = $backup['created_at'];
            }

            if (!$stats['newest_backup'] || $backup['created_at'] > $stats['newest_backup']) {
                $stats['newest_backup'] = $backup['created_at'];
            }
        }

        return $stats;
    }
}