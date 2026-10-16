<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = AppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->get('filter') === 'unread') {
            $query->unread();
        } elseif ($request->get('filter') === 'read') {
            $query->read();
        }

        $perPage = min((int)$request->get('per_page', 20), 50);
        $notifications = $query->paginate($perPage);

        $unreadCount = AppNotification::where('user_id', $user->id)->unread()->count();
        $unreadBadge = $unreadCount >= 10 ? '9+' : (string)$unreadCount;

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'unread_count' => $unreadCount,
                'unread_badge' => $unreadBadge,
            ],
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = AppNotification::where('user_id', $user->id)->findOrFail($id);

        if (!$notification->isRead()) {
            $notification->markAsRead();
        }

        $unreadCount = AppNotification::where('user_id', $user->id)->unread()->count();
        $unreadBadge = $unreadCount >= 10 ? '9+' : (string)$unreadCount;

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
            'data' => [
                'notification' => $notification,
                'unread_count' => $unreadCount,
                'unread_badge' => $unreadBadge,
            ],
        ]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        AppNotification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
            'data' => [
                'unread_count' => 0,
                'unread_badge' => '0',
            ],
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $notification = AppNotification::where('user_id', $user->id)->findOrFail($id);
        $notification->delete();

        $unreadCount = AppNotification::where('user_id', $user->id)->unread()->count();
        $unreadBadge = $unreadCount >= 10 ? '9+' : (string)$unreadCount;

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully',
            'data' => [
                'unread_count' => $unreadCount,
                'unread_badge' => $unreadBadge,
            ],
        ]);
    }
}
