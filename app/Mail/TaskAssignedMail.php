<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification_data;

    public function __construct($notification_data)
    {
        $this->notification_data = $notification_data;
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')) // Set the 'from' address
                    ->subject('New Task Assigned')
                    ->view('email.taskAssigned')
                    ->with([
                        'type' => $this->notification_data['type'],
                        'type_id' => $this->notification_data['type_id'],
                        'type_title' => $this->notification_data['type_title'],
                        'access_url' => $this->notification_data['access_url'],
                        'action' => $this->notification_data['action'],
                        'title' => $this->notification_data['title'],
                        'message' => $this->notification_data['message'],
                    ]);
    }
}
