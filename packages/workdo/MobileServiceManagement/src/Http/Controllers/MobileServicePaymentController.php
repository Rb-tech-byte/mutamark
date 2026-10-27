<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Request;
use Workdo\MobileServiceManagement\Models\MobileServicePayment;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServicePaymentRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileServicePaymentRequest;
use Workdo\ProductService\Models\ProductServiceTax;
use Workdo\MobileServiceManagement\Events\UpdateMobileServicePaymentStatus;
use Workdo\MobileServiceManagement\Events\CreateMobileServicePayment;
use Workdo\MobileServiceManagement\Events\UpdateMobileServicePayment;
use Workdo\MobileServiceManagement\Events\DestroyMobileServicePayment;

class MobileServicePaymentController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-mobile-service-payments')) {
            $payments = MobileServicePayment::query()
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-service-payments')) {
                        $q->where('created_by', creatorId())->orWhereNull('created_by');
                    } elseif (Auth::user()->can('manage-own-mobile-service-payments')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->with(['mobileServiceRequest:id,service_id,customer_name,email,mobile_no'])
                ->when(request('search'), function ($q) {
                    $q->whereHas('mobileServiceRequest', function ($query) {
                        $query->where('customer_name', 'like', '%' . request('search') . '%')
                            ->orWhere('service_id', 'like', '%' . request('search') . '%');
                    });
                })
                ->when(request('status'), function ($q) {
                    $q->where('payment_status', request('status'));
                })
                ->when(request('payment_date'), function ($q) {
                    $dateRange = request('payment_date');
                    $dates     = explode(' - ', $dateRange);
                    if (count($dates) === 2) {
                        $q->whereBetween('payment_date', [$dates[0], $dates[1]]);
                    } else {
                        $q->whereDate('payment_date', $dateRange);
                    }
                })
                ->latest()
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $serviceRequests = MobileServiceRequest::where('created_by', creatorId())
                ->where('is_approve', 1)
                ->where('is_repair_end', 1)
                ->where('payment_status', '!=', 1)
                ->with(['mobileService:id,name,sale_price,tax_ids'])
                ->select('id', 'service_id', 'customer_name', 'email', 'mobile_no', 'mobile_service_id')
                ->get();

            // Load taxes for each service explicitly
            $serviceRequests->each(function ($request) {
                if ($request->mobileService && $request->mobileService->tax_ids) {
                    $taxIds = $request->mobileService->tax_ids;
                    if (!empty($taxIds) && is_array($taxIds)) {
                        $request->mobileService->taxes = ProductServiceTax::whereIn('id', $taxIds)
                            ->get(['id', 'tax_name', 'rate']);
                    }
                }
            });


            return Inertia::render('MobileServiceManagement/Payments/Index', [
                'payments'        => $payments,
                'serviceRequests' => $serviceRequests,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }



    public function store(StoreMobileServicePaymentRequest $request)
    {
        if (Auth::user()->can('create-mobile-service-payments')) {
            $validated = $request->validated();

            $extraChargesTotal = 0;
            if (!empty($validated['extra_charges'])) {
                $extraChargesTotal = collect($validated['extra_charges'])->sum('price');
            }

            $totalAmount = $validated['price'] + ($validated['repair_charge'] ?? 0) + $extraChargesTotal + ($validated['tax_amount'] ?? 0);

            $payment                    = new MobileServicePayment();
            $payment->mobile_service_id = $validated['mobile_service_id'];
            $payment->payment_date      = $validated['payment_date'];
            $payment->service_price     = $validated['price'];
            $payment->tax_amount        = $validated['tax_amount'] ?? 0;
            $payment->repair_charge     = $validated['repair_charge'] ?? 0;
            $payment->payment_amount    = $totalAmount;
            $payment->extra_charges     = $validated['extra_charges'] ?? [];
            $payment->payment_status    = 'pending';
            $payment->notes             = $validated['description'] ?? null;
            $payment->creator_id        = Auth::id();
            $payment->created_by        = creatorId();
            $payment->save();

            CreateMobileServicePayment::dispatch($request, $payment);

            return redirect()->route('mobile-service-management.payments.index')->with('success', __('The payment has been created successfully.'));
        } else {
            return redirect()->route('mobile-service-management.payments.index')->with('error', __('Permission denied'));
        }
    }

    public function show(MobileServicePayment $payment)
    {
        if (Auth::user()->can('view-mobile-service-payments')) {
            if (!$this->canAccesssServicPayment($payment)) {
                return redirect()->route('mobile-service-management.payments.index')->with('error', __('Access denied'));
            }
            return Inertia::render('MobileServiceManagement/Payments/View', [
                'payment' => $payment->load(['mobileServiceRequest']),
            ]);
        } else {
            return redirect()->route('mobile-service-management.payments.index')->with('error', __('Permission denied'));
        }
    }


    public function update(UpdateMobileServicePaymentRequest $request, MobileServicePayment $payment)
    {
        if (Auth::user()->can('edit-mobile-service-payments')) {
            $validated = $request->validated();

            $extraChargesTotal = 0;
            if (!empty($validated['extra_charges'])) {
                $extraChargesTotal = collect($validated['extra_charges'])->sum('price');
            }

            $totalAmount = $validated['price'] + ($validated['repair_charge'] ?? 0) + $extraChargesTotal + ($validated['tax_amount'] ?? 0);

            $payment->mobile_service_id = $validated['mobile_service_id'];
            $payment->payment_date      = $validated['payment_date'];
            $payment->service_price     = $validated['price'];
            $payment->tax_amount        = $validated['tax_amount'] ?? 0;
            $payment->repair_charge     = $validated['repair_charge'] ?? 0;
            $payment->payment_amount    = $totalAmount;
            $payment->extra_charges     = $validated['extra_charges'] ?? [];
            $payment->payment_status    = 'pending';
            $payment->notes             = $validated['description'] ?? null;

            $payment->save();

            UpdateMobileServicePayment::dispatch($request, $payment);

            return back()->with('success', __('The payment details are updated successfully.'));
        } else {
            return redirect()->route('mobile-service-management.payments.index')->with('error', __('Permission denied'));
        }
    }

    public function updateStatus(Request $request, MobileServicePayment $payment)
    {
        if (Auth::user()->can('cleared-mobile-service-payments') && $payment->created_by == creatorId()) {
            try {
                if ($request->status === 'cleared') {
                    try {
                        UpdateMobileServicePaymentStatus::dispatch($request, $payment);
                    } catch (\Throwable $th) {
                        return back()->with('error', $th->getMessage());
                    }
                    MobileServiceRequest::find($payment->mobile_service_id)->update([
                        'payment_status' => 1
                    ]);
                } elseif ($request->status === 'cancelled') {
                    MobileServiceRequest::find($payment->mobile_service_id)->update([
                        'payment_status' => 2
                    ]);
                }
                $payment->update(['payment_status' => $request->status]);

                return back()->with('success', __('The payment status has been updated successfully.'));
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function print(MobileServicePayment $payment)
    {
        if (Auth::user()->can('print-mobile-service-payments')) {
            $payment->load(['mobileServiceRequest']);

            return Inertia::render('MobileServiceManagement/Payments/Print', [
                'payment' => $payment
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServicePayment $payment)
    {
        if (Auth::user()->can('delete-mobile-service-payments')) {
            DestroyMobileServicePayment::dispatch($payment);

            $payment->delete();
            return back()->with('success', __('The payment has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
    private function canAccesssServicPayment(MobileServicePayment $payment)
    {
        if (Auth::user()->can('manage-any-mobile-service-payments')) {
            return $payment->created_by == creatorId();
        } elseif (Auth::user()->can('manage-own-mobile-service-payments')) {
            return $payment->creator_id == Auth::id() || $payment->customer_id == Auth::id();
        } else {
            return false;
        }
    }
}
