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
        $adminEmail = env('ADMIN_EMAIL');
        
        if (!$adminEmail) {
            \Log::warning('ADMIN_EMAIL not configured. Skipping task due notification.');
            return;
        }

        try {
            Mail::to($adminEmail)->send(
                new TaskDueSoonNotification($this->task, $this->daysUntilDue)
            );
            \Log::info("Task due notification sent successfully for task: {$this->task->title} to {$adminEmail}");
        } catch (\Exception $e) {
            \Log::error("Failed to send task due notification: " . $e->getMessage(), [
                'task_id' => $this->task->id,
                'admin_email' => $adminEmail,
                'error' => $e->getTraceAsString()
            ]);
            // Re-throw to mark job as failed
            throw $e;
        }
    }
}
