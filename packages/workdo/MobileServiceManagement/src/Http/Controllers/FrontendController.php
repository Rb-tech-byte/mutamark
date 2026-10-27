<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceCustomPage;
use Workdo\MobileServiceManagement\Models\MobileServiceReview;
use Workdo\MobileServiceManagement\Models\MobileServiceContact;
use Workdo\MobileServiceManagement\Models\MobileServiceSetup;
use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServiceReviewRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServiceContactRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServiceBookingRequest;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use App\Models\User;
use Workdo\MobileServiceManagement\Models\MobileTrackingStatus;
use Workdo\MobileServiceManagement\Models\MobileServicePayment;
use Workdo\ProductService\Models\ProductServiceItem;

class FrontendController extends Controller
{
    private function getUserIdFromRequest($request = null)
    {
        if ($request && $request->route('userSlug')) {
            $user = User::where('slug', $request->route('userSlug'))->first();
            if ($user) {
                return $user->id;
            }
        }
        return creatorId();
    }

    public function index(Request $request)
    {
        $userId       = $this->getUserIdFromRequest($request);
        $testimonials = MobileServiceReview::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'name'       => $review->name,
                    'review'     => $review->review,
                    'rating'     => $review->rating,
                    'created_at' => $review->created_at
                ];
            });

              // Calculate rating statistics
        $totalReviews        = MobileServiceReview::where('created_by', $userId)->count();
        $averageRating       = $totalReviews > 0 ? round(MobileServiceReview::where('created_by', $userId)->avg('rating'), 1) :   0;
        $fiveStarReviews     = MobileServiceReview::where('created_by', $userId)->where('rating', 5)->count();
        $totalServices       = ProductServiceItem::where('created_by', $userId)->count();
        $totalDeviceRepaired = MobileServiceRequest::where('created_by', $userId)->where('is_repair_end', 1)->count();
        $satisfactionRate    = $totalReviews > 0 ? round(($averageRating / 5) * 100) : 0;

        return Inertia::render('MobileServiceManagement/Frontend/Index', [
            'testimonials' => $testimonials,
            'reviewStats'  => [
                'totalReviews'        => $totalReviews,
                'averageRating'       => $averageRating,
                'fiveStarReviews'     => $fiveStarReviews,
                'totalServices'       => $totalServices,
                'totalDeviceRepaired' => $totalDeviceRepaired,
                'satisfactionRate'    => $satisfactionRate
            ]
        ]);
    }

    public function services(Request $request)
    {
        $userId = $this->getUserIdFromRequest($request);
        $query  = ProductServiceItem::where('type', 'service')->where('created_by', $userId);
        
          // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $services = $query->paginate(6);

        return Inertia::render('MobileServiceManagement/Frontend/Services', [
            'services' => $services,
            'search'   => $request->search
        ]);
    }

    public function booking(Request $request)
    {
        $userId         = $this->getUserIdFromRequest($request);
        $mobileServices = ProductServiceItem::where('created_by', $userId)
            ->where('is_active', 1)->where('type','service')
            ->select('id', 'name')
            ->get();
        $deviceBrands = MobileDeviceBrand::where('created_by', $userId)
            ->select('id', 'name')
            ->get();

        return Inertia::render('MobileServiceManagement/Frontend/Booking', [
            'mobileServices' => $mobileServices,
            'deviceBrands'   => $deviceBrands
        ]);
    }

    public function submitBooking(StoreMobileServiceBookingRequest $request, $userSlug)
    {
        $userId    = $this->getUserIdFromRequest($request);
        $user      = User::where('slug', $userSlug)->firstOrFail();
        $user_id   = $user->id;
        $validated = $request->validated();

        $defaultTrackingStatus = MobileTrackingStatus::where('created_by',  $userId)
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
        $booking->tracking_status_log = $defaultTrackingStatus ? $defaultTrackingStatus->id : 'received';
        $booking->is_approve          = 0;
        $booking->created_by          = $userId;

              // Handle multiple file attachments
        $attachmentUrls = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $singleRequest = new Request();
                $singleRequest->files->set('single_file', $file);

                $upload = upload_file($singleRequest, 'single_file', $fileNameToStore, '');
                if ($upload['flag'] == 1) {
                    $attachmentUrls[] = ltrim($upload['url'], '/');
                }
            }
        }

        if (!empty($attachmentUrls)) {
            $booking->attachment = json_encode($attachmentUrls);
        }

        $booking->save();
            // Send email notification
        if (company_setting('New Mobile Service Request', $user_id) == 'on') {
            $trackingUrl = route('mobile-service.frontend.index', $userSlug);
            $link        = '<a href="' . $trackingUrl . '">Click here to track your request</a>';

            $emailData = [
                'tracking_id'  => $booking->service_id,
                'tracking_url' => $link,
                'company_name' => company_setting('company_name', $user_id) ?: config('app.name'),
                'app_name'     => config('app.name'),
                'app_url'      => config('app.url'),
            ];
            try {
                EmailTemplate::sendEmailTemplate('New Mobile Service Request', [$booking->email], $emailData,$user_id);
            } catch (\Exception $e) {
                 return back()->with('error', $e->getMessage());
            }
        }
        return back()->with('success', __('Booking submitted successfully!'));
    }

    public function about(Request $request)
    {
        $userId            = $this->getUserIdFromRequest($request);
        $aboutTitleSection = MobileServiceSetup::where('created_by', $userId)
            ->where('key', 'about_title_section')
            ->value('value');
        $aboutData = $aboutTitleSection ? json_decode($aboutTitleSection, true) : [];
              // Add user names and avatars to team members if they exist
        if (isset($aboutData['team_members']) && is_array($aboutData['team_members'])) {
            foreach ($aboutData['team_members'] as &$member) {
                if (isset($member['user_id'])) {
                    $user            = User::find($member['user_id']);
                    $member['name']  = $user ? $user->name : '-';
                    $member['image'] = $user && $user->avatar ? $user->avatar : null;
                }
            }
        }

        return Inertia::render('MobileServiceManagement/Frontend/About', [
            'aboutTitleSection' => $aboutData
        ]);
    }

    public function contact(Request $request)
    {
        $userId         = $this->getUserIdFromRequest($request);
        $contactSection = MobileServiceSetup::where('created_by', $userId)
            ->where('key', 'contact_section')
            ->value('value');
        $contactData = $contactSection ? json_decode($contactSection, true) : [];

              // Parse service hours if available
        if (isset($contactData['service_hours'])) {
            $contactData['service_hours'] = json_decode($contactData['service_hours'], true);
        }

              // Get social links
        $socialLinks = MobileServiceSetup::where('created_by', $userId)
            ->where('key', 'social_links')
            ->value('value');
        $socialLinksData = $socialLinks ? json_decode($socialLinks, true) : [];

        return Inertia::render('MobileServiceManagement/Frontend/Contact', [
            'contactInfo' => $contactData,
            'socialLinks' => $socialLinksData
        ]);
    }

    public function faq(Request $request)
    {
        $userId = $this->getUserIdFromRequest($request);
        $faqs   = MobileServiceSetup::where('created_by', $userId)
            ->where('key', 'faqs')
            ->value('value');

        $faqsData = $faqs ? json_decode($faqs, true) : [];

        return Inertia::render('MobileServiceManagement/Frontend/Faq', [
            'faqs' => $faqsData
        ]);
    }

    public function serviceDetails(Request $request, $userSlug, $service)
    {
        $userId  = $this->getUserIdFromRequest($request);
        $service = ProductServiceItem::where('id', $service)
            ->where('created_by', $userId)
            ->firstOrFail();
        $sameCategory = ProductServiceItem::where('category_id', $service->category_id)
            ->where('id', '!=', $service)
            ->limit(6)
            ->get();
        $otherServices = ProductServiceItem::where('category_id', '!=', $service->category_id)
            ->limit(6 - $sameCategory->count())
            ->get();

        $relatedServices = $sameCategory->concat($otherServices)->map(function ($relatedService) {
            return [
                'id'          => $relatedService->id,
                'name'        => $relatedService->name,
                'description' => $relatedService->description,
                'sale_price'  => $relatedService->sale_price,
                'image'       => $relatedService->image
            ];
        });

        $reviews = MobileServiceReview::where('mobile_service_id', $service->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('MobileServiceManagement/Frontend/ServiceDetails', [
            'title'           => "{$service->name} | {$userSlug} | Service Details",
            'service'         => $service,
            'relatedServices' => $relatedServices,
            'reviews'         => $reviews,
        ]);
    }


    public function customPage(Request $request,$userSlug, $slug)
    {
        $userId = $this->getUserIdFromRequest($request);
        $customPage = MobileServiceCustomPage::where('slug', $slug)->where('created_by', $userId)
            ->firstOrFail();

        return Inertia::render('MobileServiceManagement/Frontend/CustomPage', [
            'title'      => "{$customPage->title} | {$userSlug} | Mobile Service Management",
            'customPage' => $customPage
        ]);
    }

    public function storeReview(StoreMobileServiceReviewRequest $request, $userSlug, $service)
    {
        $userId    = $this->getUserIdFromRequest($request);
        $validated = $request->validated();

        $review                    = new MobileServiceReview();
        $review->name              = $validated['name'];
        $review->email             = $validated['email'];
        $review->mobile_service_id = $service;
        $review->rating            = $validated['rating'];
        $review->review            = $validated['review'];
        $review->created_by        = $userId;
        $review->save();

        return back()->with('success', 'Review submitted successfully!');
    }

    public function trackService(Request $request, $userSlug)
    {
        $user      = User::where('slug', $userSlug)->firstOrFail();
        $createdBy = $user->id;

        $validated = $request->validate([
            'serviceId' => 'required|string',
            'email'     => 'required|email'
        ]);

        $serviceRequest = MobileServiceRequest::where('service_id', $validated['serviceId'])
            ->where('email', $validated['email'])
            ->where('created_by', $createdBy)
            ->first();
        if ($serviceRequest) {
            if ($serviceRequest->email !== $request['email']) {
                return redirect()->back()->with('error', __('The email you entered does not match our records for this booking.'));
            }
            if ($serviceRequest->is_approve === 0) {
                return redirect()->back()->with('error', __('Your booking is still pending approval. Please wait for confirmation.'));
            }
            if ($serviceRequest->is_approve === 1) {
                return redirect()->route('mobile-service.frontend.order-details', [
                    'userSlug'  => $userSlug,
                    'serviceId' => $validated['serviceId'],
                    'email'     => $validated['email']
                ]);
            } else {
                return redirect()->back()->with('error', __('Unfortunately, your booking request was rejected.'));
            }
        } else {
            return redirect()->back()->with('error', __('Service request not found with the provided service ID and email.'));
        }
    }

    public function showTracking(Request $request, $userSlug)
    {
        if ($request->has(['serviceId', 'email'])) {
            $user      = User::where('slug', $userSlug)->firstOrFail();
            $createdBy = $user->id;

            $serviceRequest = MobileServiceRequest::where('service_id', $request->serviceId)
                ->where('email', $request->email)
                ->where('created_by', $createdBy)
                ->with(['mobileService', 'deviceBrand', 'trackingStatus'])
                ->first();

            if ($serviceRequest) {
                  // Get service original cost
                $serviceCost = $serviceRequest->mobileService ? $serviceRequest->mobileService->sale_price : 0;
                
                  // Get total payment amount from mobile_service_payments table
                $serviceID     = MobileServicePayment::where('mobile_service_id', $serviceRequest->id);
                $totalPayments = $serviceID->value('payment_amount');
                $paymentStatus = $serviceID->value('payment_status');

                return Inertia::render('MobileServiceManagement/Frontend/OrderDetails', [
                    'title'               => "Track Service #{$serviceRequest->service_id} | {$userSlug}",
                    'serviceRequest'      => $serviceRequest,
                    'serviceCost'         => $serviceCost,
                    'totalPayments'       => $totalPayments,
                    'paymentStatus'       => $paymentStatus,
                    'allTrackingStatuses' => MobileTrackingStatus::where('created_by', $createdBy)->orderBy('order')->get()
                ]);
            }
        }

        return redirect()->route('mobile-service.frontend.index', $userSlug);
    }

    public function contactStore(StoreMobileServiceContactRequest $request)
    {
        $userId    = $this->getUserIdFromRequest($request);
        $validated = $request->validated();

        $contact             = new MobileServiceContact();
        $contact->first_name = $validated['first_name'];
        $contact->last_name  = $validated['last_name'];
        $contact->email      = $validated['email'];
        $contact->phone      = $validated['phone'];
        $contact->subject    = $validated['repair_type'];
        $contact->message    = $validated['message'];
        $contact->created_by = $userId;
        $contact->save();

        return back()->with('success', __('Contact message sent successfully!'));
    }
}
