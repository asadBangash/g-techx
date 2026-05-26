<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncCompanySubscriptionCommand extends Command
{
    protected $signature = 'app:sync-company-subscription
                            {--email= : Sync only this company email}';

    protected $description = 'Sync addons, plan modules, permissions, and sidebar menus for subscribed companies';

    public function handle(): int
    {
        $this->info('Step 1/2: Syncing addons and module permissions...');
        Artisan::call('app:sync-modules', ['--with-seed' => true], $this->output);

        $this->newLine();
        $this->info('Step 2/2: Assigning subscribed plan modules to companies...');

        $options = ['--force' => true];
        if ($email = $this->option('email')) {
            $options['--email'] = $email;
        }

        Artisan::call('app:assign-company-plans', $options, $this->output);

        $this->newLine();
        Artisan::call('cache:clear');
        $this->info('Subscription sync complete. Ask users to log out and back in.');

        return self::SUCCESS;
    }
}
