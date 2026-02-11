# Task Management System

A full-stack task management application built with Laravel (backend) and React (frontend).

## Features

- ✅ Create, read, update, and delete tasks
- ✅ Task properties: title, description, status, priority, due date
- ✅ Kanban board view with drag-and-drop functionality
- ✅ Multi-user support with task assignment
- ✅ Role-based access (Admin sees all tasks, members see assigned tasks)
- ✅ User authentication (session-based)
- ✅ Complete audit trail tracking all task changes
- ✅ Email notifications for tasks due soon (using Laravel queues)
- ✅ Visual badges for assigned users and overdue tasks
- ✅ Filter tasks by priority
- ✅ Sort tasks by various fields (created date, due date, priority, title)
- ✅ Task detail view with embedded audit trail
- ✅ Clean, modern UI with responsive design

## Tech Stack

- **Backend**: Laravel 12 (PHP)
- **Frontend**: React 19
- **Database**: SQLite (default)

## Setup Instructions

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd backend
   ```

2. Install dependencies (if not already done):
   ```bash
   composer install
   ```

3. Create `.env` file (if not exists):
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Seed the database with initial users:
   ```bash
   php artisan db:seed --class=UserSeeder
   ```
   
   This creates:
   - Admin: admin@example.com / password
   - Team Member 1: john@example.com / password
   - Team Member 2: jane@example.com / password

7. Configure email and admin settings in `.env`:
   
   **For Development (Log emails to file):**
   ```env
   # Email Configuration - Development
   MAIL_MAILER=log
   MAIL_FROM_ADDRESS=noreply@example.com
   MAIL_FROM_NAME="Task Manager"

   # Admin Email for Notifications
   ADMIN_EMAIL=admin@example.com

   # Days ahead to send notification (default: 1)
   TASK_DUE_NOTIFICATION_DAYS=1
   TASK_CHECK_OVERDUE=true

   # Queue Configuration (use 'database' for queue jobs)
   QUEUE_CONNECTION=database
   ```
   
   **For Production (SMTP):**
   ```env
   # Email Configuration - Production
   MAIL_MAILER=smtp
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-email@example.com
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@example.com
   MAIL_FROM_NAME="Task Manager"

   # Admin Email for Notifications
   ADMIN_EMAIL=admin@example.com

   # Days ahead to send notification (default: 1)
   TASK_DUE_NOTIFICATION_DAYS=1
   TASK_CHECK_OVERDUE=true

   # Queue Configuration
   QUEUE_CONNECTION=database
   ```
   
   **Note:** When using `MAIL_MAILER=log`, emails will be written to `storage/logs/laravel.log` instead of being sent. This is perfect for development and testing.

8. Start the Laravel development server:
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`

9. Start the queue worker (for email notifications):
   ```bash
   php artisan queue:work
   ```

10. (Optional) For production, set up a cron job to run the scheduler:
   ```bash
   * * * * * cd /path-to-your-project/backend && php artisan schedule:run >> /dev/null 2>&1
   ```

### Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies (if not already done):
   ```bash
   npm install
   ```

3. Start the React development server:
   ```bash
   npm start
   ```
   The frontend will be available at `http://localhost:3000`

## API Endpoints

All API endpoints are prefixed with `/api`:

### Authentication
- `POST /api/login` - Login user (email, password)
- `GET /api/me` - Get current authenticated user
- `POST /api/logout` - Logout user
- `GET /api/users` - Get all users (admin only)

### Tasks
- `GET /api/tasks` - Get all tasks (filtered by user, supports query parameters: `status`, `priority`, `sort_by`, `sort_order`)
- `GET /api/tasks/{id}` - Get a specific task
- `POST /api/tasks` - Create a new task (requires authentication)
- `PUT /api/tasks/{id}` - Update a task (requires authentication)
- `DELETE /api/tasks/{id}` - Delete a task (requires authentication)

### Audit Trail
- `GET /api/tasks/{taskId}/audits` - Get audit trail for a task

## API Request/Response Format

### Create Task Request
```json
{
  "title": "Task title",
  "description": "Task description",
  "status": "pending|in-progress|completed",
  "priority": "low|medium|high",
  "due_date": "2024-12-31"
}
```

### Task Response
```json
{
  "data": {
    "id": 1,
    "title": "Task title",
    "description": "Task description",
    "status": "pending",
    "priority": "medium",
    "due_date": "2024-12-31",
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-01 12:00:00"
  }
}
```

## Architecture Decisions

1. **RESTful API Design**: Used Laravel API resources for consistent JSON responses
2. **Separation of Concerns**: Clear separation between backend API and frontend UI
3. **State Management**: Used React hooks (useState, useEffect) for state management - simple and effective for this scope
4. **Component Structure**: Modular component design (TaskList, TaskItem, TaskForm) for maintainability
5. **API Service Layer**: Centralized API calls in a service module for reusability

## Authentication

The system uses session-based authentication:

- **Login**: Use the login form with email and password
- **Session**: Sessions are maintained via cookies (CORS configured)
- **Authorization**: 
  - Admin users can see and manage all tasks
  - Team members can only see and manage their assigned tasks
  - Admin can assign tasks to any user when creating/editing

## Audit Trail

All task changes are automatically logged:
- Task creation
- Task updates (field-by-field tracking)
- Status changes
- Task completion
- Task deletion

Each audit entry includes:
- Action type
- Field changed (if applicable)
- Old and new values
- User who performed the action
- Timestamp

## Trade-offs

1. **State Management**: Chose React hooks over Redux/Context API for simplicity - sufficient for this application scope
2. **Database**: Using SQLite for simplicity - easy to switch to MySQL/PostgreSQL if needed
3. **Authentication**: Session-based instead of JWT - simpler for web apps, less suitable for mobile
4. **Error Handling**: Basic error handling - could be enhanced with toast notifications
5. **Testing**: Manual testing focus - automated tests can be added for production

## Email Notifications

The system includes automated email notifications for tasks that are due soon:

- **Scheduled Command**: Runs daily at 9:00 AM UTC to check for due tasks
- **Queue System**: Uses Laravel queues to send emails asynchronously
- **Configuration**: Set `ADMIN_EMAIL` in `.env` to receive notifications
- **Notification Timing**: Configure `TASK_DUE_NOTIFICATION_DAYS` (default: 1 day ahead)

To manually trigger the check:
```bash
php artisan tasks:check-due
```

## Notes

- Make sure both backend (port 8000) and frontend (port 3000) servers are running
- CORS is configured to allow requests from the React frontend with credentials
- The API base URL is configured in `frontend/src/services/api.js` - update if your backend runs on a different port
- For email notifications to work, ensure the queue worker is running: `php artisan queue:work`
- The scheduled task checker runs automatically via Laravel's scheduler (requires cron in production)
- Login is required to access the application - use the seeded user credentials
- Tasks are automatically assigned to the current user unless you're an admin (admins can assign to any user)

## Deliverables

This project includes:
1. **PROMPT_LOG.md** - Chronological log of all AI prompts used during development
2. **SYSTEM_OVERVIEW.md** - System architecture, decisions, and trade-offs documentation
3. **Code Repository** - Complete source code for backend and frontend

