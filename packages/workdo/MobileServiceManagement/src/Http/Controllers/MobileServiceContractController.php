<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Workdo\MobileServiceManagement\Models\MobileServiceContract;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServiceContractRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileServiceContractRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Events\CreateMobileServiceContract;
use Workdo\MobileServiceManagement\Events\UpdateMobileServiceContract;
use Workdo\MobileServiceManagement\Events\DestroyMobileServiceContract;


class MobileServiceContractController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-mobile-service-contracts')) {
            $mobileservicecontracts = MobileServiceContract::query()

                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-service-contracts')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-service-contracts')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('customer_name'), function ($q) {
                    $q->where(function ($query) {
                        $query->where('customer_name', 'like', '%' . request('customer_name') . '%');
                        $query->orWhere('contract_details', 'like', '%' . request('customer_name') . '%');
                    });
                })
                ->when(request('contract_type') !== null && request('contract_type') !== '', fn($q) => $q->where('contract_type', request('contract_type') === '1' ? 1 : 0))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('MobileServiceManagement/MobileServiceContracts/Index', [
                'mobileservicecontracts' => $mobileservicecontracts,

            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreMobileServiceContractRequest $request)
    {
        if (Auth::user()->can('create-mobile-service-contracts')) {
            $validated = $request->validated();

            $mobileservicecontract                   = new MobileServiceContract();
            $mobileservicecontract->customer_name    = $validated['customer_name'];
            $mobileservicecontract->contract_type    = $validated['contract_type'];
            $mobileservicecontract->start_date       = $validated['start_date'];
            $mobileservicecontract->end_date         = $validated['end_date'];
            $mobileservicecontract->contract_details = $validated['contract_details'];

            $mobileservicecontract->creator_id = Auth::id();
            $mobileservicecontract->created_by = creatorId();
            $mobileservicecontract->save();
            CreateMobileServiceContract::dispatch($request, $mobileservicecontract);

            return redirect()->route('mobile-service-management.mobile-service-contracts.index')->with('success', __('The contract has been created successfully.'));
        } else {
            return redirect()->route('mobile-service-management.mobile-service-contracts.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateMobileServiceContractRequest $request, MobileServiceContract $mobileservicecontract)
    {
        if (Auth::user()->can('edit-mobile-service-contracts')) {
            $validated = $request->validated();

            $mobileservicecontract->customer_name    = $validated['customer_name'];
            $mobileservicecontract->contract_type    = $validated['contract_type'];
            $mobileservicecontract->start_date       = $validated['start_date'];
            $mobileservicecontract->end_date         = $validated['end_date'];
            $mobileservicecontract->contract_details = $validated['contract_details'];

            $mobileservicecontract->save();
            UpdateMobileServiceContract::dispatch($request, $mobileservicecontract);

            return back()->with('success', __('The contract details are updated successfully.'));
        } else {
            return redirect()->route('mobile-service-management.mobile-service-contracts.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServiceContract $mobileservicecontract)
    {
        if (Auth::user()->can('delete-mobile-service-contracts')) {
            DestroyMobileServiceContract::dispatch($mobileservicecontract);
            $mobileservicecontract->delete();

            return back()->with('success', __('The contract has been deleted.'));
        } else {
            return redirect()->route('mobile-service-management.mobile-service-contracts.index')->with('error', __('Permission denied'));
        }
    }
}
