<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    /**
     * Get all notifications
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving notifications: ' . $e->getMessage());
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return $this->sendNotFound('Notification not found');
            }

            $notification->markAsRead();

            return $this->sendResponse(null, 'Notification marked as read');
        } catch (\Exception $e) {
            return $this->sendServerError('Error marking notification as read: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            $user->unreadNotifications->markAsRead();

            return $this->sendResponse(null, 'All notifications marked as read');
        } catch (\Exception $e) {
            return $this->sendServerError('Error marking notifications as read: ' . $e->getMessage());
        }
    }
}
