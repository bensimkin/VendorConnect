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

---

### POST `/tasks/{id}/question-answer`
**Purpose**: Submit answer to task brief question

**Request Body**:
```json
{
  "question_id": 1,
  "answer": "My answer to the question"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Question answer submitted successfully"
}
```

**Database Operations**:
- **Create**: `question_answered` table (stores question answers)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (brief questions section)

---

### POST `/tasks/{id}/checklist-answer`
**Purpose**: Submit checklist answer

**Request Body**:
```json
{
  "checklist_id": 1,
  "answers": [
    {
      "item_id": 1,
      "completed": true
    },
    {
      "item_id": 2,
      "completed": false
    }
  ]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Checklist answers submitted successfully"
}
```

**Database Operations**:
- **Create**: `checklist_answered` table (stores checklist answers)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (brief checklist section)

---

### GET `/tasks/{id}/information`
**Purpose**: Get detailed task information including brief questions and checklist

**Response**:
```json
{
  "success": true,
  "data": {
    "task": {
      "id": 1,
      "title": "Website Redesign",
      "description": "Complete website redesign for client"
    },
    "brief_questions": [
      {
        "id": 1,
        "question_text": "What is your target audience?",
        "question_type": "text",
        "required": true,
        "answer": "Young professionals aged 25-35"
      }
    ],
    "brief_checklist": [
      {
        "id": 1,
        "checklist": [
          {
            "id": 1,
            "text": "Design mockups completed",
            "completed": true
          },
          {
            "id": 2,
            "text": "Client feedback received",
            "completed": false
          }
        ]
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task data)
- **Read**: `task_brief_questions` table (template questions)
- **Read**: `task_brief_checklists` table (template checklists)
- **Read**: `question_answered` table (user answers)
- **Read**: `checklist_answered` table (user checklist answers)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (information tab)

---

### POST `/tasks/{id}/stop-repetition`
**Purpose**: Stop task repetition

**Response**:
```json
{
  "success": true,
  "message": "Task repetition stopped successfully"
}
```

**Database Operations**:
- **Update**: `tasks.is_repeating` (sets to 0)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (repetition controls)

---

### POST `/tasks/{id}/resume-repetition`
**Purpose**: Resume task repetition

**Response**:
```json
{
  "success": true,
  "message": "Task repetition resumed successfully"
}
```

**Database Operations**:
- **Update**: `tasks.is_repeating` (sets to 1)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (repetition controls)

---

### GET `/tasks/{id}/repeating-history`
**Purpose**: Get task repetition history

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "created_at": "2025-08-01T06:00:00.000000Z",
      "status": "completed"
    },
    {
      "id": 2,
      "created_at": "2025-08-08T06:00:00.000000Z",
      "status": "in_progress"
    }
  ]
}
```

**Database Operations**:
- **Read**: `tasks` table (repetition history)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (repetition history)

---

## 📎 Task Media APIs

### POST `/tasks/{id}/media`
**Purpose**: Upload media files to task

**Request Body**:
```
multipart/form-data with media files
```

**Response**:
```json
{
  "success": true,
  "message": "Media uploaded successfully",
  "data": [
    {
      "id": 1,
      "name": "design-mockup.jpg",
      "file_name": "design-mockup_123.jpg",
      "mime_type": "image/jpeg",
      "size": 1024000,
      "url": "https://example.com/storage/media/design-mockup_123.jpg"
    }
  ]
}
```

**Database Operations**:
- **Create**: `media` table (file metadata)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (media section)

---

### GET `/tasks/{id}/media`
**Purpose**: Get task media files

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "design-mockup.jpg",
      "file_name": "design-mockup_123.jpg",
      "mime_type": "image/jpeg",
      "size": 1024000,
      "url": "https://example.com/storage/media/design-mockup_123.jpg"
    }
  ]
}
```

**Database Operations**:
- **Read**: `media` table (task media files)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (media section)

---

### DELETE `/media/{mediaId}`
**Purpose**: Delete specific media file

**Response**:
```json
{
  "success": true,
  "message": "Media deleted successfully"
}
```

**Database Operations**:
- **Delete**: `media` table (removes file record)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (media section)

---

### DELETE `/media`
**Purpose**: Delete multiple media files

**Request Body**:
```json
{
  "media_ids": [1, 2, 3]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Media files deleted successfully"
}
```

**Database Operations**:
- **Delete**: `media` table (multiple records)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (bulk media actions)

---

## 💬 Task Messages APIs

### POST `/tasks/{id}/messages`
**Purpose**: Upload message to task

**Request Body**:
```json
{
  "message": "Task update message",
  "attachment": "file" // optional
}
```

**Response**:
```json
{
  "success": true,
  "message": "Message uploaded successfully",
  "data": {
    "id": 1,
    "message": "Task update message",
    "attachment": "message_attachment.pdf",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `ch_messages` table (task messages)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (messages section)

---

### GET `/tasks/{id}/messages`
**Purpose**: Get task messages

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "message": "Task update message",
      "attachment": "message_attachment.pdf",
      "from_id": 1,
      "from_user": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe"
      },
      "created_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `ch_messages` table (task messages)
- **Read**: `users` table (message sender info)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (messages section)

---

### DELETE `/messages/{messageId}`
**Purpose**: Delete specific message

**Response**:
```json
{
  "success": true,
  "message": "Message deleted successfully"
}
```

**Database Operations**:
- **Delete**: `ch_messages` table (removes message)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (messages section)

---

### DELETE `/messages`
**Purpose**: Delete multiple messages

**Request Body**:
```json
{
  "message_ids": [1, 2, 3]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Messages deleted successfully"
}
```

**Database Operations**:
- **Delete**: `ch_messages` table (multiple records)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (bulk message actions)

---

## 📦 Task Deliverable APIs

### GET `/tasks/{taskId}/deliverables`
**Purpose**: Get task deliverables

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "task_id": 1,
      "title": "Website Design",
      "description": "Complete website design",
      "type": "design",
      "quantity": 1,
      "completed_at": null,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `task_deliverables` table (deliverable data)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

### POST `/tasks/{taskId}/deliverables`
**Purpose**: Create task deliverable

**Request Body**:
```json
{
  "title": "Logo Design",
  "description": "Company logo design",
  "type": "design",
  "quantity": 1
}
```

**Response**:
```json
{
  "success": true,
  "message": "Deliverable created successfully",
  "data": {
    "id": 1,
    "title": "Logo Design",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `task_deliverables` table (deliverable data)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

### GET `/tasks/{taskId}/deliverables/{deliverableId}`
**Purpose**: Get specific deliverable

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "task_id": 1,
    "title": "Website Design",
    "description": "Complete website design",
    "type": "design",
    "quantity": 1,
    "completed_at": null,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Read**: `task_deliverables` table (deliverable data)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

### PUT `/tasks/{taskId}/deliverables/{deliverableId}`
**Purpose**: Update deliverable

**Request Body**:
```json
{
  "title": "Updated Logo Design",
  "description": "Updated logo description",
  "type": "design",
  "quantity": 2
}
```

**Response**:
```json
{
  "success": true,
  "message": "Deliverable updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Logo Design",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `task_deliverables` table (deliverable data)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

### DELETE `/tasks/{taskId}/deliverables/{deliverableId}`
**Purpose**: Delete deliverable

**Response**:
```json
{
  "success": true,
  "message": "Deliverable deleted successfully"
}
```

**Database Operations**:
- **Delete**: `task_deliverables` table (removes deliverable)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

### POST `/tasks/{taskId}/deliverables/{deliverableId}/complete`
**Purpose**: Mark deliverable as complete

**Response**:
```json
{
  "success": true,
  "message": "Deliverable marked as complete",
  "data": {
    "completed_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `task_deliverables.completed_at` (sets completion timestamp)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

### DELETE `/tasks/{taskId}/deliverables/{deliverableId}/files/{mediaId}`
**Purpose**: Delete deliverable file

**Response**:
```json
{
  "success": true,
  "message": "Deliverable file deleted successfully"
}
```

**Database Operations**:
- **Delete**: `media` table (removes file)

**Frontend Pages**:
- `/tasks/{id}` - Task detail page (deliverables section)

---

## 📁 Project Management APIs

### GET `/projects`
**Purpose**: Get all projects with filtering and pagination

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status_id` (optional): Filter by status
- `priority_id` (optional): Filter by priority
- `workspace_id` (optional): Filter by workspace
- `search` (optional): Search in title and description
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort order (asc/desc, default: desc)

**Response**:
```json
{
  "success": true,
  "message": "Projects retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "E-commerce Platform",
        "description": "Modern e-commerce solution",
        "start_date": "2025-08-01",
        "end_date": "2025-12-31",
        "status_id": 1,
        "priority_id": 2,
        "workspace_id": 1,
        "created_by": 1,
        "created_at": "2025-08-28T06:00:00.000000Z",
        "updated_at": "2025-08-28T06:00:00.000000Z",
        "status": {
          "id": 1,
          "name": "Active",
          "color": "#3B82F6"
        },
        "priority": {
          "id": 2,
          "name": "High",
          "color": "#EF4444",
          "level": 4
        },
        "workspace": {
          "id": 1,
          "name": "Main Workspace"
        },
        "creator": {
          "id": 1,
          "first_name": "John",
          "last_name": "Doe"
        },
        "users": [
          {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Smith"
          }
        ],
        "clients": [
          {
            "id": 1,
            "name": "ABC Company"
          }
        ],
        "task_count": 12,
        "completed_task_count": 8
      }
    ],
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 45,
    "from": 1,
    "to": 15
  }
}
```

**Database Operations**:
- **Read**: `projects` table (main project data)
- **Read**: `project_statuses` table (status information)
- **Read**: `priorities` table (priority information)
- **Read**: `workspaces` table (workspace information)
- **Read**: `users` table (creator and assigned users)
- **Read**: `project_user` table (user assignments)
- **Read**: `project_client` table (client assignments)
- **Read**: `clients` table (assigned clients)
- **Read**: `tasks` table (task counts)

**Frontend Pages**:
- `/projects` - Project list page
- `/project-management` - Project management page
- `/dashboard` - Dashboard (recent projects)

---

### POST `/projects`
**Purpose**: Create a new project

**Request Body**:
```json
{
  "title": "New Project",
  "description": "Project description",
  "start_date": "2025-08-01",
  "end_date": "2025-12-31",
  "status_id": 1,
  "priority_id": 2,
  "workspace_id": 1,
  "user_ids": [1, 2],
  "client_ids": [1, 2]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Project created successfully",
  "data": {
    "id": 1,
    "title": "New Project",
    "description": "Project description",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `projects` table (main project data)
- **Create**: `project_user` table (user assignments)
- **Create**: `project_client` table (client assignments)

**Frontend Pages**:
- `/projects/create` - Create project page
- `/projects` - Project list page (redirects after creation)

---

### GET `/projects/{id}`
**Purpose**: Get specific project details

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "E-commerce Platform",
    "description": "Modern e-commerce solution",
    "start_date": "2025-08-01",
    "end_date": "2025-12-31",
    "status_id": 1,
    "priority_id": 2,
    "workspace_id": 1,
    "created_by": 1,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z",
    "status": {
      "id": 1,
      "name": "Active",
      "color": "#3B82F6"
    },
    "priority": {
      "id": 2,
      "name": "High",
      "color": "#EF4444",
      "level": 4
    },
    "workspace": {
      "id": 1,
      "name": "Main Workspace"
    },
    "creator": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe"
    },
    "users": [
      {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith"
      }
    ],
    "clients": [
      {
        "id": 1,
        "name": "ABC Company"
      }
    ],
    "tasks": [
      {
        "id": 1,
        "title": "Website Design",
        "status": {
          "id": 1,
          "name": "In Progress"
        },
        "priority": {
          "id": 2,
          "name": "High"
        }
      }
    ],
    "statistics": {
      "total_tasks": 12,
      "completed_tasks": 8,
      "pending_tasks": 4,
      "completion_percentage": 66.67
    }
  }
}
```

**Database Operations**:
- **Read**: `projects` table (project data)
- **Read**: `project_statuses` table (status information)
- **Read**: `priorities` table (priority information)
- **Read**: `workspaces` table (workspace information)
- **Read**: `users` table (creator and assigned users)
- **Read**: `project_user` table (user assignments)
- **Read**: `project_client` table (client assignments)
- **Read**: `clients` table (assigned clients)
- **Read**: `tasks` table (project tasks)
- **Read**: `statuses` table (task status)
- **Read**: `priorities` table (task priority)

**Frontend Pages**:
- `/projects/{id}` - Project detail page
- `/projects/{id}/edit` - Project edit page

---

### PUT `/projects/{id}`
**Purpose**: Update existing project

**Request Body**:
```json
{
  "title": "Updated Project Title",
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
  "message": "Project updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Project Title",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `projects` table (project data)
- **Delete/Create**: `project_user` table (user assignments)
- **Delete/Create**: `project_client` table (client assignments)

**Frontend Pages**:
- `/projects/{id}/edit` - Project edit page
- `/projects/{id}` - Project detail page (redirects after update)

---

### DELETE `/projects/{id}`
**Purpose**: Delete specific project

**Response**:
```json
{
  "success": true,
  "message": "Project deleted successfully"
}
```

**Database Operations**:
- **Delete**: `projects` table (cascades to related tables)
- **Delete**: `project_user` table (user assignments)
- **Delete**: `project_client` table (client assignments)
- **Delete**: `tasks` table (project tasks)

**Frontend Pages**:
- `/projects/{id}` - Project detail page
- `/projects` - Project list page (redirects after deletion)

---

### GET `/projects/{id}/statistics`
**Purpose**: Get project statistics

**Response**:
```json
{
  "success": true,
  "data": {
    "total_tasks": 12,
    "completed_tasks": 8,
    "pending_tasks": 4,
    "overdue_tasks": 1,
    "completion_percentage": 66.67,
    "average_task_duration": 5.2,
    "team_performance": {
      "total_hours": 120,
      "efficiency_score": 85.5
    },
    "timeline": {
      "start_date": "2025-08-01",
      "end_date": "2025-12-31",
      "days_remaining": 45,
      "on_schedule": true
    }
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task counts and statistics)
- **Read**: `projects` table (project timeline)
- **Read**: `time_trackers` table (time tracking data)

**Frontend Pages**:
- `/projects/{id}` - Project detail page (statistics tab)
- `/project-management` - Project management dashboard

---

### GET `/projects/{id}/tasks`
**Purpose**: Get project tasks

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status_id` (optional): Filter by status
- `priority_id` (optional): Filter by priority
- `user_id` (optional): Filter by assigned user

**Response**:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Website Design",
        "description": "Complete website design",
        "status_id": 1,
        "priority_id": 2,
        "start_date": "2025-08-01",
        "end_date": "2025-08-31",
        "created_at": "2025-08-28T06:00:00.000000Z",
        "status": {
          "id": 1,
          "name": "In Progress",
          "color": "#3B82F6"
        },
        "priority": {
          "id": 2,
          "name": "High",
          "color": "#EF4444"
        },
        "users": [
          {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Smith"
          }
        ]
      }
    ],
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 25,
    "from": 1,
    "to": 15
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (project tasks)
- **Read**: `statuses` table (task status)
- **Read**: `priorities` table (task priority)
- **Read**: `task_user` table (user assignments)
- **Read**: `users` table (assigned users)

**Frontend Pages**:
- `/projects/{id}` - Project detail page (tasks tab)
- `/project-management` - Project management page

