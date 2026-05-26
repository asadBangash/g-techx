<?php

namespace App\Console\Commands;

use App\Models\AddOn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SyncModulesCommand extends Command
{
    protected $signature = 'app:sync-modules {--with-seed : Run package seeders for each module}';

    protected $description = 'Sync addons from packages/workdo and refresh plan module lists';

    public function handle(): int
    {
        $packagesPath = base_path('packages/workdo');

        if (! File::exists($packagesPath)) {
            $this->error('packages/workdo folder not found. Upload the full project including packages/.');

            return self::FAILURE;
        }

        $synced = 0;

        foreach (File::directories($packagesPath) as $directory) {
            $moduleJsonPath = $directory.'/module.json';

            if (! File::exists($moduleJsonPath)) {
                continue;
            }

            $data = json_decode(File::get($moduleJsonPath), true);

            if (! $data || empty($data['name'])) {
                continue;
            }

            $moduleName = $data['name'];

            AddOn::updateOrCreate(
                ['module' => $moduleName],
                [
                    'name' => $data['alias'] ?? $moduleName,
                    'monthly_price' => $data['monthly_price'] ?? 0,
                    'yearly_price' => $data['yearly_price'] ?? 0,
                    'package_name' => $data['package_name'] ?? null,
                    'for_admin' => $data['for_admin'] ?? false,
                    'priority' => $data['priority'] ?? 0,
                    'is_enable' => true,
                ]
            );

            if ($this->option('with-seed')) {
                try {
                    Artisan::call('package:seed', ['packageName' => $moduleName]);
                    $this->line("  Seeded: {$moduleName}");
                } catch (\Throwable $e) {
                    $this->warn("  Seed failed for {$moduleName}: ".$e->getMessage());
                }
            }

            $synced++;
        }

        $this->info("Synced {$synced} addons.");

        $this->call('db:seed', ['--class' => 'Database\\Seeders\\PlanSeeder', '--force' => true]);

        $enabled = AddOn::where('is_enable', 1)->where('for_admin', false)->count();
        $this->info("Enabled addons (visible on plans page): {$enabled}");

        return self::SUCCESS;
    }
}
