<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Classes\Module;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use App\Models\Setting;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserActiveModule;
use App\Models\UserCoupon;
use App\Models\AddOn;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\DynamicStorageService;
use App\Services\StorageConfigService;

if (!function_exists('creatorId')) {
    function creatorId()
    {
        if (Auth::user()->type == 'superadmin' || Auth::user()->type == 'company') {
            return Auth::user()->id;
        } else {
            return Auth::user()->created_by;
        }
    }
}

if (!function_exists('creatorUser')) {
    function creatorUser()
    {
        if (Auth::user() && (Auth::user()->type == 'superadmin' || Auth::user()->type == 'company')) {
            return Auth::user();
        } else {
            return Auth::user()->createdBy();
        }
    }
}

if (!function_exists('setSetting')) {
    function setSetting(string $key, $value, $userId = null, $isPublic = true): void
    {
        $createdBy = $userId ?? creatorId();
        Setting::updateOrCreate(
            ['key' => $key, 'created_by' => $createdBy],
            ['value' => $value, 'is_public' => $isPublic]
        );

        // Clear user-specific cache
        if (Auth::check() && Auth::user()->type == 'superadmin'){
            Cache::forget('admin_settings');
            Cache::forget('admin_settings_public');
        }
        Cache::forget('company_settings_' . $createdBy);
        Cache::forget('company_settings_' . $createdBy . '_public');
    }
}

if (!function_exists('getAdminAllSetting')) {
    function getAdminAllSetting($publicOnly = false)
    {
        $cacheKey = $publicOnly ? 'admin_settings_public' : 'admin_settings';
        $settings = Cache::rememberForever($cacheKey, function () use ($publicOnly) {
            $super_admin = User::where('type', 'superadmin')->first();
            if ($super_admin) {
                $query = Setting::where('created_by', $super_admin->id);
                if ($publicOnly) {
                    $query->where('is_public', 1);
                }
                return $query->pluck('value', 'key')->toArray();
            }
            return [];
        });

        if (config('app.is_demo')) {
            $themeKeys = [
                'theme_color' => 'themeColor',
                'sidebar_variant' => 'sidebarVariant',
                'sidebar_style' => 'sidebarStyle',
                'layout_direction' => 'layoutDirection', 
                'theme_mode' => 'themeMode',
                'custom_color' => 'customColor'
            ];
            
            $superadmin = User::where('type', 'superadmin')->first();
            $cookieName = 'theme_settings_' . ($superadmin ? $superadmin->id : 1);
            
            if (\Cookie::get($cookieName)) {
                $cookieData = json_decode(\Cookie::get($cookieName), true);
                if (is_array($cookieData)) {
                    foreach ($themeKeys as $cookieKey => $settingKey) {
                        if (isset($cookieData[$cookieKey])) {
                            $settings[$settingKey] = $cookieData[$cookieKey];
                        }
                    }
                }
            }
        }

        // Auto-set RTL for specific languages
        if (in_array(app()->getLocale(), ['ar', 'he'])) {
            $settings['layoutDirection'] = 'rtl';
        }

        return $settings;
    }
}

if (!function_exists('getCompanyAllSetting')) {
    function getCompanyAllSetting($user_id = null, $publicOnly = false)
    {
        $user = $user_id ? User::find($user_id) : auth()->user();

        if (!$user) return [];

        if (!in_array($user->type, ['company', 'superadmin'])) {
            $user = User::find($user->created_by);
        }

        if ($user) {
            $key = $publicOnly ? 'company_settings_' . $user->id . '_public' : 'company_settings_' . $user->id;
            $settings = Cache::rememberForever($key, function () use ($user, $publicOnly) {
                $query = Setting::where('created_by', $user->id);
                if ($publicOnly) {
                    $query->where('is_public', 1);
                }
                return $query->pluck('value', 'key')->toArray();
            });

            if (config('app.is_demo')) {
                $themeKeys = [
                    'theme_color' => 'themeColor',
                    'sidebar_variant' => 'sidebarVariant',
                    'sidebar_style' => 'sidebarStyle',
                    'layout_direction' => 'layoutDirection', 
                    'theme_mode' => 'themeMode',
                    'custom_color' => 'customColor'
                ];
                
                $cookieName = 'theme_settings_' . creatorId();
                if (\Cookie::get($cookieName)) {
                    $cookieData = json_decode(\Cookie::get($cookieName), true);
                    if (is_array($cookieData)) {
                        foreach ($themeKeys as $cookieKey => $settingKey) {
                            if (isset($cookieData[$cookieKey])) {
                                $settings[$settingKey] = $cookieData[$cookieKey];
                            }
                        }
                    }
                }
            }

            // Auto-set RTL for specific languages
            if (in_array(app()->getLocale(), ['ar', 'he'])) {
                $settings['layoutDirection'] = 'rtl';
            }

            return $settings;
        }

        return [];
    }
}

if (!function_exists('admin_setting')) {
    function admin_setting($key)
    {
        if ($key) {
            $admin_settings = getAdminAllSetting();
            $setting = (array_key_exists($key, $admin_settings)) ? $admin_settings[$key] : null;
            return $setting;
        }
    }
}

if (!function_exists('company_setting')) {
    function company_setting($key, $user_id = null)
    {
        if ($key) {
            $company_settings = getCompanyAllSetting($user_id);
            return $company_settings[$key] ?? null;
        }
        return null;
    }
}

if (!function_exists('invoice_default_currency')) {
    function invoice_default_currency($user_id = null): string
    {
        return company_setting('defaultCurrency', $user_id) ?: 'USD';
    }
}

if (!function_exists('valid_currency_codes')) {
    function valid_currency_codes(): array
    {
        return array_column(config('default_currency.currencies', []), 'code');
    }
}

if (!function_exists('getImageUrlPrefix')) {
    function getImageUrlPrefix(): string
    {
        $storageType = admin_setting('storageType') ?: 'local';

        switch ($storageType) {
            case 's3':
            case 'aws_s3':
                $endpoint = admin_setting('awsEndpoint');
                if ($endpoint && strpos($endpoint, 'amazonaws.com') === false) {
                    return rtrim($endpoint, '/') . '/media/';
                }
                $bucket = admin_setting('awsBucket');
                $region = admin_setting('awsDefaultRegion');
                return "https://{$bucket}.s3.{$region}.amazonaws.com/media";

            case 'wasabi':
                $url = admin_setting('wasabiUrl');
                $bucket = admin_setting('wasabiBucket');
                return $url ? rtrim($url, '/') . '/' . $bucket . '/media' : url('/storage/media/');

            case 'local':
                return url('/storage/media/');
            default:
                return url('/storage/media/');
        }
    }
}

// Seed module permissions onto the global company role (required for sidebar menus).
if (! function_exists('seedPackagePermissions')) {
    function seedPackagePermissions(array $moduleNames): void
    {
        foreach ($moduleNames as $moduleName) {
            $moduleName = trim((string) $moduleName);
            if ($moduleName === '') {
                continue;
            }

            $seederClass = "Workdo\\{$moduleName}\\Database\\Seeders\\PermissionTableSeeder";
            if (! class_exists($seederClass)) {
                continue;
            }

            try {
                (new $seederClass())->run();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

if (! function_exists('resolveCompanyUser')) {
    function resolveCompanyUser(?User $user): ?User
    {
        if (! $user) {
            return null;
        }

        if ($user->type === 'company') {
            return $user;
        }

        return $user->created_by ? User::find($user->created_by) : null;
    }
}

if (! function_exists('syncPlanAddonsFromPackages')) {
    /**
     * Ensure plan modules exist in the addons table as enabled (required on fresh servers).
     */
    function syncPlanAddonsFromPackages(array $moduleNames): void
    {
        foreach ($moduleNames as $moduleName) {
            $moduleName = trim((string) $moduleName);
            if ($moduleName === '') {
                continue;
            }

            $moduleJsonPath = base_path('packages/workdo/'.$moduleName.'/module.json');
            if (! file_exists($moduleJsonPath)) {
                continue;
            }

            $data = json_decode(file_get_contents($moduleJsonPath), true);
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

            try {
                (new Module())->moduleCacheForget($data['name']);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}

if (! function_exists('companyRoleMissingPlanPermissions')) {
    function companyRoleMissingPlanPermissions(array $planModules): bool
    {
        $companyRole = Role::where('name', 'company')->where('guard_name', 'web')->first();
        if (! $companyRole || empty($planModules)) {
            return false;
        }

        foreach ($planModules as $moduleName) {
            $permissions = Permission::where('add_on', $moduleName)->get();

            // No permissions exist for this module yet — definitely missing.
            if ($permissions->isEmpty()) {
                return true;
            }

            foreach ($permissions as $permission) {
                if (! $companyRole->hasPermissionTo($permission->name)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (! function_exists('refreshPermissionCache')) {
    function refreshPermissionCache(?User $user = null): void
    {
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            report($e);
        }

        if ($user) {
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

if (! function_exists('fillQuickContactDefaults')) {
    function fillQuickContactDefaults(array $data, string $type = 'customer'): array
    {
        $label = ucfirst($type);
        $data['name'] = trim((string) ($data['name'] ?? '')) ?: "{$label} ".now()->format('Y-m-d H:i:s');
        $email = trim((string) ($data['email'] ?? ''));
        $data['email'] = $email !== '' ? $email : strtolower($type).'-'.Str::uuid().'@noemail.local';

        return $data;
    }
}

if (! function_exists('fillWarehouseDefaults')) {
    function fillWarehouseDefaults(array $data): array
    {
        $suffix = now()->format('Y-m-d H:i:s');
        $data['name'] = trim((string) ($data['name'] ?? '')) ?: "Warehouse {$suffix}";
        $data['address'] = trim((string) ($data['address'] ?? '')) ?: '-';
        $data['city'] = trim((string) ($data['city'] ?? '')) ?: '-';
        $data['zip_code'] = trim((string) ($data['zip_code'] ?? '')) ?: '-';
        $data['phone'] = trim((string) ($data['phone'] ?? '')) ?: null;
        $data['email'] = trim((string) ($data['email'] ?? '')) ?: null;

        return $data;
    }
}

if (! function_exists('fillAccountPartyDefaults')) {
    function fillAccountPartyDefaults(array $data, string $type = 'customer'): array
    {
        $label = ucfirst($type);
        $suffix = now()->format('Y-m-d H:i:s');
        $data['company_name'] = trim((string) ($data['company_name'] ?? '')) ?: "{$label} {$suffix}";
        $data['contact_person_name'] = trim((string) ($data['contact_person_name'] ?? '')) ?: $data['company_name'];
        $email = trim((string) ($data['contact_person_email'] ?? ''));
        $data['contact_person_email'] = $email !== '' ? $email : strtolower($type).'-'.Str::uuid().'@noemail.local';

        $billing = is_array($data['billing_address'] ?? null) ? $data['billing_address'] : [];
        $data['billing_address'] = [
            'name' => trim((string) ($billing['name'] ?? '')) ?: $data['company_name'],
            'address_line_1' => trim((string) ($billing['address_line_1'] ?? '')) ?: '-',
            'address_line_2' => trim((string) ($billing['address_line_2'] ?? '')),
            'city' => trim((string) ($billing['city'] ?? '')) ?: '-',
            'state' => trim((string) ($billing['state'] ?? '')) ?: '-',
            'country' => trim((string) ($billing['country'] ?? '')) ?: '-',
            'zip_code' => trim((string) ($billing['zip_code'] ?? '')) ?: '-',
        ];

        $sameAsBilling = $data['same_as_billing'] ?? true;
        $data['same_as_billing'] = (bool) $sameAsBilling;

        if ($data['same_as_billing']) {
            $data['shipping_address'] = $data['billing_address'];
        } else {
            $shipping = is_array($data['shipping_address'] ?? null) ? $data['shipping_address'] : [];
            $data['shipping_address'] = [
                'name' => trim((string) ($shipping['name'] ?? '')) ?: $data['company_name'],
                'address_line_1' => trim((string) ($shipping['address_line_1'] ?? '')) ?: '-',
                'address_line_2' => trim((string) ($shipping['address_line_2'] ?? '')),
                'city' => trim((string) ($shipping['city'] ?? '')) ?: '-',
                'state' => trim((string) ($shipping['state'] ?? '')) ?: '-',
                'country' => trim((string) ($shipping['country'] ?? '')) ?: '-',
                'zip_code' => trim((string) ($shipping['zip_code'] ?? '')) ?: '-',
            ];
        }

        return $data;
    }
}

if (! function_exists('ensureCompanySubscriptionReady')) {
    /**
     * Make subscribed company modules + sidebar permissions work.
     *
     * Cheap operations only (safe to call on every login/register on shared hosting):
     *   - sync addons table from packages/workdo (idempotent updateOrCreate)
     *   - sync user_active_modules to match plan.modules
     *   - refresh permission cache
     *
     * Heavy operations (only when explicitly requested via the repair command):
     *   - seedPackagePermissions      → thousands of inserts; run once at deploy
     *   - dispatchPlanModuleSetup     → per-module default data; can take minutes
     */
    function ensureCompanySubscriptionReady(User $user, bool $withDefaultData = false): void
    {
        $company = resolveCompanyUser($user);
        if (! $company || ! $company->active_plan) {
            return;
        }

        $plan = Plan::find($company->active_plan);
        if (! $plan) {
            return;
        }

        $planModules = is_array($plan->modules)
            ? array_values(array_filter(array_map('trim', $plan->modules)))
            : [];

        if (empty($planModules)) {
            return;
        }

        syncPlanAddonsFromPackages($planModules);

        $currentModules = UserActiveModule::where('user_id', $company->id)
            ->pluck('module')
            ->sort()
            ->values()
            ->all();
        $expectedModules = collect($planModules)->sort()->values()->all();

        if ($currentModules !== $expectedModules) {
            applyPlanModulesToUser($company, $planModules, false);
        }

        // Permission seeding is a one-time deployment task; do NOT run synchronously here
        // because it inserts thousands of rows and stalls registration/login on shared hosting.
        // Run `php artisan app:repair-company-access` once after deploy.
        if ($withDefaultData) {
            dispatchPlanModuleSetup($company, $planModules, false);
        }

        refreshPermissionCache($user);
    }
}

// Users Activated Module
if (!function_exists('ActivatedModule')) {
    function ActivatedModule($user_id = null)
    {
        $alwaysActive = User::$superadmin_activated_module;
        $user_active_module = [];

        if ($user_id != null) {
            $user = User::find($user_id);
        } elseif (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = null;
        }

        if (! empty($user)) {
            if ($user->type == 'superadmin') {
                $user_active_module = array_values((new Module())->allEnabled());
            } else {
                $companyUser = $user->type === 'company'
                    ? $user
                    : User::find($user->created_by);

                if ($companyUser) {
                    // Include plan modules + user_active_modules (same source as the plans page).
                    $user_active_module = Plan::getUserSubscriptionModules($companyUser->id);
                    $user_active_module = array_values(array_unique(array_merge($alwaysActive, $user_active_module)));
                }
            }
        } else {
            $user_active_module = array_values((new Module())->allEnabledAdmin());
        }

        return $user_active_module;
    }
}

// check module is active
if (!function_exists('Module_is_active')) {
    function Module_is_active($module, $user_id = null)
    {
        if ((new Module())->has($module)) {

            $isModuleActive = (new Module())->isEnabled($module);
            if ($isModuleActive == false) {
                return false;
            }

            if (!empty($user_id)) {
                $user = User::find($user_id);
            } else {
                $user = Auth::user();
            }
            if (!empty($user)) {
                if ($user->type == 'superadmin') {
                    return true;
                } else {
                    $active_module = ActivatedModule($user->id);
                    if ((count($active_module) > 0 && in_array($module, $active_module))) {
                        return true;
                    }
                    return false;
                }
            }
            return false;
        }
        return false;
    }
}

// Dispatch module setup events (default data + role permissions).
if (! function_exists('dispatchPlanModuleSetup')) {
    function dispatchPlanModuleSetup(User $user, array $modules_array, bool $seedPermissions = true): void
    {
        if (empty($modules_array)) {
            return;
        }

        if ($seedPermissions) {
            seedPackagePermissions($modules_array);
        }

        $modules_string = implode(',', $modules_array);
        DefaultData::dispatch($user->id, $modules_string);

        $company_role = Role::where('name', 'company')->where('guard_name', 'web')->first();
        $client_role = Role::where('name', 'client')->where('created_by', $user->id)->first();
        $staff_role = Role::where('name', 'staff')->where('created_by', $user->id)->first();

        if (! empty($company_role)) {
            GivePermissionToRole::dispatch($company_role->id, 'company', $modules_string);
        }
        if (! empty($client_role)) {
            GivePermissionToRole::dispatch($client_role->id, 'client', $modules_string);
        }
        if (! empty($staff_role)) {
            GivePermissionToRole::dispatch($staff_role->id, 'staff', $modules_string);
        }
    }
}

// Log out and send the user back to login (used after subscription changes).
if (! function_exists('logoutAndRedirectToLogin')) {
    function logoutAndRedirectToLogin(string $message, ?string $email = null): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerate(true);
        request()->session()->regenerateToken();

        $params = $email ? ['email' => $email] : [];

        return redirect()
            ->route('login', $params)
            ->with('success', $message);
    }
}

// for plan assign
if (! function_exists('applyPlanModulesToUser')) {
    function applyPlanModulesToUser(User $user, array $modules_array, bool $withSetup = true, bool $seedPermissions = false): void
    {
        if (empty($modules_array)) {
            return;
        }

        UserActiveModule::where('user_id', $user->id)->delete();

        foreach ($modules_array as $moduleName) {
            $moduleName = trim((string) $moduleName);
            if ($moduleName === '') {
                continue;
            }

            UserActiveModule::create([
                'user_id' => $user->id,
                'module' => $moduleName,
            ]);
        }

        if ($withSetup) {
            dispatchPlanModuleSetup($user, $modules_array, $seedPermissions);
        }
    }
}

if (! function_exists('syncUserPlanModules')) {
    function syncUserPlanModules($user_id, $plan_id = null, $modules = null): array
    {
        $user = User::find($user_id);
        if (! $user) {
            return ['is_success' => false, 'error' => 'User not found.'];
        }

        $plan = $plan_id ? Plan::find($plan_id) : Plan::find($user->active_plan);
        if (! $plan) {
            return ['is_success' => false, 'error' => 'Plan not found.'];
        }

        if (is_array($modules)) {
            $modules_array = $modules;
        } elseif (! empty($modules)) {
            $modules_array = array_filter(array_map('trim', explode(',', $modules)));
        } else {
            $modules_array = is_array($plan->modules) ? $plan->modules : [];
        }

        if (empty($modules_array)) {
            return ['is_success' => false, 'error' => 'No modules found for this plan.'];
        }

        applyPlanModulesToUser($user, $modules_array, true, true);

        if (! $user->active_plan) {
            $user->active_plan = $plan->id;
            $user->save();
        }

        return ['is_success' => true];
    }
}

if (!function_exists('assignPlan')) {
    function assignPlan($plan_id = null, $duration = null, $modules = null, $counter = null, $user_id = null)
    {
        if ($user_id != null) {
            $user = User::find($user_id);
        } else {
            $user = User::find(Auth::user()->id);
        }

        if ($plan_id != null) {
            $plan = \App\Models\Plan::find($plan_id);
        } else {
            $plan = \App\Models\Plan::where('free_plan', 1)->first();
        }

        if ($plan && $user) {
            $user->active_plan = $plan->id;
            if (!empty($duration)) {
                $durationStr = (string)$duration;

                if (strtolower($durationStr) == 'month' || $durationStr === '1') {
                    $user->plan_expire_date = \Carbon\Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
                    $user->trial_expire_date = null;
                } elseif (strtolower($durationStr) == 'year') {
                    $user->plan_expire_date = \Carbon\Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
                    $user->trial_expire_date = null;
                } elseif (strtolower($durationStr) == 'trial') {
                    $user->trial_expire_date = \Carbon\Carbon::now()->addDays((int)$plan->trial_days)->isoFormat('YYYY-MM-DD');
                    if ($user->plan_expire_date) {
                        $user->plan_expire_date = null;
                    }
                } else {
                    $user->plan_expire_date = null;
                }
            } else {
                $user->plan_expire_date = null;
            }
            // Handle modules assignment
            if (is_array($modules)) {
                $modules_array = $modules;
            } elseif (! empty($modules)) {
                $modules_array = array_filter(array_map('trim', explode(',', $modules)));
            } else {
                $modules_array = is_array($plan->modules) ? $plan->modules : [];
            }

            if (! empty($modules_array)) {
                applyPlanModulesToUser($user, $modules_array, true, true);
            }

            if ($user->type === 'company') {
                ensureCompanySubscriptionReady($user, false);
            }
            
            // Set user limits from plan (don't modify the plan itself)
            $user->total_user = $plan->number_of_users;
            $user->storage_limit = $plan->storage_limit;
            $user->save();

            // User count management logic
            $users = User::where('created_by', $user->id)->where('is_disable', 0)->get();
            $total = $users->count();

            if ($plan->number_of_users == -1) {
                $users = User::where('created_by', $user->id)->get();
                foreach ($users as $item) {
                    $item->is_disable = 0;
                    $item->is_enable_login = 1;
                    $item->save();
                }
            } elseif ($plan->number_of_users > 0) {
                if ($total > $plan->number_of_users) {
                    $count = $total - $plan->number_of_users;
                    $usersToDisable = User::orderBy('created_at', 'desc')
                        ->where('created_by', $user->id)
                        ->where('is_disable', 0)
                        ->take($count)
                        ->get();
                    foreach ($usersToDisable as $userItem) {
                        $userItem->is_disable = 1;
                        $userItem->is_enable_login = 0;
                        $userItem->save();
                    }
                } else {
                    $count = $plan->number_of_users - $total;
                    $usersToEnable = User::where('created_by', $user->id)
                        ->where('is_disable', 1)
                        ->take($count)
                        ->get();

                    foreach ($usersToEnable as $userItem) {
                        $userItem->is_disable = 0;
                        $userItem->is_enable_login = 1;
                        $userItem->save();
                    }
                }
            }

            return ['is_success' => true];
        } else {
            return [
                'is_success' => false,
                'error' => 'Plan is deleted.',
            ];
        }
    }
}

// Plan check
if (!function_exists('canCreateUser')) {
    function canCreateUser($userId = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();

        if (!$user) {
            return ['can_create' => false, 'message' => __('User not found')];
        }

        $creator = ($user->type == 'company' || $user->type == 'superadmin') ? $user : User::find($user->created_by);

        if (!$creator) {
            return ['can_create' => false, 'message' => __('Creator not found')];
        }

        if ($creator->total_user == -1) {
            return ['can_create' => true];
        }

        $currentUserCount = User::where('created_by', $creator->id)->where('is_disable', 0)->count();

        if ($currentUserCount >= $creator->total_user) {
            return ['can_create' => false, 'message' => __('You have reached the maximum user limit. Please upgrade your plan.')];
        }

        return ['can_create' => true];
    }
}

// use coupon
if (!function_exists('recordCouponUsage')) {
    function recordCouponUsage($couponId, $userId, $orderId = null)
    {
        UserCoupon::create([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId
        ]);

        return true;
    }
}

// apply coupon
if (!function_exists('applyCouponDiscount')) {
    function applyCouponDiscount($couponCode, $originalAmount, $userId = null)
    {
        $coupon = \App\Models\Coupon::where('code', $couponCode)
            ->where('status', true)
            ->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => __('Invalid coupon code')];
        }

        if ($coupon->expiry_date && $coupon->expiry_date < now()) {
            return ['valid' => false, 'message' => __('Coupon has expired')];
        }

        if ($coupon->limit) {
            $usageCount = UserCoupon::where('coupon_id', $coupon->id)->count();
            if ($usageCount >= $coupon->limit) {
                return ['valid' => false, 'message' => __('Coupon usage limit exceeded')];
            }
        }

        if ($userId && $coupon->limit_per_user) {
            $userUsageCount = UserCoupon::where('coupon_id', $coupon->id)
                ->where('user_id', $userId)->count();
            if ($userUsageCount >= $coupon->limit_per_user) {
                return ['valid' => false, 'message' => __('You have exceeded the usage limit for this coupon')];
            }
        }

        if ($coupon->minimum_spend && $originalAmount < $coupon->minimum_spend) {
            return ['valid' => false, 'message' => __('Minimum spend amount not met')];
        }

        if ($coupon->maximum_spend && $originalAmount > $coupon->maximum_spend) {
            return ['valid' => false, 'message' => __('Maximum spend amount exceeded')];
        }

        $discountAmount = 0;

        switch ($coupon->type) {
            case 'percentage':
                $discountAmount = ($originalAmount * $coupon->discount) / 100;
                break;
            case 'flat':
                $discountAmount = min($coupon->discount, $originalAmount);
                break;
            case 'fixed':
                $discountAmount = max(0, $originalAmount - $coupon->discount);
                return [
                    'valid' => true,
                    'coupon' => $coupon,
                    'discount_amount' => $coupon->discount,
                    'final_amount' => $discountAmount
                ];
        }

        $finalAmount = max(0, $originalAmount - $discountAmount);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount
        ];
    }
}

// set config email
if (!function_exists('SetConfigEmail')) {
    function SetConfigEmail($user_id = null)
    {
        try {
            if (!empty($user_id)) {
                $company_settings = getCompanyAllSetting($user_id);
            } else if (Auth::check()) {
                $company_settings = getCompanyAllSetting();
            } else {
                $user_id = User::where('type', 'superadmin')->first()->id;
                $company_settings = getCompanyAllSetting($user_id);
            }
            if(empty($company_settings['email_host'])) {
                throw new \Exception(__('Email host is not configured'));
            }

            config([
                'mail.default' => $company_settings['email_driver'] ?? 'smtp',
                'mail.mailers.smtp.host' => $company_settings['email_host'],
                'mail.mailers.smtp.port' => $company_settings['email_port'] ?? 587,
                'mail.mailers.smtp.encryption' => $company_settings['email_encryption'] ?? 'tls',
                'mail.mailers.smtp.username' => $company_settings['email_username'] ?? '',
                'mail.mailers.smtp.password' => $company_settings['email_password'] ?? '',
                'mail.from.address' => $company_settings['email_fromAddress'] ?? 'noreply@example.com',
            ]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

if (! function_exists('isAppInstalled')) {
    /**
     * App is installed if storage/installed exists OR database is migrated & seeded.
     */
    function isAppInstalled(): bool
    {
        if (\Illuminate\Support\Facades\File::exists(storage_path('installed'))) {
            return true;
        }

        try {
            if (empty(config('app.key'))) {
                return false;
            }

            if (! \Illuminate\Support\Facades\Schema::hasTable('users')
                || ! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return false;
            }

            $ready = User::where('type', 'superadmin')->exists()
                && Setting::query()->exists();

            if ($ready) {
                \Illuminate\Support\Facades\File::put(
                    storage_path('installed'),
                    'auto ' . date('Y-m-d H:i:s')
                );

                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}

if (! function_exists('isLandingPageEnabled')) {
    function isLandingPageEnabled()
    {
        return admin_setting('landingPageEnabled') === 'on';
    }
}


if (!function_exists('upload_file')) {
    function upload_file($request, $key_name, $name, $path)
    {
        try {

            $config = StorageConfigService::getStorageConfig();

            $file = $request->$key_name;
            $extension = strtolower($file->getClientOriginalExtension());
            $allowed_extensions = explode(',', $config['allowed_file_types']);
            if (empty($extension) || !in_array($extension, $allowed_extensions)) {
                return [
                    'flag' => 0,
                    'msg'  => 'The ' . $key_name . ' must be a file of type: ' .$config['allowed_file_types']. '.',
                ];
            }

            $validation = [
                'mimes:' . $config['allowed_file_types'],
                'max:' . $config['max_file_size_kb'],
            ];

            $validator = \Validator::make($request->all(), [
                $key_name => $validation
            ]);

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            DynamicStorageService::configureDynamicDisks();

            $activeDisk = StorageConfigService::getActiveDisk();

            // Store file directly to storage
            $file->storeAs( 'media/' . $path, $name, $activeDisk);

            return [
                'flag' => 1,
                'msg' => 'success',
                'url' => $path.'/'.$name
            ];

        } catch (\Exception $e) {
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('upload_base64_file')) {
    function upload_base64_file($base64_string, $name, $path)
    {
        try {
            $config = StorageConfigService::getStorageConfig();

            // Decode base64 string
            if (preg_match('/^data:([a-zA-Z0-9][a-zA-Z0-9\/+]*);base64,(.+)$/', $base64_string, $matches)) {
                $mimeType = $matches[1];
                $data = base64_decode($matches[2]);

                // Get extension from mime type
                $mimeExtensions = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/svg+xml' => 'svg',
                    'application/pdf' => 'pdf',
                    'application/msword' => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                    'application/vnd.ms-excel' => 'xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                    'text/plain' => 'txt'
                ];
                $extension = $mimeExtensions[$mimeType] ?? null;

                if (!$extension) {
                    return ['flag' => 0, 'msg' => 'Unsupported file type'];
                }

                $allowed_extensions = explode(',', $config['allowed_file_types']);
                if (!in_array($extension, $allowed_extensions)) {
                    return ['flag' => 0, 'msg' => 'File type not allowed: ' . $extension];
                }

                // Check file size
                $fileSize = strlen($data);
                $maxSizeBytes = $config['max_file_size_kb'] * 1024;
                if ($fileSize > $maxSizeBytes) {
                    return ['flag' => 0, 'msg' => 'File size exceeds limit'];
                }

                DynamicStorageService::configureDynamicDisks();
                $activeDisk = StorageConfigService::getActiveDisk();

                // Add extension to filename if not present
                $finalName = pathinfo($name, PATHINFO_EXTENSION) ? $name : $name . '.' . $extension;

                // Store file
                \Storage::disk($activeDisk)->put('media/' . $path . '/' . $finalName, $data);

                return ['flag' => 1, 'msg' => 'success', 'url' => $path . '/' . $finalName];
            }

            return ['flag' => 0, 'msg' => 'Invalid base64 format'];

        } catch (\Exception $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }
}

if (!function_exists('delete_file')) {
    function delete_file($url)
    {
        try {
            DynamicStorageService::configureDynamicDisks();
            $activeDisk = StorageConfigService::getActiveDisk();

            $filePath = 'media/' . $url;

            if (\Storage::disk($activeDisk)->exists($filePath)) {
                \Storage::disk($activeDisk)->delete($filePath);
                return [
                    'flag' => 1,
                    'msg' => 'File deleted successfully'
                ];
            }

            return [
                'flag' => 0,
                'msg' => 'File not found'
            ];

        } catch (\Exception $e) {
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('ModulePriceByName')) {
    function ModulePriceByName($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (empty($resultArray)) {
            $addons = AddOn::all()->toArray();
            foreach ($addons as $item) {
                if (isset($item['module'])) {
                    $resultArray[$item['module']]['monthly_price'] = $item['monthly_price'];
                    $resultArray[$item['module']]['yearly_price'] = $item['yearly_price'];
                }
            }
        }

        $data = $resultArray[$module_name] ?? [];
        $data['monthly_price'] = $data['monthly_price'] ?? 0;
        $data['yearly_price'] = $data['yearly_price'] ?? 0;
        return $data;
    }
}

// module alias name
if (!function_exists('ModuleAliasName')) {
    function ModuleAliasName($moduleName)
    {
        $module = (new Module())->find($moduleName);
        return $module ? ($module->alias ?? $moduleName) : $moduleName;
    }
}

if (!function_exists('parseBrowserData')) {
    function parseBrowserData(string $userAgent): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';
        $deviceType = 'desktop';

        // Browser detection
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent)) {
            $browser = 'Edge';
        }

        // OS detection
        if (preg_match('/Windows NT/', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
            $deviceType = 'mobile';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $os = 'iOS';
            $deviceType = preg_match('/iPad/', $userAgent) ? 'tablet' : 'mobile';
        }

        return [
            'browser_name' => $browser,
            'os_name' => $os,
            'browser_language' => 'en',
            'device_type' => $deviceType,
        ];
    }
}



