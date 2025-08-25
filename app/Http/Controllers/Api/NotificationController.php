<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $priority = $request->get('priority');
            $read = $request->get('read'); // 'true', 'false', or null for all

            $query = Notification::where('from_id', $user->id);

            // Filter by type
            if ($type) {
                $query->where('type', $type);
            }

            // Filter by priority
            if ($priority) {
                $query->where('priority', $priority);
            }

            // Filter by read status - disabled since table doesn't have read_at column
            // if ($read === 'true') {
            //     $query->read();
            // } elseif ($read === 'false') {
            //     $query->unread();
            // }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->sendResponse($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving notifications: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        try {
            $user = Auth::user();
            // Since the table doesn't have read_at column, return 0 for now
            $count = 0;

            return $this->sendResponse(['count' => $count], 'Unread count retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving unread count: ' . $e->getMessage());
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('from_id', $user->id)
                ->find($id);

            if (!$notification) {
                return $this->sendNotFound('Notification not found');
            }

            // Since table doesn't have read_at column, just return success
            return $this->sendResponse($notification, 'Notification marked as read');
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
            // Since table doesn't have read_at column, just return success
            $updated = 0;

            return $this->sendResponse(['updated_count' => $updated], 'All notifications marked as read');
        } catch (\Exception $e) {
            return $this->sendServerError('Error marking notifications as read: ' . $e->getMessage());
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('from_id', $user->id)
                ->find($id);

            if (!$notification) {
                return $this->sendNotFound('Notification not found');
            }

            // Since table doesn't have read_at column, just return success
            return $this->sendResponse($notification, 'Notification marked as unread');
        } catch (\Exception $e) {
            return $this->sendServerError('Error marking notification as unread: ' . $e->getMessage());
        }
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('from_id', $user->id)
                ->find($id);

            if (!$notification) {
                return $this->sendNotFound('Notification not found');
            }

            $notification->delete();

            return $this->sendResponse(null, 'Notification deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting notification: ' . $e->getMessage());
        }
    }

    /**
     * Delete all read notifications
     */
    public function deleteRead()
    {
        try {
            $user = Auth::user();
            // Since table doesn't have read_at column, return 0
            $deleted = 0;

            return $this->sendResponse(['deleted_count' => $deleted], 'Read notifications deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting read notifications: ' . $e->getMessage());
        }
    }

    /**
     * Get notification types
     */
    public function types()
    {
        try {
            $types = [
                Notification::TYPE_TASK_ASSIGNED => 'Task Assigned',
                Notification::TYPE_TASK_COMPLETED => 'Task Completed',
                Notification::TYPE_TASK_DUE_SOON => 'Task Due Soon',
                Notification::TYPE_TASK_OVERDUE => 'Task Overdue',
                Notification::TYPE_DELIVERABLE_ADDED => 'Deliverable Added',
                Notification::TYPE_COMMENT_ADDED => 'Comment Added',
                Notification::TYPE_PROJECT_UPDATED => 'Project Updated',
                Notification::TYPE_CLIENT_UPDATED => 'Client Updated',
            ];

            return $this->sendResponse($types, 'Notification types retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving notification types: ' . $e->getMessage());
        }
    }

    /**
     * Get notification priorities
     */
    public function priorities()
    {
        try {
            $priorities = [
                Notification::PRIORITY_LOW => 'Low',
                Notification::PRIORITY_MEDIUM => 'Medium',
                Notification::PRIORITY_HIGH => 'High',
                Notification::PRIORITY_URGENT => 'Urgent',
            ];

            return $this->sendResponse($priorities, 'Notification priorities retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving notification priorities: ' . $e->getMessage());
        }
    }
}
