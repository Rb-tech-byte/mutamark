<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class DefultSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            // admin settings
            $admin = User::where('type','superadmin')->first();

            if (Setting::where('created_by', $admin->id)->exists()) {
                return;
            }

            $admin_settings = [
                // Brand Settings
                'logo_light' => 'logo_light.png',
                'logo_dark' => 'logo_dark.png',
                'favicon' => 'favicon.png',
                'titleText' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Mutamark ERP',
                'footerText' => 'Copyright © ' . (!empty(env('APP_NAME')) ? env('APP_NAME') : 'Mutamark ERP') . ' — Powered by Wolinet Technology Ltd',
                'sidebarVariant' => 'inset',
                'sidebarStyle' => 'plain',
                'themeMode' => 'light',
                'themeColor' => 'green',
                'customColor' => '#10b981',

                // System Settings
                'defaultLanguage' => 'en',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'calendarStartDay' => '0',
                'enableRegistration' => 'on',
                'enableEmailVerification' => 'off',
                'landingPageEnabled' => 'on',
                'termsConditionsUrl' => '',

                // Currency Settings
                'defaultCurrency' => 'USD',
                'currencySymbol' => '$',
                'currency_format' => '2',
                'decimalFormat' => '2',
                'decimalSeparator' => '.',
                'thousandsSeparator' => ',',
                'floatNumber' => '1',
                'currencySymbolSpace' => '0',
                'currencySymbolPosition' => 'left',

                // SEO Settings
                'metaKeywords' => 'mutamark, mutamark erp, enterprise resource planning, tanzania, procurement, 3-way matching, accounting, payment gateways, selcom, pesapal, lipa namba',
                'metaTitle' => !empty(env('APP_NAME')) ? env('APP_NAME') . ' - Integrated Enterprise Resource Planning' : 'Mutamark ERP - Integrated Enterprise Resource Planning',
                'metaDescription' => 'Mutamark ERP is an enterprise management system offering seamless accounting, 3-way matching procurement, inventory management, and integrated Tanzanian payment gateways.',
                'metaImage' => 'meta_image.png',

                // Cookie Settings
                'enableCookiePopup' => '1',
                'enableLogging' => '0',
                'strictlyNecessaryCookies' => '1',
                'cookieTitle' => 'Cookie Consent',
                'strictlyCookieTitle' => 'Strictly Necessary Cookies',
                'cookieDescription' => 'We use cookies to enhance your browsing experience and provide personalized content.',
                'strictlyCookieDescription' => 'These cookies are essential for the website to function properly.',
                'contactUsDescription' => 'If you have any questions about our cookie policy, please contact us.',
                'contactUsUrl' => 'https://example.com/contact',

                // storage Settings
                'storageType' => 'local',
                'allowedFileTypes' => 'jpg,png,webp,gif,jpeg,pdf',
                'maxUploadSize' => '2048',

            ];

            foreach($admin_settings as $key => $value){
                Setting::firstOrCreate(
                    ['key' => $key, 'created_by' => $admin->id],
                    ['value' => $value]
                );
            }

        Artisan::call('cache:clear');
    }
}
