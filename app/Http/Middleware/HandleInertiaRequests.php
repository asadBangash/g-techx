<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cookie;
use App\Classes\Module;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $base = parent::share($request);

        if (! isAppInstalled()) {
            return $base;
        }

        try {
            return array_merge($base, $this->sharedAppData($request));
        } catch (\Throwable $e) {
            report($e);

            return array_merge($base, $this->fallbackSharedData($request));
        }
    }

    private function sharedAppData(Request $request): array
    {
        $locale = $request->user()->lang ?? $this->getSuperAdminLang();

        if (config('app.is_demo') && Cookie::get('language')) {
            $locale = Cookie::get('language');
        }

        app()->setLocale($locale);

        $languageFile = resource_path('lang/language.json');
        $defaultLanguages = [];
        if (file_exists($languageFile)) {
            $languages = json_decode(file_get_contents($languageFile), true) ?? [];
            $defaultLanguages = array_values($languages);
        }

        $packages = [];
        try {
            $packages = (new Module())->allModules();
        } catch (\Throwable $e) {
            report($e);
        }

        $activatedPackages = [];
        try {
            $activatedPackages = ActivatedModule();
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'auth' => [
                'user' => $request->user()
                    ? array_merge(
                        $request->user()->toArray(),
                        [
                            'permissions' => $this->getUserPermissions($request->user()),
                            'roles' => $this->getUserRoles($request->user()),
                            'activatedPackages' => $activatedPackages,
                        ]
                    )
                    : ['activatedPackages' => $activatedPackages],
                'impersonating' => $request->session()->has('impersonator_id'),
                'lang' => $locale,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'packages' => $packages,
            'adminAllSetting' => $request->user() ? getAdminAllSetting() : getAdminAllSetting(true),
            'companyAllSetting' => $request->user() ? getCompanyAllSetting($request->user()->id) : [],
            'imageUrlPrefix' => getImageUrlPrefix(),
            'baseUrl' => url('/'),
            'currencies' => config('default_currency.currencies', []),
            'defaultLanguages' => $defaultLanguages,
            'is_demo' => config('app.is_demo', false),
            'brand' => config('brand', []),
            'brandLogoUrl' => asset(config('brand.logo', 'assets/brand/gtechx-logo.png')),
        ];
    }

    private function fallbackSharedData(Request $request): array
    {
        return [
            'auth' => [
                'user' => $request->user(),
                'impersonating' => $request->session()->has('impersonator_id'),
                'lang' => 'en',
                'activatedPackages' => [],
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'packages' => [],
            'adminAllSetting' => [],
            'companyAllSetting' => [],
            'imageUrlPrefix' => url('/storage/media/'),
            'baseUrl' => url('/'),
            'currencies' => config('default_currency.currencies', []),
            'defaultLanguages' => [],
            'is_demo' => config('app.is_demo', false),
            'brand' => config('brand', [
                'short_name' => 'G-TechX',
                'logo' => 'assets/brand/gtechx-logo.png',
            ]),
            'brandLogoUrl' => asset('assets/brand/gtechx-logo.png'),
        ];
    }

    public function onException($request, $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return redirect()->route('users.index')->with('error', 'Permission denied');
        }

        return parent::onException($request, $exception);
    }

    private function getUserPermissions($user): array
    {
        if (method_exists($user, 'getAllPermissions')) {
            return $user->getAllPermissions()->pluck('name')->toArray();
        }
        return [];
    }

    private function getUserRoles($user): array
    {
        if (method_exists($user, 'getRoleNames')) {
            return $user->getRoleNames()->toArray();
        }
        return [];
    }

    private function getSuperAdminLang(): string
    {
        try {
            return admin_setting('defaultLanguage') ?: 'en';
        } catch (\Throwable $e) {
            return 'en';
        }
    }
}
