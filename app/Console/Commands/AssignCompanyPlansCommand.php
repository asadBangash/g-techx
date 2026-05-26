<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserActiveModule;
use Illuminate\Console\Command;

class AssignCompanyPlansCommand extends Command
{
    protected $signature = 'app:assign-company-plans {--email= : Assign only to this company email}';

    protected $description = 'Assign the free plan and modules to companies missing an active subscription';

    public function handle(): int
    {
        $plan = Plan::where('free_plan', true)->where('status', true)->first();

        if (! $plan) {
            $this->error('No active free plan found. Run: php artisan db:seed --class=PlanSeeder');

            return self::FAILURE;
        }

        $query = User::where('type', 'company');

        if ($email = $this->option('email')) {
            $query->where('email', $email);
        }

        $companies = $query->get();
        $fixed = 0;

        foreach ($companies as $company) {
            $moduleCount = UserActiveModule::where('user_id', $company->id)->count();

            if ($company->active_plan && $moduleCount > 0) {
                $this->line("Skip: {$company->email} (already has plan + {$moduleCount} modules)");

                continue;
            }

            $result = assignPlan(
                $plan->id,
                'Month',
                implode(',', $plan->modules ?? []),
                null,
                $company->id
            );

            if ($result['is_success'] ?? false) {
                $fixed++;
                $newCount = UserActiveModule::where('user_id', $company->id)->count();
                $this->info("Fixed: {$company->email} — {$newCount} modules assigned");
            } else {
                $this->warn("Failed: {$company->email} — ".($result['error'] ?? 'unknown error'));
            }
        }

        $this->info("Done. Updated {$fixed} company account(s).");

        return self::SUCCESS;
    }
}
