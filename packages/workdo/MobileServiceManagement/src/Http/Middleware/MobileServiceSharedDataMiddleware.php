<?php

namespace Workdo\MobileServiceManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Classes\Module;
use Workdo\MobileServiceManagement\Models\MobileServiceSetup;
use Workdo\MobileServiceManagement\Models\MobileServiceCustomPage;
use Workdo\ProductService\Models\ProductServiceItem;

class MobileServiceSharedDataMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (str_starts_with($request->route()?->getName() ?? '', 'mobile-service.frontend.')) {
            $userId = $this->getUserIdFromRequest($request);

            $user              = User::find($userId);
            $userSlug          = $request->route('userSlug');
            $sanitizedUserSlug = $userSlug ? htmlspecialchars($userSlug, ENT_QUOTES, 'UTF-8') : null;

            Inertia::share([
                'mobileServiceSettings'    => $this->getMobileServiceSettings($userId),
                'mobileServices'           => $this->getMobileServices($userId),
                'mobileServiceCustomPages' => $this->getMobileServiceCustomPages($userId),
                'companyAllSetting'        => getCompanyAllSetting($userId),
                'userSlug'                 => $sanitizedUserSlug,
                'auth'                     => [
                    'user' => ['activatedPackages' => ActivatedModule($userId ?? null)],
                    'lang' => $user && $user->lang ? $user->lang : 'en',
                ],
                'packages'       => (new Module())->allModules(),
                'imageUrlPrefix' => $user ? getImageUrlPrefix() : url('/'),
            ]);
        }

        return $next($request);
    }

    private function getUserIdFromRequest(Request $request): int
    {
        $userSlug = $request->route('userSlug');
        if ($userSlug) {
            try {
                $user = User::where('slug', $userSlug)->first();
                if ($user) {
                    return $user->id;
                }
            } catch (\Exception $e) {
                \Log::error('Database error in MobileServiceSharedDataMiddleware: ' . $e->getMessage());
                abort(500, 'Database error');
            }
        }

        abort(404, 'Mobile service page not found');
    }

    private function getMobileServiceSettings($userId): array
    {
        try {
            $settings = MobileServiceSetup::where('created_by', $userId)
                ->pluck('value', 'key')
                ->toArray();

              // Banner settings - now from nested JSON
            $bannerSettings = json_decode($settings['banner_section'] ?? '{}', true);

              // Site settings - now from nested JSON
            $siteSettings = json_decode($settings['site_settings'] ?? '{}', true);

              // Contact info
            $contactInfo = json_decode($settings['contact_section'] ?? '{}', true);
              // Home section data
            $homeSection = json_decode($settings['home_section'] ?? '{}', true);
              // About section
            $aboutSection = json_decode($settings['about_section'] ?? '{}', true);
              // Social links
            $socialLinks = json_decode($settings['social_links'] ?? '{}', true);

            return [
                'site_settings'   => $siteSettings ?? [],
                'banner_settings' => $bannerSettings ?? [],
                'contact_info'    => $contactInfo ?? [],
                'home_section'    => $homeSection ?? [],
                'about_section'   => $aboutSection ?? [],
                'social_links'    => $socialLinks ?? [],
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching settings: ' . $e->getMessage());

            return [
                'site_settings'   => [],
                'banner_settings' => [],
                'contact_info'    => [],
                'home_section'    => [],
                'about_section'   => [],
                'social_links'    => [],
            ];
        }
    }

    private function getMobileServices($userId): array
    {
        try {

              // Check total services for this user
            $totalServices = ProductServiceItem::where('type', 'service')->where('created_by', $userId)->count();

              // Get all services for debugging
            $allServices = ProductServiceItem::where('type', 'service')->where('created_by', $userId)
                ->select('id', 'name', 'is_active')
                ->get()
                ->toArray();

            return ProductServiceItem::where('type', 'service')->where('created_by', $userId)
                ->where('is_active', 1)
                ->select('id', 'name', 'description', 'sale_price', 'image')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMobileServiceCustomPages($userId): array
    {
        try {
            return MobileServiceCustomPage::where('created_by', $userId)
                ->where('enable_page_footer', 'on')
                ->select('id', 'name', 'title', 'slug')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
