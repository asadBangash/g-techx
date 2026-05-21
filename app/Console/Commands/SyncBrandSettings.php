<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;

class SyncBrandSettings extends Command
{
    protected $signature = 'brand:sync';

    protected $description = 'Sync G-TechX brand settings (logo, name, colors) for superadmin';

    public function handle(): int
    {
        $admin = User::where('type', 'superadmin')->first();

        if (!$admin) {
            $this->error('Superadmin user not found.');

            return self::FAILURE;
        }

        $updates = [
            'logo_light' => 'assets/brand/gtechx-logo.png',
            'logo_dark' => 'assets/brand/gtechx-logo.png',
            'favicon' => 'assets/brand/gtechx-logo.png',
            'themeMode' => 'dark',
            'titleText' => config('brand.short_name'),
            'footerText' => 'Copyright © ' . date('Y') . ' ' . config('brand.copyright'),
            'themeColor' => 'custom',
            'customColor' => config('brand.primary_color'),
            'metaTitle' => config('brand.short_name') . ' - ' . config('brand.tagline'),
            'metaDescription' => config('brand.description'),
            'metaKeywords' => 'g-techx, gtechx, accounting, fbr, pakistan, erp, business management',
        ];

        foreach ($updates as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'created_by' => $admin->id],
                ['value' => $value]
            );
        }

        $this->call('cache:clear');
        $this->info('G-TechX brand settings synced successfully.');

        return self::SUCCESS;
    }
}
