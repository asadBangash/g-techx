<?php

namespace App\Console\Commands;

use App\Classes\Module;
use App\Models\AddOn;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserActiveModule;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DiagnoseCompanyAccessCommand extends Command
{
    protected $signature = 'app:diagnose-company-access {--email= : Diagnose a single company by email}';

    protected $description = 'Show why a company user is missing sidebar features';

    public function handle(): int
    {
        $email = $this->option('email');

        $query = User::where('type', 'company');
        if ($email) {
            $query->where('email', $email);
        }

        $companies = $query->get();
        if ($companies->isEmpty()) {
            $this->error('No company users found'.($email ? " for {$email}" : ''));

            return self::FAILURE;
        }

        $this->line('');
        $this->line('=== ENVIRONMENT ===');
        $this->line('App URL:        '.config('app.url'));
        $this->line('Env:            '.app()->environment());
        $this->line('packages/workdo exists: '.(is_dir(base_path('packages/workdo')) ? 'YES' : 'NO  <-- BIG PROBLEM'));
        $this->line('public/build/manifest.json exists: '.(file_exists(public_path('build/manifest.json')) ? 'YES' : 'NO  <-- run npm run build'));

        $workdoDirs = is_dir(base_path('packages/workdo'))
            ? array_values(array_filter(scandir(base_path('packages/workdo')), fn ($d) => $d !== '.' && $d !== '..' && is_dir(base_path('packages/workdo/'.$d))))
            : [];
        $this->line('packages/workdo subfolders ('.count($workdoDirs).'): '.implode(', ', $workdoDirs));

        $this->line('');
        $this->line('=== ADDONS TABLE ===');
        $enabledAddons = AddOn::where('is_enable', 1)->orderBy('priority')->pluck('module')->toArray();
        $this->line('Enabled addons ('.count($enabledAddons).'): '.implode(', ', $enabledAddons));
        if (count($enabledAddons) < 5) {
            $this->warn('Very few enabled addons. Run: php artisan app:sync-modules --with-seed');
        }

        $this->line('');
        $this->line('=== COMPANY ROLE PERMISSIONS ===');
        $companyRole = Role::where('name', 'company')->where('guard_name', 'web')->first();
        if (! $companyRole) {
            $this->error('No "company" role exists. Run: php artisan db:seed --force');
        } else {
            $perms = $companyRole->permissions()->pluck('name')->toArray();
            $this->line('Company role permissions count: '.count($perms));
            $missing = ['manage-hrm', 'manage-account', 'manage-pos', 'manage-crm', 'manage-product'];
            foreach ($missing as $p) {
                $has = in_array($p, $perms);
                $this->line('  '.($has ? '[OK]  ' : '[MISS]').' '.$p);
            }
        }

        foreach ($companies as $company) {
            $this->line('');
            $this->line('================================================');
            $this->line('COMPANY: '.$company->email.' (id='.$company->id.')');
            $this->line('================================================');
            $this->line('active_plan id: '.($company->active_plan ?? 'NULL'));

            $plan = $company->active_plan ? Plan::find($company->active_plan) : null;
            if (! $plan) {
                $this->error('No active plan record found.');

                continue;
            }
            $this->line('plan name:      '.$plan->name);
            $planModules = is_array($plan->modules) ? $plan->modules : [];
            $this->line('plan modules ('.count($planModules).'): '.implode(', ', $planModules));

            $uam = UserActiveModule::where('user_id', $company->id)->pluck('module')->toArray();
            $this->line('user_active_modules ('.count($uam).'): '.implode(', ', $uam));

            $subModules = Plan::getUserSubscriptionModules($company->id);
            $this->line('getUserSubscriptionModules ('.count($subModules).'): '.implode(', ', $subModules));

            $userPerms = $company->getAllPermissions()->pluck('name')->toArray();
            $this->line('User total permissions: '.count($userPerms));

            $this->line('');
            $this->line('-- GAP ANALYSIS --');
            $missingFromAddons = array_diff($planModules, $enabledAddons);
            if ($missingFromAddons) {
                $this->warn('Plan modules NOT in enabled addons: '.implode(', ', $missingFromAddons));
                $this->line('  Fix: ensure packages/workdo/<Name>/module.json exists and run php artisan app:sync-modules --with-seed');
            }

            $missingFromUam = array_diff($planModules, $uam);
            if ($missingFromUam) {
                $this->warn('Plan modules NOT in user_active_modules: '.implode(', ', $missingFromUam));
                $this->line('  Fix: php artisan app:repair-company-access --email='.$company->email);
            }

            $rolePerms = $companyRole ? $companyRole->permissions()->pluck('name')->toArray() : [];
            $expected = [];
            foreach ($planModules as $mod) {
                $p = Permission::where('add_on', $mod)->pluck('name')->toArray();
                $expected = array_merge($expected, $p);
            }
            $missingPerms = array_diff($expected, $rolePerms);
            if ($missingPerms) {
                $this->warn('Permissions missing from company role ('.count($missingPerms).'): '.implode(', ', array_slice($missingPerms, 0, 15)).(count($missingPerms) > 15 ? ' ...' : ''));
                $this->line('  Fix: php artisan app:repair-company-access --email='.$company->email);
            }

            if (! $missingFromAddons && ! $missingFromUam && ! $missingPerms) {
                $this->info('Backend looks healthy. If sidebar is still limited, the issue is the frontend build (packages/workdo missing at npm run build time).');
            }
        }

        $this->line('');
        $this->info('Done.');

        return self::SUCCESS;
    }
}
