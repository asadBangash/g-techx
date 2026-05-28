<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SafeStorageLinkCommand extends Command
{
    protected $signature = 'app:storage-link {--force : Replace existing link or directory}';

    protected $description = 'Create public/storage symlink without exec() (for shared hosting)';

    public function handle(): int
    {
        $links = config('filesystems.links', [
            public_path('storage') => storage_path('app/public'),
        ]);

        foreach ($links as $link => $target) {
            $link = $this->normalizePath($link);
            $target = $this->normalizePath($target);

            if (! File::isDirectory($target)) {
                File::makeDirectory($target, 0755, true);
                $this->line("Created directory: {$target}");
            }

            if (file_exists($link) || is_link($link)) {
                if (! $this->option('force')) {
                    $this->warn("Already exists: {$link} (use --force to replace)");

                    continue;
                }

                if (is_link($link)) {
                    unlink($link);
                } elseif (File::isDirectory($link)) {
                    File::deleteDirectory($link);
                } else {
                    File::delete($link);
                }
            }

            if ($this->createLink($target, $link)) {
                $this->info("Linked [{$link}] → [{$target}]");

                continue;
            }

            $this->error("Could not create symlink for [{$link}]");
            $this->line('');
            $this->line('Run this in SSH (Hostinger):');
            $this->line("  ln -sfn {$target} {$link}");
            $this->line('');
            $this->line('Or in File Manager: delete public/storage, then create symlink:');
            $this->line("  Link: public/storage");
            $this->line("  Target: storage/app/public");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function createLink(string $target, string $link): bool
    {
        if (! function_exists('symlink')) {
            return false;
        }

        if (@symlink($target, $link)) {
            return true;
        }

        // Relative symlink sometimes works when absolute paths are blocked.
        $relativeTarget = $this->relativePath(dirname($link), $target);

        if ($relativeTarget !== null) {
            return @symlink($relativeTarget, $link);
        }

        return false;
    }

    private function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    private function relativePath(string $from, string $to): ?string
    {
        $from = realpath($from);
        $to = realpath($to);

        if ($from === false || $to === false) {
            return null;
        }

        $fromParts = explode(DIRECTORY_SEPARATOR, rtrim($from, DIRECTORY_SEPARATOR));
        $toParts = explode(DIRECTORY_SEPARATOR, rtrim($to, DIRECTORY_SEPARATOR));

        while (count($fromParts) && count($toParts) && $fromParts[0] === $toParts[0]) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        $relative = array_merge(
            array_fill(0, count($fromParts), '..'),
            $toParts
        );

        return implode(DIRECTORY_SEPARATOR, $relative);
    }
}
