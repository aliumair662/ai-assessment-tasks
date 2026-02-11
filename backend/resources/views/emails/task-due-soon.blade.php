<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Due Soon Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #42a5f5;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .task-details {
            background: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border-left: 4px solid #ffa726;
        }
        .task-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .task-info {
            margin: 8px 0;
            color: #666;
        }
        .task-info strong {
            color: #333;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
            margin-right: 8px;
        }
        .badge-pending {
            background: #fff3e0;
            color: #e65100;
        }
        .badge-in-progress {
            background: #e3f2fd;
            color: #1565c0;
        }
        .badge-high {
            background: #ffebee;
            color: #c62828;
        }
        .badge-medium {
            background: #fff9c4;
            color: #f57f17;
        }
        .badge-low {
            background: #f5f5f5;
            color: #616161;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 12px;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ Task Due Soon Notification</h1>
    </div>
    
    <div class="content">
        <div class="alert">
            <strong>Reminder:</strong> You have a task that is due 
            @if($daysUntilDue == 0)
                <strong>today</strong>!
            @elseif($daysUntilDue == 1)
                <strong>tomorrow</strong>!
            @else
                in <strong>{{ $daysUntilDue }} days</strong>!
            @endif
        </div>

        <div class="task-details">
            <div class="task-title">{{ $task->title }}</div>
            
            @if($task->description)
            <div class="task-info">
                <strong>Description:</strong><br>
                {{ $task->description }}
            </div>
            @endif

            <div class="task-info">
                <strong>Status:</strong>
                <span class="badge badge-{{ str_replace('-', '-', $task->status) }}">
                    {{ $task->status }}
                </span>
            </div>

            <div class="task-info">
                <strong>Priority:</strong>
                <span class="badge badge-{{ $task->priority }}">
                    {{ $task->priority }}
                </span>
            </div>

            <div class="task-info">
                <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('F j, Y') }}
            </div>

            @if($daysUntilDue == 0)
            <div class="task-info" style="color: #c62828; font-weight: bold;">
                ⚠️ This task is due TODAY!
            </div>
            @endif
        </div>

        <p style="margin-top: 20px;">
            Please review and complete this task before the due date.
        </p>
    </div>

    <div class="footer">
        <p>This is an automated notification from Task Management System.</p>
        <p>You are receiving this because you are the admin.</p>
    </div>
</body>
</html>

