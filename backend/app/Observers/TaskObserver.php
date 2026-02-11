<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskAudit;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        TaskAudit::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => null,
            'metadata' => [
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $changes = $task->getChanges();
        $original = $task->getOriginal();

        // Remove timestamps from changes
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $metadata = [];
        $statusChanged = false;
        $isCompleted = false;
        $nonStatusChanges = [];

        // Separate status changes from other changes
        foreach ($changes as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            
            if ($field === 'status') {
                $statusChanged = true;
                $isCompleted = ($newValue === 'completed');
            } else {
                $nonStatusChanges[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }

            $metadata[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        // Always log status changes separately with special action
        if ($statusChanged) {
            $oldStatus = $original['status'] ?? null;
            $newStatus = $changes['status'];
            
            TaskAudit::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => $isCompleted ? 'completed' : 'status_changed',
                'field_changed' => 'status',
                'old_value' => $this->formatValue($oldStatus),
                'new_value' => $this->formatValue($newStatus),
                'metadata' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ]);
        }

        // Log other field changes
        if (!empty($nonStatusChanges)) {
            if (count($nonStatusChanges) === 1) {
                // Single field change - log it individually
                $field = array_key_first($nonStatusChanges);
                $change = $nonStatusChanges[$field];
                
                TaskAudit::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'field_changed' => $field,
                    'old_value' => $this->formatValue($change['old']),
                    'new_value' => $this->formatValue($change['new']),
                    'metadata' => [
                        $field => $change,
                    ],
                ]);
            } else {
                // Multiple fields changed - log as a summary
                TaskAudit::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'field_changed' => 'multiple',
                    'old_value' => null,
                    'new_value' => null,
                    'metadata' => $nonStatusChanges,
                ]);
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        TaskAudit::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => null,
            'metadata' => [
                'title' => $task->title,
                'status' => $task->status,
            ],
        ]);
    }

    /**
     * Format value for display in audit trail.
     */
    private function formatValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
