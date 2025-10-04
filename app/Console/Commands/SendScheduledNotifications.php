<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Send scheduled notifications that are due';

    public function handle()
    {
        $this->info('Starting to send scheduled notifications...');

        $scheduledNotifications = Notification::scheduled()->get();
        $sentCount = 0;

        foreach ($scheduledNotifications as $notification) {
            try {
                $notification->update(['sent_at' => now()]);
                $sentCount++;
                $this->info("Sent notification: {$notification->title} to user {$notification->user_id}");
            } catch (\Exception $e) {
                $this->error("Failed to send notification {$notification->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sentCount} scheduled notifications.");
    }
}
