<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use Workdo\MobileServiceManagement\Events\CreateMobileServiceRequest;
use Workdo\MobileServiceManagement\Events\UpdateMobileServiceRequest;
use Workdo\MobileServiceManagement\Events\DestroyMobileServiceRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServiceBookingRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileServiceBookingRequest;
use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Workdo\MobileServiceManagement\Models\MobileTrackingStatus;
use Workdo\MobileServiceManagement\Models\MobileServiceLiveTracking;
use Workdo\MobileServiceManagement\Http\Requests\AssignTechnicianRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateTrackingStatusRequest;
use Workdo\MobileServiceManagement\Models\MobileServiceAssignTechnician;
use App\Models\User;
use App\Models\EmailTemplate;
use Workdo\ProductService\Models\ProductServiceItem;
use Illuminate\Http\Request;

class MobileServiceRequestController extends Controller
{

    public function index()
    {
        if (Auth::user()->can('manage-mobile-service-requests')) {
            $mobilerequests = MobileServiceRequest::query()
                ->where('is_approve', 1)
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-service-requests')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-service-requests')) {
                        // For all other users, show requests they are involved in
                        $q->where(function ($subQ) {
                            // Show own created requests
                            $subQ->where('creator_id', Auth::id());
                            // OR show assigned requests (for technicians)
                            $subQ->orWhereHas('assignedTechnicians', function ($techQ) {
                                $techQ->where('technician_id', Auth::id());
                            });
                        });
                    } else {
                        // If no permissions, show nothing
                        $q->whereRaw('1 = 0');
                    }
                })
                ->with(['trackingStatus:id,status_name,status_color', 'assignedTechnicians.technician:id,name', 'mobileService:id,name'])
                ->where(function ($q) {
                    $q->orWhere('is_repair_end', 0);
                })
                ->when(request('customer_name'), function ($q) {
                    $q->where('customer_name', 'like', '%' . request('customer_name') . '%');
                })
                ->when(request('email'), function ($q) {
                    $q->where('email', 'like', '%' . request('email') . '%');
                })
                ->when(request('tracking_status'), function ($q) {
                    $q->where('tracking_status', request('tracking_status'));
                })
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $devicebrands   = MobileDeviceBrand::where('created_by', creatorId())->select('id', 'name')->get();
            $mobileservices = ProductServiceItem::where('type', 'service')->where('created_by', creatorId())->select('id', 'name')->get();
            $allUsers       = User::where('created_by', creatorId())->emp()->select('id', 'name')->get();
            return Inertia::render('MobileServiceManagement/ServiceRequests/Index', [
                'mobilerequests' => $mobilerequests,
                'mobileservices' => $mobileservices,
                'devicebrands'   => $devicebrands,
                'allUsers'       => $allUsers,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('view-mobile-service-requests')) {
            if (!$this->canAccesssServiceRequest($mobileservicerequest)) {
                return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Access denied'));
            }
            $trackingstatuses    = MobileTrackingStatus::where('created_by', creatorId())->select('id', 'status_name', 'status_color', 'order')->get();
            $allTrackingStatuses = MobileTrackingStatus::where('created_by', creatorId())->orderBy('order', 'asc')->get();

            return Inertia::render('MobileServiceManagement/ServiceRequests/View', [
                'mobileservicerequest' => $mobileservicerequest->load(['deviceBrand:id,name', 'trackingStatus:id,status_name,status_color', 'mobileService:id,name']),
                'trackingstatuses'     => $trackingstatuses,
                'allTrackingStatuses'  => $allTrackingStatuses
            ]);
        } else {
            return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('create-mobile-service-requests')) {
            $devicebrands   = MobileDeviceBrand::where('created_by', creatorId())->select('id', 'name')->get();
            $mobileservices = ProductServiceItem::where('type', 'service')->where('created_by', creatorId())->select('id', 'name', 'sale_price')->get();

            return Inertia::render('MobileServiceManagement/ServiceRequests/Create', [
                'mobileservices' => $mobileservices,
                'devicebrands'   => $devicebrands,
            ]);
        } else {
            return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Permission denied'));
        }
    }

    public function store(StoreMobileServiceBookingRequest $request)
    {
        if (Auth::user()->can('create-mobile-service-contracts')) {
            $validated = $request->validated();

            if (Auth::user()->type == 'company') {
                $isApproveStatus = 1;
                $createdbyId     = creatorId();
            } else {
                $isApproveStatus = null;
                $createdbyId     = Auth::user()->id;
            }
            $defaultTrackingStatus = MobileTrackingStatus::where('created_by', creatorId())
                ->orderBy('id', 'asc')
                ->first();
            $booking                      = new MobileServiceRequest();
            $booking->service_id          = time();
            $booking->customer_name       = $validated['firstName'];
            $booking->email               = $validated['email'];
            $booking->mobile_no           = $validated['phone'];
            $booking->address             = $validated['address'];
            $booking->priority            = $validated['urgency'];
            $booking->mobile_model        = $validated['deviceModel'];
            $booking->description         = $validated['issueDescription'];
            $booking->special_requests    = $validated['specialRequests'];
            $booking->mobile_service_id   = $validated['serviceType'];
            $booking->device_brand        = $validated['deviceBrand'];
            $booking->tracking_status     = $defaultTrackingStatus ? $defaultTrackingStatus->id : null;
            $booking->tracking_status_log = $defaultTrackingStatus ? [$defaultTrackingStatus->id] : [];
            $booking->is_approve          = $isApproveStatus;
            $booking->creator_id          = Auth::id();
            $booking->created_by          = $createdbyId;

            // Handle multiple images from media library
            if (!empty($validated['attachments'])) {
                $imageNames          = array_map('basename', $validated['attachments']);
                $booking->attachment = json_encode($imageNames);
            }
            $booking->save();

            CreateMobileServiceRequest::dispatch($request, $booking);

            // Send email notification
            if (company_setting('New Mobile Service Request') == 'on') {
                $user        = Auth::user();
                $userSlug    = $user->slug ?? 'demo';
                $trackingUrl = route('mobile-service.frontend.index', ['userSlug' => $userSlug, 'tracking_id' => $booking->service_id]);
                $link        = '<a href="' . $trackingUrl . '">Click here to track your request</a>';

                $emailData = [
                    'tracking_id'  => $booking->service_id,
                    'tracking_url' => $link,
                    'company_name' => company_setting('company_name') ?: config('app.name'),
                    'app_name'     => config('app.name'),
                    'app_url'      => config('app.url'),
                ];
                try {
                    EmailTemplate::sendEmailTemplate('New Mobile Service Request', [$booking->email], $emailData);
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }
            }

            return redirect()->route('mobile-service-management.service-requests.index')->with('success', __('The service request has been created successfully.'));
        } else {
            return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Permission denied'));
        }
    }
    public function edit(MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('edit-mobile-service-requests') && $mobileservicerequest->created_by == creatorId()) {
            $devicebrands   = MobileDeviceBrand::where('created_by', creatorId())->select('id', 'name')->get();
            $mobileservices = ProductServiceItem::where('type', 'service')->where('created_by', creatorId())->select('id', 'name', 'sale_price')->get();

            return Inertia::render('MobileServiceManagement/ServiceRequests/Edit', [
                'mobileservicerequest' => $mobileservicerequest,
                'mobileservices'       => $mobileservices,
                'devicebrands'         => $devicebrands,
            ]);
        } else {
            return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateMobileServiceBookingRequest $request, MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('edit-mobile-service-requests')) {
            $validated = $request->validated();

            $mobileservicerequest->customer_name     = $validated['firstName'];
            $mobileservicerequest->email             = $validated['email'];
            $mobileservicerequest->mobile_no         = $validated['phone'];
            $mobileservicerequest->address           = $validated['address'];
            $mobileservicerequest->priority          = $validated['urgency'];
            $mobileservicerequest->mobile_model      = $validated['deviceModel'];
            $mobileservicerequest->description       = $validated['issueDescription'];
            $mobileservicerequest->special_requests  = $validated['specialRequests'];
            $mobileservicerequest->mobile_service_id = $validated['serviceType'];
            $mobileservicerequest->device_brand      = $validated['deviceBrand'];


            // Handle multiple images from media library
            if (!empty($validated['attachments'])) {
                $imageNames                       = array_map('basename', $validated['attachments']);
                $mobileservicerequest->attachment = json_encode($imageNames);
            }

            $mobileservicerequest->save();

            UpdateMobileServiceRequest::dispatch($request, $mobileservicerequest);

            return redirect()->route('mobile-service-management.service-requests.index')->with('success', __('The service request has been updated successfully.'));
        } else {
            return redirect()->route('mobile-service-management.service-requests.index')->with('error', __('Permission denied'));
        }
    }

    public function updateTrackingStatus(UpdateTrackingStatusRequest $request, MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('tracking-mobile-service-requests')) {
            $validated = $request->validated();

            $mobileservicerequest->tracking_status = $validated['tracking_status'];

            // Update tracking status log as array
            $currentLog = is_array($mobileservicerequest->tracking_status_log) ? $mobileservicerequest->tracking_status_log : [];
            if (!in_array((int)$validated['tracking_status'], $currentLog)) {
                $currentLog[]                              = (int)$validated['tracking_status'];
                $mobileservicerequest->tracking_status_log = $currentLog;
            }

            $mobileservicerequest->save();

            // Store in MobileServiceLiveTracking
            $liveTracking                     = new MobileServiceLiveTracking();
            $liveTracking->service_id         = $mobileservicerequest->service_id;
            $liveTracking->tracking_status_id = $validated['tracking_status'];
            $liveTracking->date               = now();
            $liveTracking->creator_id         = Auth::id();
            $liveTracking->created_by         = creatorId();
            $liveTracking->save();

            return redirect()->back()->with('success', __('The status has been updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function assignTechnician(AssignTechnicianRequest $request, MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('assign-mobile-service-technician')) {
            $validated = $request->validated();

            $existingAssignment = MobileServiceAssignTechnician::where('mobile_service_id', $mobileservicerequest->id)->first();

            if ($existingAssignment) {
                $existingAssignment->technician_id   = $validated['technician_id'];
                $existingAssignment->completion_date = $validated['estimated_completion_date'];
                $existingAssignment->save();
            } else {
                $assignment                     = new MobileServiceAssignTechnician();
                $assignment->mobile_service_id  = $mobileservicerequest->id;
                $assignment->technician_id      = $validated['technician_id'];
                $assignment->completion_date    = $validated['estimated_completion_date'];
                $assignment->creator_id         = Auth::id();
                $assignment->created_by         = creatorId();
                $assignment->save();
            }

            $mobileservicerequest->is_technician_asign = 1;
            $mobileservicerequest->save();

            return redirect()->back()->with('success', __('Technician assigned successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function endRepair(MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('end-mobile-service-repair')) {
            $mobileservicerequest->is_repair_end = 1;
            $mobileservicerequest->save();
            return back()->with('success', __('Repair ended successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function deleteAttachment(MobileServiceRequest $mobileservicerequest, Request $request)
    {
        $attachment = $request->input('attachment');
        $attachments = json_decode($mobileservicerequest->attachment, true) ?: [];
        $attachments = array_filter($attachments, fn($item) => $item !== $attachment);
        
        $mobileservicerequest->attachment = json_encode(array_values($attachments));
        $mobileservicerequest->save();

        return back()->with('success', __('The attachment has been deleted.'));
    }

    public function destroy(MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('delete-mobile-service-requests')) {
            DestroyMobileServiceRequest::dispatch($mobileservicerequest);
            $mobileservicerequest->delete();

            return redirect()->route('mobile-service-management.service-requests.index')->with('success', __('The service request has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
    private function canAccesssServiceRequest(MobileServiceRequest $mobileservicerequest)
    {
        if (Auth::user()->can('manage-any-mobile-service-requests')) {
            return $mobileservicerequest->created_by == creatorId();
        } elseif (Auth::user()->can('manage-own-mobile-service-requests')) {
            return $mobileservicerequest->creator_id == Auth::id() 
            || $mobileservicerequest->customer_id == Auth::id()
            || $mobileservicerequest->assignedTechnicians()
                ->where('technician_id', Auth::id())
                ->exists();
        } else {
            return false;
        }
    }
}
