<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskAuditResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskAuditController extends Controller
{
    /**
     * Get audit trail for a specific task.
     */
    public function index(Request $request, string $taskId): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $task = Task::findOrFail($taskId);
        
        // Check if user can view this task
        if ($user->role !== 'admin' && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $audits = $task->audits()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => TaskAuditResource::collection($audits),
        ]);
    }

    /**
     * Get a specific audit entry.
     */
    public function show(string $taskId, string $auditId): JsonResponse
    {
        $task = Task::findOrFail($taskId);
        
        $audit = $task->audits()->findOrFail($auditId);

        return response()->json([
            'data' => new TaskAuditResource($audit),
        ]);
    }
}
