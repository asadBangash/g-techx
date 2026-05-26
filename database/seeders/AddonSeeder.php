<?php

namespace Database\Seeders;

use App\Models\AddOn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AddonSeeder extends Seeder
{
    /**
     * Register all workdo packages in the addons table (required for plan features UI).
     */
    public function run(): void
    {
        $packagesPath = base_path('packages/workdo');

        if (! File::exists($packagesPath)) {
            $this->command?->warn('packages/workdo directory not found.');

            return;
        }

        $count = 0;

        foreach (File::directories($packagesPath) as $directory) {
            $moduleJsonPath = $directory.'/module.json';

            if (! File::exists($moduleJsonPath)) {
                continue;
            }

            $data = json_decode(File::get($moduleJsonPath), true);

            if (! $data || empty($data['name'])) {
                continue;
            }

            AddOn::updateOrCreate(
                ['module' => $data['name']],
                [
                    'name' => $data['alias'] ?? $data['name'],
                    'monthly_price' => $data['monthly_price'] ?? 0,
                    'yearly_price' => $data['yearly_price'] ?? 0,
                    'package_name' => $data['package_name'] ?? null,
                    'for_admin' => $data['for_admin'] ?? false,
                    'priority' => $data['priority'] ?? 0,
                    'is_enable' => true,
                ]
            );

            $count++;
        }

        $this->command?->info("Synced {$count} addons from packages/workdo.");
    }
}
