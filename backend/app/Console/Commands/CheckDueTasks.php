<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskDueSoonNotification;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for tasks that are due soon and send email notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = (int) env('TASK_DUE_NOTIFICATION_DAYS', 1); // Default: 1 day ahead
        $checkOverdue = filter_var(env('TASK_CHECK_OVERDUE', 'true'), FILTER_VALIDATE_BOOLEAN); // Check for overdue tasks
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($daysAhead);
        $overdueStartDate = $today->copy()->subDays(30); // Check overdue tasks from last 30 days

        // Find tasks that are due between today and the end date (inclusive)
        // This includes tasks due today and up to X days ahead
        $dueTasks = Task::where('status', '!=', 'completed')
            ->whereNotNull('due_date');

        if ($checkOverdue) {
            // Include overdue tasks (past due date) and upcoming tasks
            $dueTasks = $dueTasks->where(function($query) use ($today, $endDate, $overdueStartDate) {
                $query->whereBetween('due_date', [
                        $today->format('Y-m-d'),
                        $endDate->format('Y-m-d')
                    ])
                    ->orWhere(function($q) use ($today, $overdueStartDate) {
                        // Overdue tasks (past due date but not too old)
                        $q->where('due_date', '<', $today->format('Y-m-d'))
                          ->where('due_date', '>=', $overdueStartDate->format('Y-m-d'));
                    });
            });
        } else {
            // Only check upcoming tasks
            $dueTasks = $dueTasks->whereBetween('due_date', [
                $today->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        }

        $dueTasks = $dueTasks->get();

        if ($dueTasks->isEmpty()) {
            $this->info('No tasks due soon.');
            $this->line("Checked for tasks due between {$today->format('Y-m-d')} and {$endDate->format('Y-m-d')}");
            if ($checkOverdue) {
                $this->line("Also checked for overdue tasks from the last 30 days");
            }
            return 0;
        }

        $this->info("Found {$dueTasks->count()} task(s) due soon or overdue.");
        $this->line("Checking tasks due between {$today->format('Y-m-d')} and {$endDate->format('Y-m-d')}");
        if ($checkOverdue) {
            $this->line("Also checking overdue tasks from the last 30 days");
        }

        foreach ($dueTasks as $task) {
            $taskDueDate = Carbon::parse($task->due_date);
            $daysUntilDue = $today->diffInDays($taskDueDate, false);
            
            // Load user relationship to determine recipient
            $task->load('user');
            $recipient = $task->user_id && $task->user 
                ? $task->user->email 
                : (env('ADMIN_EMAIL') ?: 'Admin (from env)');
            
            // Dispatch job to send email notification
            SendTaskDueSoonNotification::dispatch($task, $daysUntilDue);
            
            $statusText = $daysUntilDue < 0 
                ? "OVERDUE (" . abs($daysUntilDue) . " days ago)" 
                : ($daysUntilDue == 0 ? 'TODAY' : "in {$daysUntilDue} day(s)");
            $this->line("Queued notification for task: {$task->title} (Due: {$task->due_date} - {$statusText}) → {$recipient}");
        }

        $this->info('All notifications have been queued.');
        return 0;
    }
}
