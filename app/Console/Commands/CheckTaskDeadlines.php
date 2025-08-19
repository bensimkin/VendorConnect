<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Task; // Assuming you have a Task model
use Carbon\Carbon;

class CheckTaskDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-deadlines';
    protected $description = 'Check tasks and send alerts when approaching deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       // Get current time
       $now = Carbon::now();
       // Find tasks that have a deadline within 48 hours and 28 hours
       $tasks = Task::where('end_date', '>', $now)
           ->where(function($query) use ($now) {
               $query->whereBetween('end_date', [$now->addHours(28)->subHour(), $now->addHours(28)])
                     ->orWhereBetween('end_date', [$now->addHours(20), $now->addHours(48)]);
           })
           ->get();

       foreach ($tasks as $task) {
           // Send email alert to both parties
           $this->sendAlertEmail($task);
       }

       return 0;
   }
   private function sendAlertEmail($task)
   {
       // Assuming your Task model has a relation with User
       $users = $task->taskUsers;

       foreach ($users as $user) {
           Mail::to($user->email)->send(new \App\Mail\TaskDeadlineAlert($task, $user->first_name));
       }
   }
}
