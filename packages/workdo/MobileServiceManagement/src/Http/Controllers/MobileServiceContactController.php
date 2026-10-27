<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Models\MobileServiceContact;
use Workdo\MobileServiceManagement\Events\DestroyMobileServiceContact;

class MobileServiceContactController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-mobile-service-contacts')){
            $mobileservicecontacts = MobileServiceContact::query()
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-mobile-service-contacts')) {
                        $q->where('created_by', creatorId())->orWhereNull('created_by');
                    } elseif(Auth::user()->can('manage-own-mobile-service-contacts')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('name'), function($q) {
                    $searchTerm = request('name');
                    $q->where(function($query) use ($searchTerm) {
                        $query->where('first_name', 'like', '%' . $searchTerm . '%')
                              ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                              ->orWhere('email', 'like', '%' . $searchTerm . '%')
                              ->orWhere('subject', 'like', '%' . $searchTerm . '%');
                    });
                })
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('MobileServiceManagement/Contacts/Index', [
                'mobileservicecontacts' => $mobileservicecontacts,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServiceContact $mobileservicecontact)
    {
        if(Auth::user()->can('delete-mobile-service-contacts')){
            DestroyMobileServiceContact::dispatch($mobileservicecontact);
            $mobileservicecontact->delete();
            return back()->with('success', __('The contact has been deleted.'));
        }
        else{
            return redirect()->route('mobile-service-management.mobile-service-contacts.index')->with('error', __('Permission denied'));
        }
    }
}