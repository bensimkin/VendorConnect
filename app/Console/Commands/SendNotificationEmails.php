<?php

namespace App\Console\Commands;

use App\Services\NotificationEmailService;
use Illuminate\Console\Command;

class SendNotificationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-emails {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for unread notifications older than 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification email process...');
        
        if ($this->option('dry-run')) {
            $this->info('DRY RUN MODE - No emails will be sent');
            $this->showUnreadNotifications();
            return;
        }

        $notificationEmailService = new NotificationEmailService();
        
        try {
            $sentCount = $notificationEmailService->sendUnreadNotificationEmails();
            
            if ($sentCount > 0) {
                $this->info("Successfully sent {$sentCount} notification email(s)");
            } else {
                $this->info('No notification emails to send');
            }
        } catch (\Exception $e) {
            $this->error('Error sending notification emails: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Show unread notifications that would trigger emails
     */
    protected function showUnreadNotifications()
    {
        $fiveMinutesAgo = now()->subMinutes(5);
        
        $usersWithUnreadNotifications = \App\Models\User::whereHas('notifications', function ($query) use ($fiveMinutesAgo) {
            $query->whereNull('read_at')
                  ->where('created_at', '<=', $fiveMinutesAgo)
                  ->whereNull('sent_at');
        })->with(['notifications' => function ($query) use ($fiveMinutesAgo) {
            $query->whereNull('read_at')
                  ->where('created_at', '<=', $fiveMinutesAgo)
                  ->whereNull('sent_at')
                  ->orderBy('created_at', 'desc');
        }])->get();

        if ($usersWithUnreadNotifications->isEmpty()) {
            $this->info('No users with unread notifications older than 5 minutes');
            return;
        }

        $this->info('Users who would receive notification emails:');
        $this->newLine();

        foreach ($usersWithUnreadNotifications as $user) {
            $unreadCount = $user->notifications->where('read_at', null)
                ->where('created_at', '<=', $fiveMinutesAgo)->count();
            
            $this->line("👤 {$user->first_name} {$user->last_name} ({$user->email})");
            $this->line("   📧 Would receive email with {$unreadCount} unread notification(s)");
            
            $notifications = $user->notifications->where('read_at', null)
                ->where('created_at', '<=', $fiveMinutesAgo)->take(3);
            
            foreach ($notifications as $notification) {
                $timeAgo = $notification->created_at->diffForHumans();
                $this->line("   • {$notification->title} ({$notification->priority}) - {$timeAgo}");
            }
            
            if ($unreadCount > 3) {
                $this->line("   ... and " . ($unreadCount - 3) . " more");
            }
            
            $this->newLine();
        }
    }
}
