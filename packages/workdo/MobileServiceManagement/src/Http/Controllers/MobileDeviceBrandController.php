<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileDeviceBrandRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileDeviceBrandRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Events\CreateMobileDeviceBrand;
use Workdo\MobileServiceManagement\Events\UpdateMobileDeviceBrand;
use Workdo\MobileServiceManagement\Events\DestroyMobileDeviceBrand;


class MobileDeviceBrandController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-mobile-device-brands')) {
            $mobiledevicebrands = MobileDeviceBrand::select('id', 'name', 'created_at')
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-device-brands')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-device-brands')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('MobileServiceManagement/SystemSetup/MobileDeviceBrands/Index', [
                'mobiledevicebrands' => $mobiledevicebrands,

            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreMobileDeviceBrandRequest $request)
    {
        if (Auth::user()->can('create-mobile-device-brands')) {
            $validated = $request->validated();

            $mobiledevicebrand       = new MobileDeviceBrand();
            $mobiledevicebrand->name = $validated['name'];

            $mobiledevicebrand->creator_id = Auth::id();
            $mobiledevicebrand->created_by = creatorId();
            $mobiledevicebrand->save();

            CreateMobileDeviceBrand::dispatch($request, $mobiledevicebrand);

            return redirect()->route('mobile-service.device-brands.index')->with('success', __('The device brand has been created successfully.'));
        } else {
            return redirect()->route('mobile-service.device-brands.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateMobileDeviceBrandRequest $request, MobileDeviceBrand $mobiledevicebrand)
    {
        if (Auth::user()->can('edit-mobile-device-brands')) {
            $validated = $request->validated();

            $mobiledevicebrand->name = $validated['name'];

            $mobiledevicebrand->save();

            UpdateMobileDeviceBrand::dispatch($request, $mobiledevicebrand);

            return back()->with('success', __('The device brand details are updated successfully.'));
        } else {
            return redirect()->route('mobile-service.device-brands.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileDeviceBrand $mobiledevicebrand)
    {
        if (Auth::user()->can('delete-mobile-device-brands')) {
            DestroyMobileDeviceBrand::dispatch($mobiledevicebrand);

            $mobiledevicebrand->delete();
            return back()->with('success', __('The device brand has been deleted.'));
        } else {
            return redirect()->route('mobile-service.device-brands.index')->with('error', __('Permission denied'));
        }
    }
}
