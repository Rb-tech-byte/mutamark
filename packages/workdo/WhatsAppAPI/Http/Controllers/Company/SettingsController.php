<?php
// This file use for handle company setting page

namespace Modules\WhatsAppAPI\Http\Controllers\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Notification;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        $notification_modules = Notification::where('type','WhatsAppAPI')->groupBy('module')->pluck('module');
        $notify = Notification::where('type','WhatsAppAPI')->get();
        $active_module =  ActivatedModule();
        $dependency = explode(',','WhatsAppAPI');
        
        return view('whatsappapi::company.settings.index',compact('notification_modules','notify'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
}
