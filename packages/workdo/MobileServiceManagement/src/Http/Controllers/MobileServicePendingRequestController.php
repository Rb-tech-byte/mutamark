<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use App\Models\EmailTemplate;
use Workdo\MobileServiceManagement\Events\ApproveMobileServicePendingRequest;
use Workdo\MobileServiceManagement\Events\RejectMobileServicePendingRequest;
use Workdo\MobileServiceManagement\Events\DestroyMobileServicePendingRequest;
use Workdo\ProductService\Models\ProductServiceItem;

class MobileServicePendingRequestController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-mobile-pending-requests')) {
            $mobilerequests = MobileServiceRequest::query()
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-pending-requests')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-pending-requests')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->where(function ($q) {
                    $q->orWhere('is_approve', '!=', 1);
                })
                ->with('mobileService:id,name')
                ->when(request('customer_name'), function ($q) {
                    $q->where(function($query) {
                        $query->where('customer_name', 'like', '%' . request('customer_name') . '%')
                              ->orWhere('service_id', 'like', '%' . request('customer_name') . '%');
                    });
                })
                ->when(request('email'), function ($q) {
                    $q->where('email', 'like', '%' . request('email') . '%');
                })
                ->when(request('tracking_status'), function ($q) {
                    $q->where('tracking_status', request('tracking_status'));
                })
                ->when(request('mobile_service_id'), function ($q) {
                    $q->where('mobile_service_id', request('mobile_service_id'));
                })
                ->when(request('priority'), function ($q) {
                    $q->where('priority', request('priority'));
                })
                ->when(request('request_status'), function ($q) {
                    if (request('request_status') === 'pending') {
                        $q->where('is_approve', 0);
                    } elseif (request('request_status') === 'rejected') {
                        $q->where('is_approve', 2);
                    }
                })
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $mobileServices = ProductServiceItem::where('type', 'service')
                ->where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            return Inertia::render('MobileServiceManagement/PendingRequests/Index', [
                'mobilerequests' => $mobilerequests,
                'mobileServices' => $mobileServices,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function approve(Request $request, MobileServiceRequest $pendingservicerequest)
    {
        if (Auth::user()->can('approve-mobile-pending-requests')) {
            $pendingservicerequest->is_approve = 1;
            $pendingservicerequest->save();

            ApproveMobileServicePendingRequest::dispatch($request, $pendingservicerequest);

                // Send email notification
            if (company_setting('Service Request Accept', $pendingservicerequest->created_by) == 'on') {
                $user        = Auth::user();
                $userSlug    = $user->slug ?? 'demo';
                $trackingUrl = route('mobile-service.frontend.index', $userSlug);
                $link        = '<a href="' . $trackingUrl . '">Click here to track your request</a>';

                $emailData = [
                    'tracking_id'  => $pendingservicerequest->service_id,
                    'tracking_url' => $link,
                    'company_name' => company_setting('company_name', $pendingservicerequest->created_by) ?: config('app.name'),
                    'app_name'     => config('app.name'),
                    'app_url'      => config('app.url'),
                ];
                try {
                    EmailTemplate::sendEmailTemplate('Service Request Accept', [$pendingservicerequest->email], $emailData);
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }
            }

            return back()->with('success', __('The request has been approved.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function reject(Request $request, MobileServiceRequest $pendingservicerequest)
    {
        if (Auth::user()->can('reject-mobile-pending-requests')) {
            $pendingservicerequest->is_approve = 2;
            $pendingservicerequest->save();

            RejectMobileServicePendingRequest::dispatch($request, $pendingservicerequest);

                // Send email notification
            if (company_setting('Service Request Reject', $pendingservicerequest->created_by) == 'on') {
                $emailData = [
                    'tracking_id'  => $pendingservicerequest->service_id,
                    'company_name' => company_setting('company_name', $pendingservicerequest->created_by) ?: config('app.name'),
                    'app_name'     => config('app.name'),
                    'app_url'      => config('app.url'),
                ];
                try {
                    EmailTemplate::sendEmailTemplate('Service Request Reject', [$pendingservicerequest->email], $emailData);
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }
            }

            return back()->with('success', __('The request has been rejected.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(MobileServiceRequest $pendingservicerequest)
    {
        if (Auth::user()->can('view-mobile-pending-requests')) {
            if (!$this->canAccessPendingRequest($pendingservicerequest)) {
                return redirect()->route('mobile-service-management.pending-service-requests.index')->with('error', __('Access denied'));
            }
            $pendingservicerequest->load(['mobileService:id,name', 'getServiceCreatedName:id,name']);
            return Inertia::render('MobileServiceManagement/PendingRequests/View', [
                'pendingservicerequest' => $pendingservicerequest,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServiceRequest $pendingservicerequest)
    {
        if (Auth::user()->can('delete-mobile-pending-requests')) {
            DestroyMobileServicePendingRequest::dispatch($pendingservicerequest);
            
            $pendingservicerequest->delete();
            return back()->with('success', __('The request has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function deleteAttachment(MobileServiceRequest $pendingservicerequest, Request $request)
    {
         $attachment = $request->input('attachment');
        $attachments = json_decode($pendingservicerequest->attachment, true) ?: [];
        $attachments = array_filter($attachments, fn($item) => $item !== $attachment);
        
        delete_file($attachment);
        
        $pendingservicerequest->attachment = json_encode(array_values($attachments));
        $pendingservicerequest->save();

        return back()->with('success', __('The attachment has been deleted.'));
    }
    private function canAccessPendingRequest(MobileServiceRequest $pendingservicerequest)
    {
        if (Auth::user()->can('manage-any-mobile-pending-requests')) {
            return $pendingservicerequest->created_by == creatorId();
        } elseif (Auth::user()->can('manage-owm-mobile-pending-requests')) {
            return $pendingservicerequest->creator_id == Auth::id() || $pendingservicerequest->customer_id == Auth::id();
        } else {
            return false;
        }
    }
}
