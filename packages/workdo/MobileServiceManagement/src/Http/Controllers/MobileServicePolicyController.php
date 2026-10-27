<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Workdo\MobileServiceManagement\Models\MobileServicePolicy;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServicePolicyRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileServicePolicyRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Events\CreateMobileServicePolicy;
use Workdo\MobileServiceManagement\Events\UpdateMobileServicePolicy;
use Workdo\MobileServiceManagement\Events\DestroyMobileServicePolicy;


class MobileServicePolicyController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-mobile-service-policies')) {
            $mobileservicepolicies = MobileServicePolicy::query()

                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-service-policies')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-service-policies')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('policy_name'), function ($q) {
                    $q->where(function ($query) {
                        $query->where('policy_name', 'like', '%' . request('policy_name') . '%');
                    });
                })

                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('MobileServiceManagement/MobileServicePolicies/Index', [
                'mobileservicepolicies' => $mobileservicepolicies,

            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreMobileServicePolicyRequest $request)
    {
        if (Auth::user()->can('create-mobile-service-policies')) {
            $validated = $request->validated();

            $mobileservicepolicy                  = new MobileServicePolicy();
            $mobileservicepolicy->policy_name     = $validated['policy_name'];
            $mobileservicepolicy->description     = $validated['description'];
            $mobileservicepolicy->response_time   = $validated['response_time'];
            $mobileservicepolicy->resolution_time = $validated['resolution_time'];
            $mobileservicepolicy->created_by      = creatorId();
            $mobileservicepolicy->creator_id      = Auth::id();
            $mobileservicepolicy->save();
            CreateMobileServicePolicy::dispatch($request, $mobileservicepolicy);

            return redirect()->route('mobile-service-management.mobile-service-policies.index')->with('success', __('The service policy has been created successfully.'));
        } else {
            return redirect()->route('mobile-service-management.mobile-service-policies.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateMobileServicePolicyRequest $request, MobileServicePolicy $mobileservicepolicy)
    {
        if (Auth::user()->can('edit-mobile-service-policies')) {
            $validated = $request->validated();

            $mobileservicepolicy->policy_name     = $validated['policy_name'];
            $mobileservicepolicy->description     = $validated['description'];
            $mobileservicepolicy->response_time   = $validated['response_time'];
            $mobileservicepolicy->resolution_time = $validated['resolution_time'];

            $mobileservicepolicy->save();
            UpdateMobileServicePolicy::dispatch($request, $mobileservicepolicy);

            return back()->with('success', __('The service policy details are updated successfully.'));
        } else {
            return redirect()->route('mobile-service-management.mobile-service-policies.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServicePolicy $mobileservicepolicy)
    {
        if (Auth::user()->can('delete-mobile-service-policies')) {
            DestroyMobileServicePolicy::dispatch($mobileservicepolicy);
            $mobileservicepolicy->delete();

            return back()->with('success', __('The service policy has been deleted.'));
        } else {
            return redirect()->route('mobile-service-management.mobile-service-policies.index')->with('error', __('Permission denied'));
        }
    }
}
