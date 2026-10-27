<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Workdo\MobileServiceManagement\Models\MobileTrackingStatus;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileTrackingStatusRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileTrackingStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Events\CreateMobileTrackingStatus;
use Workdo\MobileServiceManagement\Events\UpdateMobileTrackingStatus;
use Workdo\MobileServiceManagement\Events\DestroyMobileTrackingStatus;


class MobileTrackingStatusController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-mobile-tracking-statuses')){
            $mobiletrackingstatuses = MobileTrackingStatus::select('id', 'icon_name', 'status_color', 'status_name', 'order', 'created_at')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-mobile-tracking-statuses')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-mobile-tracking-statuses')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('MobileServiceManagement/SystemSetup/MobileTrackingStatuses/Index', [
                'mobiletrackingstatuses' => $mobiletrackingstatuses,

            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreMobileTrackingStatusRequest $request)
    {
        if(Auth::user()->can('create-mobile-tracking-statuses')){
            $validated = $request->validated();



            $mobiletrackingstatus               = new MobileTrackingStatus();
            $mobiletrackingstatus->icon_name    = $validated['icon_name'];
            $mobiletrackingstatus->status_color = $validated['status_color'];
            $mobiletrackingstatus->status_name  = $validated['status_name'];

            $mobiletrackingstatus->creator_id = Auth::id();
            $mobiletrackingstatus->created_by = creatorId();
            $mobiletrackingstatus->save();

            CreateMobileTrackingStatus::dispatch($request, $mobiletrackingstatus);

            return redirect()->route('mobile-service.tracking-statuses.index')->with('success', __('The tracking status has been created successfully.'));
        }
        else{
            return redirect()->route('mobile-service.tracking-statuses.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateMobileTrackingStatusRequest $request, MobileTrackingStatus $mobiletrackingstatus)
    {
        if(Auth::user()->can('edit-mobile-tracking-statuses')){
            $validated = $request->validated();



            $mobiletrackingstatus->icon_name    = $validated['icon_name'];
            $mobiletrackingstatus->status_color = $validated['status_color'];
            $mobiletrackingstatus->status_name  = $validated['status_name'];

            $mobiletrackingstatus->save();

            UpdateMobileTrackingStatus::dispatch($request, $mobiletrackingstatus);

            return redirect()->route('mobile-service.tracking-statuses.index')->with('success', __('The tracking status details are updated successfully.'));
        }
        else{
            return redirect()->route('mobile-service.tracking-statuses.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileTrackingStatus $mobiletrackingstatus)
    {
        if(Auth::user()->can('delete-mobile-tracking-statuses')){
            DestroyMobileTrackingStatus::dispatch($mobiletrackingstatus);
            
            $mobiletrackingstatus->delete();

            return redirect()->route('mobile-service.tracking-statuses.index')->with('success', __('The tracking status has been deleted.'));
        }
        else{
            return redirect()->route('mobile-service.tracking-statuses.index')->with('error', __('Permission denied'));
        }
    }

    public function updateOrder(Request $request)
    {
        if(Auth::user()->can('edit-mobile-tracking-statuses')){
            $statusIds = $request->input('status_ids', []);

            foreach ($statusIds as $index => $statusId) {
                MobileTrackingStatus::where('id', $statusId)
                    ->where('created_by', creatorId())
                    ->update(['order' => $index + 1]);
            }

            return redirect()->back()->with('success', __('The order has been updated successfully.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}