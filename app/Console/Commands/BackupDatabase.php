<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Daily automated database backup (Non-Functional Requirement: Reliability).
 *
 *   php artisan db:backup            -> creates storage/app/backups/db-YYYYMMDD-HHMMSS.{sqlite|sql}
 *   php artisan db:backup --keep=7   -> keep only the 7 most recent backups
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Number of backups to retain}';

    protected $description = 'Create a timestamped backup of the application database';

    public function handle(): int
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $stamp = now()->format('Ymd-His');

        if ($config['driver'] === 'sqlite') {
            $source = $config['database'];

            if (! is_file($source)) {
                $this->error("SQLite database not found at {$source}");

                return self::FAILURE;
            }

            DB::connection()->getPdo()->exec('PRAGMA wal_checkpoint(FULL)');
            $target = "{$directory}/db-{$stamp}.sqlite";
            File::copy($source, $target);
        } elseif (in_array($config['driver'], ['mysql', 'mariadb'], true)) {
            $target = "{$directory}/db-{$stamp}.sql";
            $process = new Process([
                'mysqldump',
                '--host='.$config['host'],
                '--port='.$config['port'],
                '--user='.$config['username'],
                '--password='.$config['password'],
                '--single-transaction',
                '--result-file='.$target,
                $config['database'],
            ]);
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error('mysqldump failed: '.$process->getErrorOutput());

                return self::FAILURE;
            }
        } else {
            $this->error("Unsupported database driver: {$config['driver']}");

            return self::FAILURE;
        }

        $this->info('Backup created: '.$target);

        $keep = max(1, (int) $this->option('keep'));
        $files = collect(File::files($directory))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        foreach ($files->slice($keep) as $old) {
            File::delete($old->getPathname());
            $this->line('Removed old backup: '.$old->getFilename());
        }

        return self::SUCCESS;
    }
}
