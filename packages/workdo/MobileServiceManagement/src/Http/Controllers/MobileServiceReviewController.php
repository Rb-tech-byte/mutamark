<?php

namespace Workdo\MobileServiceManagement\Http\Controllers;

use Workdo\MobileServiceManagement\Models\MobileServiceReview;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\MobileServiceManagement\Events\DestroyMobileServiceReview;
use Workdo\ProductService\Models\ProductServiceItem;

class MobileServiceReviewController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-mobile-service-reviews')){
            $mobileservicereviews = MobileServiceReview::query()
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-mobile-service-reviews')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-mobile-service-reviews')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->with('mobileService:id,name')
                ->when(request('name'), function($q) {
                    $q->where(function($query) {
                        $query->where('name', 'like', '%' . request('name') . '%')
                              ->orWhere('email', 'like', '%' . request('name') . '%');
                    });
                })
                ->when(request('rating'), fn($q) => $q->where('rating', request('rating')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('MobileServiceManagement/Reviews/Index', [
                'mobileservicereviews' => $mobileservicereviews,
                'mobileservices'       => ProductServiceItem::where('created_by', creatorId())->select('id', 'name')->get(),
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(MobileServiceReview $mobileservicereview)
    {
        if(Auth::user()->can('delete-mobile-service-reviews')){
            DestroyMobileServiceReview::dispatch($mobileservicereview);
            $mobileservicereview->delete();
            return back()->with('success', __('The review has been deleted.'));
        }
        else{
            return redirect()->route('mobile-service-management.mobile-service-reviews.index')->with('error', __('Permission denied'));
        }
    }
}