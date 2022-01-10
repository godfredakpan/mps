<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\User;

class NotificationController extends Controller
{
    public function destroy($id)
    {
        $notification = User::find(auth()->id())->notifications()->whereId($id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => __('We are unable to find the notification.')], 422);
        }

        $notification->delete();
        return response()->json(['success' => true, 'message' => __('Notification has been successfully deleted.')]);
    }

    public function destroyAll()
    {
        User::find(auth()->id())->notifications()->delete();
        return response()->json(['success' => true, 'message' => __('Notifications have been successfully deleted.')]);
    }

    public function index(Request $request)
    {
        $user = User::find(auth()->id());
        if ($request->unread == 'yes') {
            return $user->unreadNotifications()->latest()->paginate(20);
        }
        $data = $user->notifications()->latest()->paginate(20);
        return response()->json($data);
    }

    public function unread($id)
    {
        $notification = User::find(auth()->id())->readNotifications()->whereId($id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => __('We are unable to find the notification.')], 422);
        }

        $notification->markAsUnread();
        return response()->json(['success' => true, 'message' => __('Notification has been successfully marked as unread.')]);
    }

    public function update($id)
    {
        $notification = User::find(auth()->id())->unreadNotifications()->whereId($id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => __('We are unable to find the notification.')], 422);
        }

        $notification->markAsRead();
        return response()->json(['success' => true, 'message' => __('Notification has been successfully marked as read.')]);
    }

    public function updateAll(Request $request)
    {
        User::find(auth()->id())->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true, 'message' => __('Notifications has been successfully marked as read.')]);
    }
}
