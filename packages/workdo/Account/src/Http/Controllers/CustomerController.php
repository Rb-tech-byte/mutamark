<?php

namespace Workdo\Account\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Account\Models\Customer;
use Workdo\Account\Http\Requests\StoreCustomerRequest;
use Workdo\Account\Http\Requests\UpdateCustomerRequest;
use Workdo\Account\Events\CreateCustomer;
use Workdo\Account\Events\UpdateCustomer;
use Workdo\Account\Events\DestroyCustomer;

class CustomerController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-customers')){
            $customers = Customer::query()
                ->with('user:id,name,avatar,is_disable')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-customers')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-customers')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('company_name'), fn($q) => $q->where('company_name', 'like', '%' . request('company_name') . '%'))
                ->when(request('customer_code'), fn($q) => $q->where('customer_code', 'like', '%' . request('customer_code') . '%'))
                ->when(request('tax_number'), fn($q) => $q->where('tax_number', 'like', '%' . request('tax_number') . '%'))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $users = User::where('type', 'client')
                ->where('created_by', creatorId())
                ->whereNotIn('id', Customer::pluck('user_id')->filter())
                ->select('id', 'name', 'email', 'mobile_no')
                ->get();

            return Inertia::render('Account/Customers/Index', [
                'customers' => $customers,
                'users' => $users,
            ]);
        }
        return back()->with('error', __('Permission denied'));
    }

    public function store(StoreCustomerRequest $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
        \App\Services\CompanyAccess::requirePermission('create-customers');
        if(Auth::user()->can('create-customers')){
            $validated = $request->validated();

            if (isset($validated['credit_limit'])) \App\Services\CompanyAccess::requirePermission('manage-account');

            $customer = new Customer();
            $customer->user_id = $validated['user_id'] ?? null;
            $customer->company_name = $validated['company_name'];
            $customer->credit_limit = $validated['credit_limit'] ?? null;
            $customer->contact_person_name = $validated['contact_person_name'];
            $customer->contact_person_email = $validated['contact_person_email'] ?? null;
            $customer->contact_person_mobile = $validated['contact_person_mobile'] ?? null;
            $customer->tax_number = $validated['tax_number'] ?? null;
            $customer->payment_terms = $validated['payment_terms'] ?? null;
            $customer->billing_address = $validated['billing_address'];
            $customer->shipping_address = $validated['same_as_billing'] ? $validated['billing_address'] : $validated['shipping_address'];
            $customer->same_as_billing = $validated['same_as_billing'] ?? false;
            $customer->notes = $validated['notes'] ?? null;
            $customer->creator_id = Auth::id();
            $customer->created_by = creatorId();
            $customer->save();

            CreateCustomer::dispatch($request, $customer);

            return $request->expectsJson() ? response()->json($customer->fresh(), 201) : redirect()->route('account.customers.index')->with('success', __('The customer has been created successfully.'));
        }
        return redirect()->route('account.customers.index')->with('error', __('Permission denied'));
    
        }, 3);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $customer) {
        $customer = Customer::where('created_by', creatorId())->lockForUpdate()->findOrFail($customer->id);

        \App\Services\CompanyAccess::requirePermission('edit-customers');
        \App\Services\CompanyAccess::check($customer);
        if(Auth::user()->can('edit-customers')){
            $validated = $request->validated();
            $newLimit = array_key_exists('credit_limit', $validated) ? $validated['credit_limit'] : $customer->credit_limit;
            if (($newLimit === null) !== ($customer->credit_limit === null) || (float) $newLimit !== (float) $customer->credit_limit) {
                \App\Services\CompanyAccess::requirePermission('manage-account');
                \App\Models\FinancialAuditLog::record(['event_type'=>'CUSTOMER_CREDIT_LIMIT_CHANGED','module'=>'Account','reference_type'=>'Customer','reference_id'=>$customer->id,'reference_number'=>$customer->customer_code,'action'=>'Changed customer credit limit','old_values'=>['credit_limit'=>$customer->credit_limit],'new_values'=>['credit_limit'=>$newLimit]]);
            }

            $customer->company_name = $validated['company_name'];
            $customer->credit_limit = $newLimit;
            $customer->contact_person_name = $validated['contact_person_name'];
            $customer->contact_person_email = $validated['contact_person_email'] ?? null;
            $customer->contact_person_mobile = $validated['contact_person_mobile'] ?? null;
            $customer->tax_number = $validated['tax_number'] ?? null;
            $customer->payment_terms = $validated['payment_terms'] ?? null;
            $customer->billing_address = $validated['billing_address'];
            $customer->shipping_address = $validated['same_as_billing'] ? $validated['billing_address'] : ($validated['shipping_address'] ?? null);
            $customer->same_as_billing = $validated['same_as_billing'] ?? false;
            $customer->notes = $validated['notes'] ?? null;
            $customer->save();

            UpdateCustomer::dispatch($request, $customer);

            return $request->expectsJson() ? response()->json($customer->fresh()) : back()->with('success', __('The customer details are updated successfully.'));
        }
        return back()->with('error', __('Permission denied'));
    
        }, 3);
    }

    public function destroy(Customer $customer)
    {
        \App\Services\CompanyAccess::requirePermission('delete-customers');
        \App\Services\CompanyAccess::check($customer);
        if(Auth::user()->can('delete-customers')){
            DestroyCustomer::dispatch($customer);
            $customer->delete();
            return back()->with('success', __('The customer has been deleted.'));
        }
        return back()->with('error', __('Permission denied'));
    }
}
