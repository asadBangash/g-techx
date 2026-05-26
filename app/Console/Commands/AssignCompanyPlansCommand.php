<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserActiveModule;
use Illuminate\Console\Command;

class AssignCompanyPlansCommand extends Command
{
    protected $signature = 'app:assign-company-plans
                            {--email= : Assign only to this company email}
                            {--force : Re-sync modules from the active plan even if some already exist}';

    protected $description = 'Sync plan modules and permissions for company accounts';

    public function handle(): int
    {
        $fallbackPlan = Plan::where('free_plan', true)->where('status', true)->first();

        if (! $fallbackPlan) {
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
            $plan = $company->active_plan
                ? Plan::find($company->active_plan)
                : null;

            if (! $plan || ! $plan->status) {
                $plan = $fallbackPlan;
            }

            $expectedModules = collect($plan->modules ?? [])
                ->filter()
                ->map(fn ($module) => trim((string) $module))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $currentModules = UserActiveModule::where('user_id', $company->id)
                ->pluck('module')
                ->sort()
                ->values()
                ->all();

            $expectedSorted = collect($expectedModules)->sort()->values()->all();
            $needsSync = $this->option('force')
                || empty($currentModules)
                || $currentModules !== $expectedSorted;

            if (! $needsSync) {
                $this->line("Skip: {$company->email} (modules already match {$plan->name})");

                continue;
            }

            if ($company->active_plan && $company->plan_expire_date) {
                $result = syncUserPlanModules($company->id, $plan->id, $expectedModules);
            } else {
                $result = assignPlan(
                    $plan->id,
                    'Month',
                    implode(',', $expectedModules),
                    null,
                    $company->id
                );
            }

            if ($result['is_success'] ?? false) {
                $fixed++;
                $newCount = UserActiveModule::where('user_id', $company->id)->count();
                $this->info("Synced: {$company->email} — {$newCount} modules from {$plan->name}");
            } else {
                $this->warn("Failed: {$company->email} — ".($result['error'] ?? 'unknown error'));
            }
        }

        $this->info("Done. Updated {$fixed} company account(s).");

        return self::SUCCESS;
    }
}
