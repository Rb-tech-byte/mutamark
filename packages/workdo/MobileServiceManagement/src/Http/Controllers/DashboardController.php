<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use Workdo\MobileServiceManagement\Models\MobileServiceContract;
use Workdo\MobileServiceManagement\Models\MobileServicePayment;
use Workdo\MobileServiceManagement\Models\MobileServiceAssignTechnician;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

            // Check if user is a staff member
        if ($user->hasRole('staff') && $user->can('manage-mobile-service-dashboard')) {
            return $this->staffDashboard();
        }

            // Company dashboard for managers
        if($user->can('manage-mobile-service-dashboard')){
            return $this->companyDashboard();
        }

        return back()->with('error', __('Permission denied.'));
    }

    private function companyDashboard()
    {
        $creatorId = creatorId();

        return Inertia::render('MobileServiceManagement/Dashboard/CompanyDashboard', [
            'stats'          => $this->getStats($creatorId),
            'recentServices' => $this->getRecentServices($creatorId),
            'recentPayments' => $this->getRecentPayments($creatorId),
            'chartData'      => $this->getChartData($creatorId),
            'contractStatus' => $this->getContractStatus($creatorId),
            'bookingUrl'     => $this->getBookingUrl($creatorId),
            'message'        => __('Mobile Service Dashboard - Manage your services efficiently.')
        ]);
    }

    private function staffDashboard()
    {
        $user   = Auth::user();
        $userId = $user->id;

            // Get service IDs assigned to this technician
        $assignedServiceIds = MobileServiceAssignTechnician::where('technician_id', $userId)
            ->pluck('mobile_service_id');
            // Stats for staff
        $myAssignedRequests  = MobileServiceAssignTechnician::where('technician_id', $userId)->count();
        $myCompletedRequests = MobileServiceRequest::whereIn('mobile_service_id', $assignedServiceIds)
            ->where('is_repair_end', 1)->count();
        $myPendingRequests = MobileServiceRequest::whereIn('mobile_service_id', $assignedServiceIds)
            ->where('is_repair_end', 0)->count();
        $myTotalEarnings = MobileServicePayment::whereHas('mobileServiceRequest', function($query) use ($assignedServiceIds) {
            $query->whereIn('mobile_service_id', $assignedServiceIds);
        })->sum('payment_amount') ?? 0;

            // Recent assigned requests
        $myRecentRequests = MobileServiceRequest::whereIn('id', $assignedServiceIds)
            ->with(['deviceBrand:id,name', 'mobileService:id,name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id'            => $request->id,
                    'service_id'    => $request->service_id,
                    'customer_name' => $request->customer_name,
                    'mobile_model'  => $request->mobile_model,
                    'device_brand'  => $request->deviceBrand?->name ?? '',
                    'service_name'  => $request->mobileService?->name ?? '',
                    'priority'      => $request->priority,
                    'status'        => $request->is_repair_end ? 'Completed' : 'In Progress',
                    'created_at'    => $request->created_at->format('Y-m-d')
                ];
            })->toArray();



        return Inertia::render('MobileServiceManagement/Dashboard/StaffDashboard', [
            'stats' => [
                'assignedRequests'  => $myAssignedRequests,
                'completedRequests' => $myCompletedRequests,
                'pendingRequests'   => $myPendingRequests,
                'totalEarnings'     => $myTotalEarnings
            ],
            'recentRequests' => $myRecentRequests,

            'bookingUrl' => $this->getBookingUrl(creatorId()),
            'staffInfo'  => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => 'Service Technician'
            ],
            'message' => __('Staff Dashboard - Track your assigned service requests.')
        ]);
    }

    private function getStats($userId): array
    {
        $totalRequests     = MobileServiceRequest::where('created_by', $userId)->count();
        $pendingRequests   = MobileServiceRequest::where('created_by', $userId)->where('is_approve', 0)->count();
        $completedRequests = MobileServiceRequest::where('created_by', $userId)->where('is_repair_end', 1)->count();
        $totalIncome       = MobileServicePayment::where('created_by', $userId)->where('payment_status', 'cleared')->sum('payment_amount') ?? 0;

        return [
            'totalRequests'     => $totalRequests,
            'pendingRequests'   => $pendingRequests,
            'completedRequests' => $completedRequests,
            'totalIncome'       => $totalIncome
        ];
    }

    private function getRecentServices(int $userId): array
    {
        return MobileServiceRequest::where('created_by', $userId)
            ->where('is_approve', 1)
            ->with(['deviceBrand:id,name', 'mobileService:id,name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id'            => $request->id,
                    'service_id'    => $request->service_id,
                    'customer_name' => $request->customer_name,
                    'mobile_model'  => $request->mobile_model,
                    'device_brand'  => $request->deviceBrand?->name ?? '',
                    'service_name'  => $request->mobileService?->name ?? '',
                    'description'   => substr($request->description, 0, 50) . '...',
                    'priority'      => $request->priority,
                    'is_approve'    => $request->is_approve,
                    'created_at'    => $request->created_at->format('Y-m-d')
                ];
            })->toArray();
    }

    private function getChartData(int $userId): array
    {
        $pendingPayments = MobileServiceRequest::where('created_by', $userId)
            ->where('payment_status', 0)
            ->count();
        
        $paidPayments = MobileServiceRequest::where('created_by', $userId)
            ->where('payment_status', 1)
            ->count();

        return [
            ['status' => 'Pending', 'count' => $pendingPayments],
            ['status' => 'Paid', 'count' => $paidPayments]
        ];
    }

    private function getContractStatus(int $userId): array
    {
        $now    = now();
        $active = MobileServiceContract::where('created_by', $userId)
            ->where('end_date', '>', $now)
            ->count();
        
        $expiring = MobileServiceContract::where('created_by', $userId)
            ->whereBetween('end_date', [$now, $now->copy()->addDays(30)])
            ->count();
        
        $expired = MobileServiceContract::where('created_by', $userId)
            ->where('end_date', '<', $now)
            ->count();

        return [
            'active'   => $active,
            'expiring' => $expiring,
            'expired'  => $expired
        ];
    }

    private function getRecentPayments(int $userId): array
    {
        return MobileServicePayment::where('created_by', $userId)
            ->with('mobileServiceRequest:id,service_id,customer_name,mobile_model')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id'             => $payment->id,
                    'payment_number' => $payment->payment_number ?? '',
                    'customer_name'  => $payment->mobileServiceRequest?->customer_name ?? '',
                    'device_name'    => $payment->mobileServiceRequest?->mobile_model ?? '',
                    'payment_amount' => $payment->payment_amount ?? 0,
                    'payment_status' => $payment->payment_status ?? 'Pending',
                    'created_at'     => $payment->created_at->format('Y-m-d')
                ];
            })->toArray();
    }

    private function getBookingUrl(int $userId): ?string
    {
        $user = User::select('slug')->find($userId);
        return $user?->slug ? route('mobile-service.frontend.index', ['userSlug' => $user->slug]): null;
    }
}