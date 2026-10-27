<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceSetup;
use Workdo\MobileServiceManagement\Http\Requests\StoreSiteSettingsRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreBannerSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreHomeSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreAboutSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreAboutTitleSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreContactUsSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreSocialLinksSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreFaqsSectionRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreCustomPagesSectionRequest;
use Workdo\MobileServiceManagement\Models\MobileServiceCustomPage;

class MobileServiceSetupController extends Controller
{
      // Site Settings
    public function siteSettingsIndex()
    {
        if(Auth::user()->can('manage-mobile-site-settings')){
            $siteSettings = MobileServiceSetup::where('created_by', creatorId())
                ->where('key', 'site_settings')
                ->first();

            $settings = $siteSettings ? json_decode($siteSettings->value, true) : [];

            return Inertia::render('MobileServiceManagement/SystemSetup/SiteSettings', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function siteSettingsStore(StoreSiteSettingsRequest $request)
    {
        if(Auth::user()->can('edit-mobile-site-settings')){
            $validated = $request->validated();

            $site_logo   = null;
            $footer_logo = null;
            $favicon     = null;

            if (!empty($validated['site_logo'])) {
                $site_logo = basename($validated['site_logo']);
            }
            if (!empty($validated['footer_logo'])) {
                $footer_logo = basename($validated['footer_logo']);
            }
            if (!empty($validated['favicon'])) {
                $favicon = basename($validated['favicon']);
            }

            $siteSettings = [
                'site_logo'          => $site_logo,
                'footer_logo'        => $footer_logo,
                'favicon'            => $favicon,
                'title'              => $validated['title'],
                'subtitle'           => $validated['subtitle'],
                'footer_text'        => $validated['footer_text'],
                'footer_description' => $validated['footer_description'],
            ];

            MobileServiceSetup::updateOrCreate(
                ['key' => 'site_settings', 'created_by' => creatorId()],
                [
                    'value'      => json_encode($siteSettings),
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]
            );

            return redirect()->back()->with('success', __('The site setting details have been saved successfully.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // Banner Section
    public function bannerSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-banner-section')){
            $bannerSection = MobileServiceSetup::where('created_by', creatorId())
                ->where('key', 'banner_section')
                ->first();

            $settings = $bannerSection ? json_decode($bannerSection->value, true) : [];

            return Inertia::render('MobileServiceManagement/SystemSetup/BannerSection', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function bannerSectionStore(StoreBannerSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-banner-section')){
            $validated = $request->validated();

            $image = null;
            if (!empty($validated['image'])) {
                $image = basename($validated['image']);
            }

            $bannerSection = [
                'banner_title'    => $validated['banner_title'],
                'banner_subtitle' => $validated['banner_subtitle'],
                'image'           => $image,
                'image_icon'      => $validated['image_icon'],
                'image_text'      => $validated['image_text'],
                'description'     => $validated['description'],
            ];

            MobileServiceSetup::updateOrCreate(
                ['key' => 'banner_section', 'created_by' => creatorId()],
                [
                    'value'      => json_encode($bannerSection),
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]
            );

            return redirect()->back()->with('success', __('The banner section details have been saved successfully.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // About Section
    public function aboutSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-about--section')){
            $aboutSection = MobileServiceSetup::where('created_by', creatorId())
                ->where('key', 'about_section')
                ->first();

            $settings = $aboutSection ? json_decode($aboutSection->value, true) : [];

            return Inertia::render('MobileServiceManagement/SystemSetup/AboutSection', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function aboutSectionStore(StoreAboutSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-about--section')){
            $validated = $request->validated();

            $image = null;
            if (!empty($validated['about_image'])) {
                $image = basename($validated['about_image']);
            }

            $aboutSection = [
                'about_title'       => $validated['about_title'],
                'about_description' => $validated['about_description'],
                'about_image'       => $image,
                'about_image_text'  => $validated['about_image_text'],
                'about_facilities'  => $validated['about_facilities'],
            ];

            MobileServiceSetup::updateOrCreate(
                ['key' => 'about_section', 'created_by' => creatorId()],
                [
                    'value'      => json_encode($aboutSection),
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]
            );

            return redirect()->back()->with('success', __('The about section details have been saved successfully.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // About Title Section
    public function aboutTitleSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-about-title-section')){
            $aboutTitleSection = MobileServiceSetup::where('created_by', creatorId())
                ->where('key', 'about_title_section')
                ->first();

            $settings = $aboutTitleSection ? json_decode($aboutTitleSection->value, true) : [];
            $users    = User::where('created_by', creatorId())->get(['id', 'name']);

            return Inertia::render('MobileServiceManagement/SystemSetup/AboutTitleSection', [
                'settings' => $settings,
                'users'    => $users
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function aboutTitleSectionStore(StoreAboutTitleSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-about-title-section')){
            $validated = $request->validated();

            $story_image = null;
            if (!empty($validated['story_image'])) {
                $story_image = basename($validated['story_image']);
            }

            $aboutTitleSection = [
                'story_title'           => $validated['story_title'],
                'story_description'     => $validated['story_description'],
                'story_image'           => $story_image,
                'about_cta_title'       => $validated['about_cta_title'],
                'about_cta_description' => $validated['about_cta_description'],
                'mission_title'         => $validated['mission_title'],
                'mission_description'   => $validated['mission_description'],
                'missions'              => $validated['missions'],
                'team_title'            => $validated['team_title'],
                'team_description'      => $validated['team_description'],
                'team_members'          => $validated['team_members'],
            ];

            MobileServiceSetup::updateOrCreate(
                ['key' => 'about_title_section', 'created_by' => creatorId()],
                [
                    'value'      => json_encode($aboutTitleSection),
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]
            );

            return redirect()->back()->with('success', __('The about title section details have been saved successfully.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // Custom Pages Section
    public function customPagesSectionIndex()
    {
        if(Auth::user()->can('manage-custom-pages-section')){
            $customPages = MobileServiceCustomPage::where('created_by', creatorId())->get();

            return Inertia::render('MobileServiceManagement/SystemSetup/CustomPagesSection', [
                'customPages' => $customPages
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

      // FAQs Section
    public function faqsSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-faqs-section')){
            $settings = MobileServiceSetup::where('created_by', creatorId())
                ->whereIn('key', ['faqs'])
                ->pluck('value', 'key')
                ->toArray();

            return Inertia::render('MobileServiceManagement/SystemSetup/FaqsSection', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function faqsSectionStore(StoreFaqsSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-faqs-section')){
            $validated = $request->validated();

            $settings = [
                'faqs' => json_encode($validated['faqs']),
            ];

            foreach($settings as $key => $value) {
                MobileServiceSetup::updateOrCreate(
                    ['key' => $key, 'created_by' => creatorId()],
                    [
                        'value'      => $value,
                        'creator_id' => Auth::id(),
                        'created_by' => creatorId()
                    ]
                );
            }

            return redirect()->back()->with('success', __('The FAQs details have been saved successfully.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // Social Links Section
    public function socialLinksSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-social-links-section')){
            $settings = MobileServiceSetup::where('created_by', creatorId())
                ->whereIn('key', ['social_links'])
                ->pluck('value', 'key')
                ->toArray();

            return Inertia::render('MobileServiceManagement/SystemSetup/SocialLinksSection', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function socialLinksSectionStore(StoreSocialLinksSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-social-links-section')){
            $validated = $request->validated();

            $settings = [
                'social_links' => json_encode($validated['social_links']),
            ];

            foreach($settings as $key => $value) {
                MobileServiceSetup::updateOrCreate(
                    ['key' => $key, 'created_by' => creatorId()],
                    [
                        'value'      => $value,
                        'creator_id' => Auth::id(),
                        'created_by' => creatorId()
                    ]
                );
            }

            return redirect()->back()->with('success', __('The social link details have been saved successfully..'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // Contact Us Section
    public function contactUsSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-contact-us-section')){
            $contactSection = MobileServiceSetup::where('created_by', creatorId())
                ->where('key', 'contact_section')
                ->first();

            $settings = $contactSection ? json_decode($contactSection->value, true) : [];

            return Inertia::render('MobileServiceManagement/SystemSetup/ContactUsSection', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function contactUsSectionStore(StoreContactUsSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-contact-us-section')){
            $validated = $request->validated();

            $contactSection = [
                'contact_title'             => $validated['contact_title'],
                'contact_phone'             => $validated['contact_phone'],
                'contact_email'             => $validated['contact_email'],
                'contact_emergency'         => $validated['contact_emergency'],
                'location_title'            => $validated['location_title'],
                'location_address'          => $validated['location_address'],
                'repair_center_title'       => $validated['repair_center_title'],
                'repair_center_description' => $validated['repair_center_description'],
                'repair_center_map_code'    => $validated['repair_center_map_code'],
                'hours_title'               => $validated['hours_title'],
                'service_hours'             => json_encode($validated['service_hours']),
            ];

            MobileServiceSetup::updateOrCreate(
                ['key' => 'contact_section', 'created_by' => creatorId()],
                [
                    'value'      => json_encode($contactSection),
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]
            );

            return redirect()->back()->with('success', __('The contact us details have been saved successfully..'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

      // Home Section
    public function homeSectionIndex()
    {
        if(Auth::user()->can('manage-mobile-home-section')){
            $homeSection = MobileServiceSetup::where('created_by', creatorId())
                ->where('key', 'home_section')
                ->first();

            $settings = $homeSection ? json_decode($homeSection->value, true) : [];

            return Inertia::render('MobileServiceManagement/SystemSetup/HomeSection', [
                'settings' => $settings
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function homeSectionStore(StoreHomeSectionRequest $request)
    {
        if(Auth::user()->can('edit-mobile-home-section')){
            $validated = $request->validated();

            $homeSection = [
                'service_tracking_title'       => $validated['service_tracking_title'],
                'service_tracking_description' => $validated['service_tracking_description'],
                'expertise_title'              => $validated['expertise_title'],
                'expertise_description'        => $validated['expertise_description'],
                'cta_title'                    => $validated['cta_title'],
                'cta_description'              => $validated['cta_description'],
                'testimonial_title'            => $validated['testimonial_title'],
                'testimonial_description'      => $validated['testimonial_description'],
            ];

            MobileServiceSetup::updateOrCreate(
                ['key' => 'home_section', 'created_by' => creatorId()],
                [
                    'value'      => json_encode($homeSection),
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]
            );

            return redirect()->back()->with('success', __('The home sectoin details have been saved successfully..'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}