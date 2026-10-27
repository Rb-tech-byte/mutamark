<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use App\Models\AddOn;
use App\Models\User;
use Workdo\LandingPage\Models\MarketplaceSetting;
use Workdo\LandingPage\Models\LandingPageSetting;
use Illuminate\Support\Facades\Auth;
use Workdo\LandingPage\Http\Requests\StoreMarketplaceSettingRequest;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('landing.page');
    }

    public function settings(Request $request)
    {
        return redirect()->route('landing-page.index');
    }

    public function storeSettings(StoreMarketplaceSettingRequest $request)
    {
        if(Auth::user()->can('manage-marketplace-settings')){
            $validated = $request->validated();

            // Handle image paths - store only filename
            if (isset($validated['config_sections']['sections'])) {
                $this->processImagePaths($validated['config_sections']['sections']);
            }

            MarketplaceSetting::updateOrCreate(['module' => $validated['module']], $validated);
        
            return back()->with('success', __('Marketplace settings saved successfully'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    private function processImagePaths(&$sections)
    {
        foreach ($sections as $sectionKey => &$sectionData) {
            if (is_array($sectionData)) {
                // Handle hero image
                if (isset($sectionData['image'])) {
                    $sectionData['image'] = $this->processImagePath($sectionData['image']);
                }
                
                // Handle screenshots images array
                if (isset($sectionData['images']) && is_array($sectionData['images'])) {
                    $sectionData['images'] = array_map([$this, 'processImagePath'], $sectionData['images']);
                }
                
                // Handle dedication subSections screenshots
                if (isset($sectionData['subSections']) && is_array($sectionData['subSections'])) {
                    foreach ($sectionData['subSections'] as &$subSection) {
                        if (isset($subSection['screenshot'])) {
                            $subSection['screenshot'] = $this->processImagePath($subSection['screenshot']);
                        }
                    }
                }
            }
        }
    }

    private function processImagePath($imagePath)
    {
        if (strpos($imagePath, 'packages/workdo') !== false) {
            return $imagePath;
        }
        return basename($imagePath);
    }


}