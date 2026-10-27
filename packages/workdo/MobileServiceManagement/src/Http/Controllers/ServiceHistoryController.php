<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use Workdo\MobileServiceManagement\Models\MobileTrackingStatus;
use Workdo\MobileServiceManagement\Events\DestroyMobileServiceHistory;
use Workdo\ProductService\Models\ProductServiceItem;

class ServiceHistoryController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-mobile-service-history')) {
            $serviceHistory = MobileServiceRequest::with(['mobileService', 'deviceBrand', 'assignedTechnicians.technician:id,name'])
                ->where('is_repair_end', 1)
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-service-history')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-service-history')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('customer_name'), function ($q) {
                    $q->where('customer_name', 'like', '%' . request('customer_name') . '%');
                })
                ->when(request('mobile_service_id'), function ($q) {
                    $q->where('mobile_service_id', request('mobile_service_id'));
                })
                ->when(request('payment_status'), function ($q) {
                    $q->where('payment_status', request('payment_status'));
                })
                ->when(request('priority'), function ($q) {
                    $q->where('priority', request('priority'));
                })
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->orderBy('updated_at', 'desc'))
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $mobileServices = ProductServiceItem::where('type', 'service')
                ->where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            return Inertia::render('MobileServiceManagement/ServiceHistory/Index', [
                'serviceHistory' => $serviceHistory,
                'mobileServices' => $mobileServices
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(MobileServiceRequest $mobileServiceRequest)
    {

        if (Auth::user()->can('view-mobile-service-history')) {
            if (!$this->canAccesssServiceHistory($mobileServiceRequest)) {
                return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Access denied'));
            }
            $serviceHistoryItem = $mobileServiceRequest->load(['mobileService', 'deviceBrand', 'assignedTechnicians.technician:id,name,email,mobile_no']);

            $allTrackingStatuses = MobileTrackingStatus::where('created_by', creatorId())->orderBy('order')->get();

            return Inertia::render('MobileServiceManagement/ServiceHistory/View', [
                'serviceHistoryItem'  => $serviceHistoryItem,
                'allTrackingStatuses' => $allTrackingStatuses
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServiceRequest $mobileServiceRequest)
    {
        if (Auth::user()->can('delete-mobile-service-history')) {
            DestroyMobileServiceHistory::dispatch($mobileServiceRequest);

            $mobileServiceRequest->delete();

            return back()->with('success', __('The service history has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
    private function canAccesssServiceHistory(MobileServiceRequest $mobileServiceRequest)
    {
        if (Auth::user()->can('manage-any-mobile-service-history')) {
            return $mobileServiceRequest->created_by == creatorId();
        } elseif (Auth::user()->can('manage-own-mobile-service-history')) {
            return $mobileServiceRequest->creator_id == Auth::id() || $mobileServiceRequest->customer_id == Auth::id();
        } else {
            return false;
        }
    }
}
