<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RepairCompanyAccessCommand extends Command
{
    protected $signature = 'app:repair-company-access {--email= : Repair a single company by email}';

    protected $description = 'Fix missing sidebar modules/permissions for subscribed companies';

    public function handle(): int
    {
        $query = User::where('type', 'company')->whereNotNull('active_plan');

        if ($email = $this->option('email')) {
            $query->where('email', $email);
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->error('No company accounts with an active plan were found.');

            return self::FAILURE;
        }

        foreach ($companies as $company) {
            ensureCompanySubscriptionReady($company, true);
            $moduleCount = \App\Models\UserActiveModule::where('user_id', $company->id)->count();
            $this->info("Repaired: {$company->email} — {$moduleCount} modules");
        }

        $this->info('Done. Ask affected users to log out and log back in.');

        return self::SUCCESS;
    }
}
