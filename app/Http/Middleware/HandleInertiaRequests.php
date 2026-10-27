<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Classes\Module;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if (!$this->isInstalled()) {
            return [];
        }
        
        // Get locale and layout direction
        $locale = $request->user() ? ($request->user()->lang ?? $this->getSuperAdminLang()) : $this->getSuperAdminLang();
        app()->setLocale($locale);
        
        // Get layout direction from user or auto-detect for guests
        $rtlLanguages = ['ar', 'he'];
        $layoutDirection = $request->user() && $request->user()->layout_direction
            ? $request->user()->layout_direction
            : (in_array($locale, $rtlLanguages) ? 'rtl' : 'ltr');

        // Fast cached language file
        $availableLanguages = Cache::remember('app_available_languages_json', 86400, function () {
            $languageFile = resource_path('lang/language.json');
            if (file_exists($languageFile)) {
                $languages = json_decode(file_get_contents($languageFile), true) ?? [];
                return array_values($languages);
            }
            return [];
        });

        // Fast cached package list
        $packages = Cache::remember('app_all_packages_meta', 3600, function () {
            return (new Module())->allModules();
        });

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? array_merge(
                        $request->user()->toArray(),
                        [
                            'permissions' => $this->getUserPermissions($request->user()),
                            'roles' => $this->getUserRoles($request->user()),
                            'activatedPackages' => ActivatedModule(),
                        ]
                    )
                    : ['activatedPackages' => ActivatedModule()],
                'impersonating' => $request->session()->has('impersonator_id'),
                'lang' => $locale,
                'layout_direction' => $layoutDirection,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'packages' => $packages,
            'adminAllSetting' => $this->safeSettings(getAdminAllSetting(true)),
            'companyAllSetting' => $request->user() ? $this->safeSettings(getCompanyAllSetting($request->user()->id)) : [],
            'imageUrlPrefix' =>  getImageUrlPrefix(),
            'baseUrl' =>  url('/'),
            'currencies' => config('default_currency.currencies', []),
            'availableLanguages' => $availableLanguages,
        ];
    }

    public function onException($request, $exception)
    {
        if ($exception instanceof AuthorizationException || $exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return \Inertia\Inertia::render('errors/403', [
                'status' => 403,
                'title' => 'Access Restricted',
                'message' => 'You do not have the required permissions to access this enterprise module or perform this action.',
            ])->toResponse($request)->setStatusCode(403);
        }

        return parent::onException($request, $exception);
    }

    private function safeSettings(array $settings): array
    {
        $publicKeys = ['pusher_app_key', 'recaptcha_site_key', 'stripe_key', 'stripe_publishable_key'];
        return array_filter($settings, fn ($key) => in_array($key, $publicKeys, true) || !preg_match('/secret|password|access_token|private_key|api_key|consumer_key|credential|storage_key/i', $key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get user permissions with lightning-fast cache (eliminates 1.34s delay per navigation)
     */
    private function getUserPermissions($user): array
    {
        if (!$user) {
            return [];
        }
        $cacheKey = 'user_permissions_cache_' . $user->id;
        return Cache::remember($cacheKey, 3600, function () use ($user) {
            if (method_exists($user, 'getAllPermissions')) {
                return $user->getAllPermissions()->pluck('name')->toArray();
            }
            return [];
        });
    }

    private function getUserRoles($user): array
    {
        if (!$user) {
            return [];
        }
        $cacheKey = 'user_roles_cache_' . $user->id;
        return Cache::remember($cacheKey, 3600, function () use ($user) {
            if (method_exists($user, 'getRoleNames')) {
                return $user->getRoleNames()->toArray();
            }
            return [];
        });
    }

    /**
     * Get superadmin language if user lang is not set
     */
    private function getSuperAdminLang(): string
    {
        return admin_setting('defaultLanguage') ? admin_setting('defaultLanguage') : 'en';
    }

    private function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }
}
