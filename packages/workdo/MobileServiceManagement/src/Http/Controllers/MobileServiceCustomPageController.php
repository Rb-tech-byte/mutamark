<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceCustomPage;
use Workdo\MobileServiceManagement\Http\Requests\StoreMobileServiceCustomPageRequest;
use Workdo\MobileServiceManagement\Http\Requests\UpdateMobileServiceCustomPageRequest;
use Illuminate\Support\Str;

class MobileServiceCustomPageController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-mobile-service-custom-pages')){
            $customPages = MobileServiceCustomPage::where('created_by', creatorId())->get();

            return Inertia::render('MobileServiceManagement/SystemSetup/CustomPages/Index', [
                'customPages' => $customPages
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreMobileServiceCustomPageRequest $request)
    {
        if (Auth::user()->can('create-mobile-service-custom-pages')) {
            $validated = $request->validated();

            $customPage                     = new MobileServiceCustomPage();
            $customPage->name               = $validated['name'];
            $customPage->title              = $validated['title'];
            $customPage->slug               = $validated['slug'];
            $customPage->contents           = $validated['contents'];
            $customPage->enable_page_footer = $validated['enable_page_footer'] ?? 'off';
            $customPage->creator_id         = Auth::id();
            $customPage->created_by         = creatorId();
            $customPage->save();

            return redirect()->back()->with('success', __('The custom page has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function edit(MobileServiceCustomPage $customPage)
    {
        if(Auth::user()->can('edit-mobile-service-custom-pages')){
            return response()->json($customPage);
        }
        else{
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    public function update(UpdateMobileServiceCustomPageRequest $request, MobileServiceCustomPage $customPage)
    {
        if (Auth::user()->can('edit-mobile-service-custom-pages')) {
            $validated = $request->validated();

            $customPage->name               = $validated['name'];
            $customPage->title              = $validated['title'];
            $customPage->slug               = $validated['slug'];
            $customPage->contents           = $validated['contents'];
            $customPage->enable_page_footer = $validated['enable_page_footer'] ?? 'off';
            $customPage->save();

            return redirect()->back()->with('success', __('The custom page details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServiceCustomPage $customPage)
    {
        if(Auth::user()->can('delete-mobile-service-custom-pages')){
            $customPage->delete();
            return redirect()->back()->with('success', __('The custom page has been deleted.'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}