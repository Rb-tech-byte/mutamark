<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\AutomationNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationCenterController extends Controller
{
    public function index(Request $request)
    {
        $creatorId = creatorId();
        $notifications = AppNotification::where('created_by', $creatorId)
            ->latest()
            ->limit(100)
            ->get();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'notifications' => $notifications->take(20)->values(),
                'unread_count' => $notifications->whereNull('read_at')->count(),
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $notifications->whereNull('read_at')->count(),
        ]);
    }

    public function runScan(Request $request)
    {
        $creatorId = creatorId();
        $count = AutomationNotificationService::scanWorkspace($creatorId, (bool) $request->boolean('send_email', false));

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json(['created' => $count]);
        }

        return back()->with('success', __('Automation scan complete. :count new alert(s) generated.', ['count' => $count]));
    }

    public function markRead(AppNotification $notification)
    {
        if ((int) $notification->created_by === (int) creatorId() && is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return back();
    }

    public function markAllRead()
    {
        AppNotification::where('created_by', creatorId())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('All notifications marked as read.'));
    }
}

