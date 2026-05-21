<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MarkAppInstalled extends Command
{
    protected $signature = 'app:mark-installed';

    protected $description = 'Mark the application as installed (skips web installer redirect)';

    public function handle(): int
    {
        $path = storage_path('installed');

        if (isAppInstalled()) {
            $this->info('Application is already marked as installed.');
            if (File::exists($path)) {
                $this->line(File::get($path));
            }

            return self::SUCCESS;
        }

        File::put($path, 'manual ' . date('Y-m-d H:i:s'));

        $this->info('Application marked as installed.');
        $this->line("Created: {$path}");

        return self::SUCCESS;
    }
}
