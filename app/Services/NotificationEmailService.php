<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationEmailService
{
    public function __construct()
    {
        // No need for SendGrid service - using Laravel Mail
    }

    /**
     * Send email notifications for unread notifications older than 5 minutes
     */
    public function sendUnreadNotificationEmails(): int
    {
        $sentCount = 0;
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);

        // Get users who have unread notifications older than 5 minutes that haven't been emailed yet
        $usersWithUnreadNotifications = User::whereHas('notifications', function ($query) use ($fiveMinutesAgo) {
            $query->whereNull('read_at')
                  ->where('created_at', '<=', $fiveMinutesAgo)
                  ->whereNull('sent_at');
        })->with(['notifications' => function ($query) use ($fiveMinutesAgo) {
            $query->whereNull('read_at')
                  ->where('created_at', '<=', $fiveMinutesAgo)
                  ->whereNull('sent_at')
                  ->orderBy('created_at', 'desc');
        }])->get();

        foreach ($usersWithUnreadNotifications as $user) {
            try {
                $unreadNotifications = $user->notifications->where('read_at', null)
                    ->where('created_at', '<=', $fiveMinutesAgo);

                if ($unreadNotifications->count() > 0) {
                    $this->sendNotificationSummaryEmail($user, $unreadNotifications);
                    $sentCount++;
                    
                    // Mark these notifications as having been emailed
                    $unreadNotifications->each(function ($notification) {
                        $notification->update(['sent_at' => now()]);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Failed to send notification email to user ' . $user->id, [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Sent {$sentCount} notification summary emails");
        return $sentCount;
    }

    /**
     * Send a summary email of unread notifications to a user
     */
    protected function sendNotificationSummaryEmail(User $user, $notifications): void
    {
        $notificationCount = $notifications->count();
        $subject = "You have {$notificationCount} unread notification" . ($notificationCount > 1 ? 's' : '');
        
        $htmlContent = $this->generateHtmlEmail($user, $notifications, $subject);
        $textContent = $this->generateTextEmail($user, $notifications, $subject);

        Mail::html($htmlContent, function ($message) use ($user, $subject, $textContent) {
            $message->to($user->email, $user->first_name . ' ' . $user->last_name)
                   ->subject($subject)
                   ->text($textContent);
        });
    }

    /**
     * Generate HTML email content
     */
    protected function generateHtmlEmail(User $user, $notifications, string $subject): string
    {
        $notificationCount = $notifications->count();
        $appName = config('app.name', 'VendorConnect');
        $appUrl = config('app.url');
        
        $notificationsHtml = '';
        foreach ($notifications as $notification) {
            $priorityClass = $this->getPriorityClass($notification->priority);
            $typeIcon = $this->getTypeIcon($notification->type);
            $timeAgo = $notification->created_at->diffForHumans();
            
            $notificationsHtml .= "
                <div style='padding: 15px; border-left: 4px solid {$this->getPriorityColor($notification->priority)}; margin-bottom: 10px; background-color: #f8f9fa;'>
                    <div style='display: flex; align-items: center; margin-bottom: 8px;'>
                        <span style='font-size: 18px; margin-right: 10px;'>{$typeIcon}</span>
                        <h3 style='margin: 0; color: #333; font-size: 16px;'>{$notification->title}</h3>
                        <span style='margin-left: auto; font-size: 12px; color: #666;'>{$timeAgo}</span>
                    </div>
                    <p style='margin: 0; color: #666; font-size: 14px;'>{$notification->message}</p>
                    <div style='margin-top: 8px;'>
                        <span style='background-color: {$this->getPriorityColor($notification->priority)}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; text-transform: uppercase;'>
                            {$notification->priority}
                        </span>
                    </div>
                </div>
            ";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$subject}</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                <h1 style='color: #2c3e50; margin: 0 0 10px 0;'>🔔 {$subject}</h1>
                <p style='margin: 0; color: #666;'>Hi {$user->first_name}, you have {$notificationCount} unread notification" . ($notificationCount > 1 ? 's' : '') . " that require your attention.</p>
            </div>
            
            <div style='margin-bottom: 20px;'>
                {$notificationsHtml}
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$appUrl}/notifications' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;'>
                    View All Notifications
                </a>
            </div>
            
            <div style='border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 12px;'>
                <p>This email was sent because you have unread notifications in {$appName}.</p>
                <p>If you no longer wish to receive these emails, please update your notification preferences.</p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Generate plain text email content
     */
    protected function generateTextEmail(User $user, $notifications, string $subject): string
    {
        $notificationCount = $notifications->count();
        $appName = config('app.name', 'VendorConnect');
        $appUrl = config('app.url');
        
        $text = "Hi {$user->first_name},\n\n";
        $text .= "You have {$notificationCount} unread notification" . ($notificationCount > 1 ? 's' : '') . " that require your attention:\n\n";
        
        foreach ($notifications as $notification) {
            $typeIcon = $this->getTypeIcon($notification->type);
            $timeAgo = $notification->created_at->diffForHumans();
            
            $text .= "{$typeIcon} {$notification->title} ({$notification->priority}) - {$timeAgo}\n";
            $text .= "   {$notification->message}\n\n";
        }
        
        $text .= "View all notifications: {$appUrl}/notifications\n\n";
        $text .= "This email was sent because you have unread notifications in {$appName}.\n";
        $text .= "If you no longer wish to receive these emails, please update your notification preferences.\n";
        
        return $text;
    }

    /**
     * Get priority color for HTML
     */
    protected function getPriorityColor(string $priority): string
    {
        return match ($priority) {
            'urgent' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#0d6efd',
            'low' => '#198754',
            default => '#6c757d',
        };
    }

    /**
     * Get priority CSS class
     */
    protected function getPriorityClass(string $priority): string
    {
        return match ($priority) {
            'urgent' => 'urgent',
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low',
            default => 'default',
        };
    }

    /**
     * Get type icon
     */
    protected function getTypeIcon(string $type): string
    {
        return match ($type) {
            'task_assigned' => '📋',
            'task_completed' => '✅',
            'task_due_soon' => '⏰',
            'task_overdue' => '🚨',
            'deliverable_added' => '📎',
            'comment_added' => '💬',
            'project_updated' => '📁',
            'client_updated' => '👤',
            default => '🔔',
        };
    }
}
