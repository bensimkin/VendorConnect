# VendorConnect API Documentation

## Overview
This document provides comprehensive documentation for all VendorConnect API endpoints, including request/response formats, database operations, and frontend page associations.

**Base URL**: `https://vc.themastermind.com.au/api/v1`

---

## 🔐 Authentication APIs

### POST `/auth/login`
**Purpose**: Authenticate user and generate access token

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "user@example.com",
      "photo": null,
      "status": 1,
      "dark_mode": 0,
      "country_code": "AU",
      "last_login_at": "2025-08-28T06:30:00.000000Z",
      "roles": [
        {
          "id": 1,
          "name": "admin"
        }
      ]
    },
    "token": "1|abc123...",
    "permissions": ["view_tasks", "create_tasks", "edit_tasks"]
  }
}
```

**Database Operations**:
- **Read**: `users` table (id, first_name, last_name, email, photo, status, dark_mode, country_code, last_login_at)
- **Read**: `model_has_roles` table (role assignments)
- **Read**: `roles` table (role names)
- **Update**: `users.last_login_at` (updates login timestamp)
- **Create**: `personal_access_tokens` table (creates new token)

**Frontend Pages**:
- `/login` - Login page
- All authenticated pages (redirects to login if not authenticated)

---

### POST `/auth/logout`
**Purpose**: Invalidate current access token

**Request Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**Database Operations**:
- **Delete**: `personal_access_tokens` table (removes current token)

**Frontend Pages**:
- All authenticated pages (redirects to login after logout)

---

### POST `/auth/forgot-password`
**Purpose**: Send password reset email

**Request Body**:
```json
{
  "email": "user@example.com"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Password reset email sent"
}
```

**Database Operations**:
- **Read**: `users` table (verifies email exists)
- **Create**: `password_resets` table (creates reset token)

**Frontend Pages**:
- `/forgot-password` - Forgot password page

---

### POST `/auth/reset-password`
**Purpose**: Reset password using token

**Request Body**:
```json
{
  "email": "user@example.com",
  "token": "reset_token_here",
  "password": "new_password123",
  "password_confirmation": "new_password123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

**Database Operations**:
- **Read**: `password_resets` table (validates token)
- **Update**: `users.password` (updates hashed password)
- **Delete**: `password_resets` table (removes used token)

**Frontend Pages**:
- `/reset-password` - Reset password page

---

### GET `/user`
**Purpose**: Get current authenticated user information

**Request Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "user@example.com",
  "photo": null,
  "status": 1,
  "dark_mode": 0,
  "country_code": "AU",
  "last_login_at": "2025-08-28T06:30:00.000000Z",
  "roles": [
    {
      "id": 1,
      "name": "admin"
    }
  ]
}
```

**Database Operations**:
- **Read**: `users` table (current user data)
- **Read**: `model_has_roles` table (user role assignments)
- **Read**: `roles` table (role information)

**Frontend Pages**:
- All authenticated pages (loads user data for navigation/profile)

---

## 👤 Profile Management APIs

### GET `/profile`
**Purpose**: Get current user's profile information

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "user@example.com",
    "photo": null,
    "status": 1,
    "dark_mode": 0,
    "country_code": "AU",
    "last_login_at": "2025-08-28T06:30:00.000000Z",
    "roles": [
      {
        "id": 1,
        "name": "admin"
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `users` table (profile data)
- **Read**: `model_has_roles` table (role assignments)
- **Read**: `roles` table (role names)

**Frontend Pages**:
- `/settings` - User settings page
- Navigation components (user menu)

---

### GET `/profile/{id}`
**Purpose**: Get specific user's profile information

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "photo": null,
    "status": 1,
    "dark_mode": 0,
    "country_code": "AU",
    "last_login_at": "2025-08-28T05:30:00.000000Z",
    "roles": [
      {
        "id": 2,
        "name": "tasker"
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `users` table (user data by ID)
- **Read**: `model_has_roles` table (role assignments)
- **Read**: `roles` table (role names)

**Frontend Pages**:
- `/users/{id}` - User detail page
- `/users/{id}/edit` - User edit page

---

### PUT `/profile/{id}`
**Purpose**: Update user profile information

**Request Body**:
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "country_code": "AU",
  "dark_mode": 1
}
```

**Response**:
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "country_code": "AU",
    "dark_mode": 1
  }
}
```

**Database Operations**:
- **Update**: `users` table (first_name, last_name, email, country_code, dark_mode)

**Frontend Pages**:
- `/settings` - User settings page
- `/users/{id}/edit` - User edit page

---

### PUT `/profile/{id}/photo`
**Purpose**: Update user profile photo

**Request Body**:
```
multipart/form-data with photo file
```

**Response**:
```json
{
  "success": true,
  "message": "Photo updated successfully",
  "data": {
    "photo": "storage/photos/user_1_photo.jpg"
  }
}
```

**Database Operations**:
- **Update**: `users.photo` (stores photo path)

**Frontend Pages**:
- `/settings` - User settings page
- Navigation components (user avatar)

---

## 📊 Dashboard APIs

### GET `/dashboard`
**Purpose**: Get main dashboard data and statistics

**Response**:
```json
{
  "success": true,
  "data": {
    "total_tasks": 150,
    "completed_tasks": 120,
    "pending_tasks": 30,
    "total_projects": 25,
    "active_projects": 20,
    "total_clients": 45,
    "recent_tasks": [
      {
        "id": 1,
        "title": "Website Redesign",
        "status": {
          "id": 1,
          "name": "In Progress"
        },
        "priority": {
          "id": 2,
          "name": "High"
        },
        "created_at": "2025-08-28T06:00:00.000000Z"
      }
    ],
    "recent_projects": [
      {
        "id": 1,
        "title": "E-commerce Platform",
        "status": {
          "id": 1,
          "name": "Active"
        },
        "created_at": "2025-08-28T06:00:00.000000Z"
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (counts and recent tasks)
- **Read**: `projects` table (counts and recent projects)
- **Read**: `clients` table (total count)
- **Read**: `statuses` table (task/project status info)
- **Read**: `priorities` table (task priority info)

**Frontend Pages**:
- `/dashboard` - Main dashboard page

---

### GET `/dashboard/tasker`
**Purpose**: Get tasker-specific dashboard data

**Response**:
```json
{
  "success": true,
  "data": {
    "assigned_tasks": 25,
    "completed_tasks": 20,
    "pending_tasks": 5,
    "overdue_tasks": 2,
    "my_tasks": [
      {
        "id": 1,
        "title": "Logo Design",
        "status": {
          "id": 1,
          "name": "In Progress"
        },
        "priority": {
          "id": 2,
          "name": "High"
        },
        "end_date": "2025-08-30",
        "project": {
          "id": 1,
          "title": "Brand Identity"
        }
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (user's assigned tasks)
- **Read**: `task_user` table (task assignments)
- **Read**: `projects` table (project info for tasks)
- **Read**: `statuses` table (task status)
- **Read**: `priorities` table (task priority)

**Frontend Pages**:
- `/dashboard/tasker` - Tasker dashboard page

---

### GET `/dashboard/requester`
**Purpose**: Get requester-specific dashboard data

**Response**:
```json
{
  "success": true,
  "data": {
    "created_tasks": 50,
    "completed_tasks": 35,
    "pending_tasks": 15,
    "my_projects": [
      {
        "id": 1,
        "title": "Website Development",
        "status": {
          "id": 1,
          "name": "Active"
        },
        "task_count": 12,
        "completed_task_count": 8
      }
    ],
    "recent_requests": [
      {
        "id": 1,
        "title": "Social Media Graphics",
        "status": {
          "id": 1,
          "name": "In Progress"
        },
        "assigned_to": [
          {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Smith"
          }
        ]
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (user's created tasks)
- **Read**: `projects` table (user's projects)
- **Read**: `task_user` table (task assignments)
- **Read**: `users` table (assigned users)
- **Read**: `statuses` table (task/project status)

**Frontend Pages**:
- `/dashboard/requester` - Requester dashboard page

---

## 🔔 Notification APIs

### GET `/notifications`
**Purpose**: Get user's notifications

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)

**Response**:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "type": "task_assigned",
        "title": "New Task Assigned",
        "message": "You have been assigned to 'Logo Design' task",
        "data": {
          "task_id": 1,
          "task_title": "Logo Design"
        },
        "read_at": null,
        "created_at": "2025-08-28T06:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 45
  }
}
```

**Database Operations**:
- **Read**: `notifications` table (user's notifications)

**Frontend Pages**:
- `/dashboard` - Notification bell in navigation
- Notification components throughout the app

---

### GET `/notifications/unread-count`
**Purpose**: Get count of unread notifications

**Response**:
```json
{
  "success": true,
  "data": {
    "count": 5
  }
}
```

**Database Operations**:
- **Read**: `notifications` table (count where read_at is null)

**Frontend Pages**:
- Navigation components (notification badge)
- All pages with notification indicators

---

### POST `/notifications/{id}/read`
**Purpose**: Mark notification as read

**Response**:
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

**Database Operations**:
- **Update**: `notifications.read_at` (sets timestamp)

**Frontend Pages**:
- Notification components
- Notification list pages

---

### POST `/notifications/mark-all-read`
**Purpose**: Mark all notifications as read

**Response**:
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

**Database Operations**:
- **Update**: `notifications.read_at` (sets timestamp for all user's unread notifications)

**Frontend Pages**:
- Notification list pages
- Notification management components

---

### DELETE `/notifications/{id}`
**Purpose**: Delete specific notification

**Response**:
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

**Database Operations**:
- **Delete**: `notifications` table (removes notification)

**Frontend Pages**:
- Notification list pages
- Notification management components

---

### DELETE `/notifications/read`
**Purpose**: Delete all read notifications

**Response**:
```json
{
  "success": true,
  "message": "Read notifications deleted successfully"
}
```

**Database Operations**:
- **Delete**: `notifications` table (removes all read notifications for user)

**Frontend Pages**:
- Notification management pages

---

### GET `/notifications/types`
**Purpose**: Get available notification types

**Response**:
```json
{
  "success": true,
  "data": [
    "task_assigned",
    "task_completed",
    "project_created",
    "deadline_approaching",
    "comment_added"
  ]
}
```

**Database Operations**:
- **Read**: `notifications` table (distinct types)

**Frontend Pages**:
- Notification settings pages
- Notification management components

---

### GET `/notifications/priorities`
**Purpose**: Get notification priority levels

**Response**:
```json
{
  "success": true,
  "data": [
    "low",
    "medium",
    "high",
    "urgent"
  ]
}
```

**Database Operations**:
- **Read**: `notifications` table (distinct priorities)

**Frontend Pages**:
- Notification settings pages
- Notification management components

---

## 📋 Task Management APIs

### GET `/tasks`
**Purpose**: Get all tasks with filtering and pagination

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status_id` (optional): Filter by status
- `priority_id` (optional): Filter by priority
- `user_id` (optional): Filter by assigned user
- `client_id` (optional): Filter by client
- `project_id` (optional): Filter by project
- `search` (optional): Search in title and description
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort order (asc/desc, default: desc)

**Response**:
```json
{
  "success": true,
  "message": "Tasks retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Website Redesign",
        "description": "Complete website redesign for client",
        "note": "High priority project",
        "status_id": 1,
        "priority_id": 2,
        "project_id": 1,
        "task_type_id": 1,
        "template_id": 1,
        "start_date": "2025-08-01",
        "end_date": "2025-08-31",
        "close_deadline": 0,
        "deliverable_quantity": 1,
        "standard_brief": "Create modern, responsive website",
        "repetition_type": null,
        "repetition_interval": null,
        "repetition_end_date": null,
        "is_repeating": 0,
        "created_at": "2025-08-28T06:00:00.000000Z",
        "updated_at": "2025-08-28T06:00:00.000000Z",
        "status": {
          "id": 1,
          "name": "In Progress",
          "slug": "in-progress",
          "color": "#3B82F6"
        },
        "priority": {
          "id": 2,
          "name": "High",
          "slug": "high",
          "color": "#EF4444",
          "level": 4
        },
        "project": {
          "id": 1,
          "title": "E-commerce Platform",
          "description": "Modern e-commerce solution"
        },
        "task_type": {
          "id": 1,
          "task_type": "Web Development",
          "description": "Website development tasks"
        },
        "users": [
          {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Smith",
            "email": "jane@example.com"
          }
        ],
        "clients": [
          {
            "id": 1,
            "name": "ABC Company",
            "company": "ABC Corp"
          }
        ]
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (main task data)
- **Read**: `statuses` table (status information)
- **Read**: `priorities` table (priority information)
- **Read**: `projects` table (project information)
- **Read**: `task_types` table (task type information)
- **Read**: `task_brief_templates` table (template information)
- **Read**: `task_user` table (user assignments)
- **Read**: `task_client` table (client assignments)
- **Read**: `users` table (assigned users)
- **Read**: `clients` table (assigned clients)

**Frontend Pages**:
- `/tasks` - Task list page
- `/project-management` - Project management page
- `/dashboard` - Dashboard (recent tasks)

---

### POST `/tasks`
**Purpose**: Create a new task

**Request Body**:
```json
{
  "title": "New Task",
  "description": "Task description",
  "note": "Additional notes",
  "status_id": 1,
  "priority_id": 2,
  "project_id": 1,
  "task_type_id": 1,
  "template_id": 1,
  "start_date": "2025-08-01",
  "end_date": "2025-08-31",
  "close_deadline": 0,
  "deliverable_quantity": 1,
  "standard_brief": "Task brief description",
  "repetition_type": "weekly",
  "repetition_interval": 1,
  "repetition_end_date": "2025-12-31",
  "is_repeating": 1,
  "user_ids": [1, 2],
  "client_ids": [1, 2]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Task created successfully",
  "data": {
    "id": 1,
    "title": "New Task",
    "description": "Task description",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `tasks` table (main task data)
- **Create**: `task_user` table (user assignments)
- **Create**: `task_client` table (client assignments)

**Frontend Pages**:
- `/tasks/create` - Create task page
- `/tasks` - Task list page (redirects after creation)

---

### GET `/tasks/{id}`
**Purpose**: Get specific task details

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Website Redesign",
    "description": "Complete website redesign for client",
    "note": "High priority project",
    "status_id": 1,
    "priority_id": 2,
    "project_id": 1,
    "task_type_id": 1,
    "template_id": 1,
    "start_date": "2025-08-01",
    "end_date": "2025-08-31",
    "close_deadline": 0,
    "deliverable_quantity": 1,
    "standard_brief": "Create modern, responsive website",
    "repetition_type": null,
    "repetition_interval": null,
    "repetition_end_date": null,
    "is_repeating": 0,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z",
    "status": {
      "id": 1,
      "name": "In Progress",
      "slug": "in-progress",
      "color": "#3B82F6"
    },
    "priority": {
      "id": 2,
      "name": "High",
      "slug": "high",
      "color": "#EF4444",
      "level": 4
    },
    "project": {
      "id": 1,
      "title": "E-commerce Platform",
      "description": "Modern e-commerce solution"
    },
    "task_type": {
      "id": 1,
      "task_type": "Web Development",
      "description": "Website development tasks"
    },
    "template": {
      "id": 1,
      "title": "Web Development Template",
      "description": "Standard web development brief"
    },
    "users": [
      {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane@example.com"
      }
    ],
    "clients": [
      {
        "id": 1,
        "name": "ABC Company",
        "company": "ABC Corp"
      }
    ],
    "deliverables": [
      {
        "id": 1,
        "title": "Website Design",
        "description": "Complete website design",
        "type": "design",
        "quantity": 1,
        "completed_at": null
      }
    ],
    "media": [
      {
        "id": 1,
        "name": "design-mockup.jpg",
        "file_name": "design-mockup_123.jpg",
        "mime_type": "image/jpeg",
        "size": 1024000
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task data)
- **Read**: `statuses` table (status information)
- **Read**: `priorities` table (priority information)
- **Read**: `projects` table (project information)
- **Read**: `task_types` table (task type information)
- **Read**: `task_brief_templates` table (template information)
- **Read**: `task_user` table (user assignments)
- **Read**: `task_client` table (client assignments)
- **Read**: `users` table (assigned users)
- **Read**: `clients` table (assigned clients)
- **Read**: `task_deliverables` table (deliverables)
- **Read**: `media` table (attached files)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page
- `/tasks/{id}/edit` - Task edit page

---

### PUT `/tasks/{id}`
**Purpose**: Update existing task

**Request Body**:
```json
{
  "title": "Updated Task Title",
  "description": "Updated description",
  "status_id": 2,
  "priority_id": 1,
  "user_ids": [1, 3],
  "client_ids": [1]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Task updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Task Title",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `tasks` table (task data)
- **Delete/Create**: `task_user` table (user assignments)
- **Delete/Create**: `task_client` table (client assignments)

**Frontend Pages**:
- `/tasks/{id}/edit` - Task edit page
- `/tasks/{id}` - Task detail page (redirects after update)

---

### DELETE `/tasks/{id}`
**Purpose**: Delete specific task

**Response**:
```json
{
  "success": true,
  "message": "Task deleted successfully"
}
```

**Database Operations**:
- **Delete**: `tasks` table (cascades to related tables)
- **Delete**: `task_user` table (user assignments)
- **Delete**: `task_client` table (client assignments)
- **Delete**: `task_deliverables` table (deliverables)
- **Delete**: `media` table (attached files)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page
- `/tasks` - Task list page (redirects after deletion)

---

### DELETE `/tasks/multiple`
**Purpose**: Delete multiple tasks

**Request Body**:
```json
{
  "task_ids": [1, 2, 3]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Tasks deleted successfully"
}
```

**Database Operations**:
- **Delete**: `tasks` table (multiple records)
- **Delete**: Related pivot tables and media

**Frontend Pages**:
- `/tasks` - Task list page (bulk actions)

---

### PUT `/tasks/{id}/status`
**Purpose**: Update task status

**Request Body**:
```json
{
  "status_id": 2
}
```

**Response**:
```json
{
  "success": true,
  "message": "Task status updated successfully",
  "data": {
    "status": {
      "id": 2,
      "name": "Completed",
      "slug": "completed",
      "color": "#10B981"
    }
  }
}
```

**Database Operations**:
- **Update**: `tasks.status_id`

**Frontend Pages**:
- `/tasks/{id}` - Task detail page
- `/tasks` - Task list page (status updates)

---

### PUT `/tasks/{id}/deadline`
**Purpose**: Update task deadline

**Request Body**:
```json
{
  "end_date": "2025-09-15"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Task deadline updated successfully",
  "data": {
    "end_date": "2025-09-15"
  }
}
```

**Database Operations**:
- **Update**: `tasks.end_date`

**Frontend Pages**:
- `/tasks/{id}` - Task detail page
- `/tasks/{id}/edit` - Task edit page
