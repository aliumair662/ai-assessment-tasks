<?php

namespace App\Jobs;

use App\Mail\TaskDueSoonNotification;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendTaskDueSoonNotification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $daysUntilDue;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task, int $daysUntilDue)
    {
        $this->task = $task;
        $this->daysUntilDue = $daysUntilDue;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load the user relationship if it exists
        $this->task->load('user');
        
        // Determine recipient: assigned user if exists, otherwise admin
        $recipientEmail = null;
        $recipientName = null;
        
        if ($this->task->user_id && $this->task->user) {
            // Task is assigned to a user, send to that user
            $recipientEmail = $this->task->user->email;
            $recipientName = $this->task->user->name;
        } else {
            // No user assigned, send to admin
            $adminEmail = env('ADMIN_EMAIL');
            
            if (!$adminEmail) {
                \Log::warning('ADMIN_EMAIL not configured and task has no assigned user. Skipping task due notification.', [
                    'task_id' => $this->task->id,
                    'task_title' => $this->task->title
                ]);
                return;
            }
            
            $recipientEmail = $adminEmail;
            $recipientName = 'Admin';
        }

        try {
            Mail::to($recipientEmail)->send(
                new TaskDueSoonNotification($this->task, $this->daysUntilDue)
            );
            \Log::info("Task due notification sent successfully for task: {$this->task->title} to {$recipientEmail} ({$recipientName})");
        } catch (\Exception $e) {
            \Log::error("Failed to send task due notification: " . $e->getMessage(), [
                'task_id' => $this->task->id,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'error' => $e->getTraceAsString()
            ]);
            // Re-throw to mark job as failed
            throw $e;
        }
    }
}
