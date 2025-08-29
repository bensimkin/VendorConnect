# VendorConnect API Documentation

## Overview
This document provides comprehensive documentation for all VendorConnect API endpoints, including request/response formats, database operations, and frontend page associations.

**Base URL**: `https://vc.themastermind.com.au/api/v1`

**Last Updated**: August 29, 2025

## ⚠️ CRITICAL: Field Naming Inconsistencies

**IMPORTANT**: This API has field naming inconsistencies that affect frontend development. The following models return duplicate fields due to Laravel model `$appends` attributes:

### **Status Model**
- **Database Field**: `statuses.title`
- **API Response**: Both `status.title` AND `status.name` (duplicate)
- **Recommended Usage**: Use `status.title` for consistency

### **Priority Model** 
- **Database Field**: `priorities.title`
- **API Response**: Both `priority.title` AND `priority.name` (duplicate)
- **Recommended Usage**: Use `priority.title` for consistency

### **TaskType Model**
- **Database Field**: `task_types.task_type`
- **API Response**: Both `task_type.task_type` AND `task_type.name` (duplicate)
- **Recommended Usage**: Use `task_type.task_type` for consistency

### **Project Model**
- **Database Field**: `projects.title`
- **API Response**: `project.title` (correct)
- **Frontend Usage**: Use `project.title` (correct)

**Impact**: Frontend interfaces should use the primary database field names (`title`, `task_type`) rather than the appended `name` fields to maintain consistency with the database schema.

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
    "permissions": ["view_tasks", "create_tasks", "edit_tasks"],
    "token": "1|abc123...",
    "token_type": "Bearer",
    "expires_at": "2025-09-04T06:30:00.000000Z"
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

## ⚠️ CRITICAL: Field Naming Inconsistencies

**IMPORTANT**: This API has field naming inconsistencies that affect frontend development. The following models return duplicate fields due to Laravel model `$appends` attributes:

### **Status Model**
- **Database Field**: `statuses.title`
- **API Response**: Both `status.title` AND `status.name` (duplicate)
- **Recommended Usage**: Use `status.title` for consistency

### **Priority Model** 
- **Database Field**: `priorities.title`
- **API Response**: Both `priority.title` AND `priority.name` (duplicate)
- **Recommended Usage**: Use `priority.title` for consistency

### **TaskType Model**
- **Database Field**: `task_types.task_type`
- **API Response**: Both `task_type.task_type` AND `task_type.name` (duplicate)
- **Recommended Usage**: Use `task_type.task_type` for consistency

### **Project Model**
- **Database Field**: `projects.title`
- **API Response**: `project.title` (correct)
- **Frontend Usage**: Use `project.title` (correct)

**Impact**: Frontend interfaces should use the primary database field names (`title`, `task_type`) rather than the appended `name` fields to maintain consistency with the database schema.

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
    "permissions": ["view_tasks", "create_tasks", "edit_tasks"],
    "token": "1|abc123...",
    "token_type": "Bearer",
    "expires_at": "2025-09-04T06:30:00.000000Z"
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

**Role-Based Access**:
- **Admin/Sub-Admin**: Full dashboard with all data
- **Requester**: Dashboard filtered to show only their created tasks
- **Tasker**: Dashboard filtered to show only their assigned tasks

**Response**:
```json
{
  "success": true,
  "message": "Dashboard data retrieved successfully",
  "data": {
    "overview": {
      "total_tasks": 150,
      "total_users": 25,
      "total_clients": 45,
      "total_projects": 25
    },
    "task_statistics": {
      "completed": 120,
      "pending": 25,
      "overdue": 5,
      "completion_rate": 80.0
    },
    "recent_tasks": [
      {
        "id": 1,
        "title": "Website Redesign",
        "status": {
          "id": 1,
          "title": "In Progress"
        },
        "priority": {
          "id": 2,
          "title": "High"
        },
        "created_at": "2025-08-28T06:00:00.000000Z"
      }
    ],
    "user_activity": [
      {
        "user": "John Doe",
        "tasks_completed": 15,
        "last_activity": "2025-08-28T06:00:00.000000Z"
      }
    ],
    "task_trend": {
      "daily_completions": [5, 8, 12, 6, 9, 11, 7],
      "weekly_average": 8.3
    },
    "project_management": {
      "active_projects": 20,
      "completed_projects": 5,
      "project_completion_rate": 80.0
    },
    "statuses": [
      {
        "id": 1,
        "title": "In Progress"
      },
      {
        "id": 2,
        "title": "Completed"
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
- `project_id` (optional): Filter by project
- `search` (optional): Search in title and description
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort order (asc/desc, default: desc)

**Role-Based Access**:
- **Admin/Sub-Admin**: Can see all tasks
- **Requester**: Only sees tasks they created
- **Tasker**: Only sees tasks they're assigned to

**Response**:
```json
{
  "success": true,
  "message": "Tasks retrieved successfully",
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
      "is_repeating": false,
      "repeat_frequency": null,
      "repeat_interval": null,
      "repeat_until": null,
      "repeat_active": true,
      "parent_task_id": null,
      "last_repeated_at": null,
      "created_by": 1,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z",
      "status": {
        "id": 1,
        "title": "In Progress",
        "description": "Task is currently being worked on",
        "color": "#3B82F6",
        "slug": "in-progress"
      },
      "priority": {
        "id": 2,
        "title": "High",
        "description": "High priority task",
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
          "last_name": "Smith"
        }
      ]
    }
  ],
  "pagination": {
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
  "is_repeating": false,
  "repeat_frequency": "weekly",
  "repeat_interval": 1,
  "repeat_until": "2025-12-31",
  "user_ids": [1, 2],
  "tag_ids": [1, 2]
}
```

**Template Integration**:
- If `template_id` is provided, the task will inherit:
  - Template title and description
  - Template questions and checklist
  - Template deliverable quantity
  - Template standard brief

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
- **Create**: Template questions and checklist data stored in JSON fields

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
    "repeat_frequency": null,
    "repetition_interval": null,
    "repeat_until": null,
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
  "client_id": 1,
  "client_ids": [1, 2]
}
```

**Client Assignment Notes**:
- **Single Client Mode**: Use `client_id` field (default)
- **Multiple Clients Mode**: Use `client_ids` array (requires setting `allow_multiple_clients_per_project` enabled)
- **Required Client**: If setting `require_project_client` is enabled, at least one client must be provided
- **Max Clients**: Controlled by setting `max_clients_per_project` (default: 5)

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
- **Create**: `client_project` table (client assignments)

**Frontend Pages**:
- `/projects/create` - Create project page
- `/projects` - Project list page (redirects after creation)

**Recent Fixes**:
- **Fixed "Unnamed Project" Issue**: Project creation now properly attaches clients to projects
- **Client Relationship**: Projects now include client data in responses
- **Foreign Key Constraint**: Fixed admin_id foreign key constraint in client_project table

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
  "client_id": 1,
  "client_ids": [1]
}
```

**Client Assignment Notes**:
- **Single Client Mode**: Use `client_id` field to assign/replace single client
- **Multiple Clients Mode**: Use `client_ids` array to sync multiple clients
- **Client Sync**: Existing client assignments are replaced with new ones

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
- **Delete/Create**: `client_project` table (client assignments)

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

---

## 👥 Client Management APIs

### GET `/clients`
**Purpose**: Get all clients with filtering and pagination

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `search` (optional): Search in name and company
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort order (asc/desc, default: desc)

**Response**:
```json
{
  "success": true,
  "message": "Clients retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Smith",
      "company": "ABC Corp",
      "email": "john@abccorp.com",
      "phone": "+1234567890",
      "address": "123 Main St, City, State",
      "website": "https://abccorp.com",
      "notes": "Important client",
      "status": 1,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z",
      "projects_count": 5,
      "tasks_count": 12
    }
  ],
  "pagination": {
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
- **Read**: `clients` table (client data)
- **Read**: `projects` table (project counts)
- **Read**: `task_client` table (task assignments)

**Frontend Pages**:
- `/clients` - Client list page
- `/dashboard` - Dashboard (recent clients)

---

### POST `/clients`
**Purpose**: Create a new client

**Request Body**:
```json
{
  "name": "Jane Doe",
  "company": "XYZ Inc",
  "email": "jane@xyzinc.com",
  "phone": "+1234567890",
  "address": "456 Oak St, City, State",
  "website": "https://xyzinc.com",
  "notes": "New client"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Client created successfully",
  "data": {
    "id": 2,
    "name": "Jane Doe",
    "company": "XYZ Inc",
    "email": "jane@xyzinc.com",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `clients` table (client data)

**Frontend Pages**:
- `/clients/create` - Create client page
- `/clients` - Client list page (redirects after creation)

---

### GET `/clients/{id}`
**Purpose**: Get specific client details

**Response**:
```json
{
  "success": true,
  "message": "Client retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Smith",
    "company": "ABC Corp",
    "email": "john@abccorp.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, State",
    "website": "https://abccorp.com",
    "notes": "Important client",
    "status": 1,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Read**: `clients` table (client data)

**Frontend Pages**:
- `/clients/{id}` - Client detail page
- `/clients/{id}/edit` - Client edit page

> Use `/clients/{id}/projects` and `/clients/{id}/tasks` to fetch related projects and tasks.

---

### PUT `/clients/{id}`
**Purpose**: Update existing client

**Request Body**:
```json
{
  "name": "John Smith Updated",
  "company": "ABC Corp Updated",
  "email": "john.updated@abccorp.com",
  "phone": "+1234567890",
  "address": "Updated address",
  "website": "https://abccorp-updated.com",
  "notes": "Updated notes"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Client updated successfully",
  "data": {
    "id": 1,
    "name": "John Smith Updated",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `clients` table (client data)

**Frontend Pages**:
- `/clients/{id}/edit` - Client edit page
- `/clients/{id}` - Client detail page (redirects after update)

---

### DELETE `/clients/{id}`
**Purpose**: Delete specific client

**Response**:
```json
{
  "success": true,
  "message": "Client deleted successfully"
}
```

**Database Operations**:
- **Delete**: `clients` table (cascades to related tables)
- **Delete**: `project_client` table (project assignments)
- **Delete**: `task_client` table (task assignments)
- **Delete**: `client_credentials` table (client credentials)

**Frontend Pages**:
- `/clients/{id}` - Client detail page
- `/clients` - Client list page (redirects after deletion)

---

### GET `/clients/{id}/projects`
**Purpose**: Get client projects

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)

**Response**:
```json
{
  "success": true,
  "message": "Client projects retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "E-commerce Platform",
      "description": "Modern e-commerce solution",
      "status_id": 1,
      "start_date": "2025-08-01",
      "end_date": "2025-12-31",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "status": {
        "id": 1,
        "name": "Active",
        "color": "#3B82F6"
      }
    }
  ],
  "pagination": {
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
- **Read**: `projects` table (client projects)
- **Read**: `client_project` table (client assignments)
- **Read**: `project_statuses` table (project status)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (projects tab)

---

### GET `/clients/{id}/tasks`
**Purpose**: Get client tasks

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status_id` (optional): Filter by status
- `priority_id` (optional): Filter by priority

**Response**:
```json
{
  "success": true,
  "message": "Client tasks retrieved successfully",
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
      }
    }
  ],
  "pagination": {
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
- **Read**: `tasks` table (client tasks)
- **Read**: `projects` table (via project relationship)
- **Read**: `client_project` table (project-client pivot)
- **Read**: `statuses` table (task status)
- **Read**: `priorities` table (task priority)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (tasks tab)

**Recent Fix (2025-08-29)**:
- **Issue**: API was failing with 500 error due to invalid `client_id` query on projects table
- **Fix**: Updated to use correct `client_project` pivot table relationship
- **Impact**: Client detail pages now load successfully without server errors

---

## 🔐 Client Credential APIs

### GET `/clients/{clientId}/credentials`
**Purpose**: Get client credentials

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "client_id": 1,
      "platform": "WordPress",
      "username": "admin",
      "password": "encrypted_password",
      "url": "https://abccorp.com/wp-admin",
      "notes": "Main website credentials",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `client_credentials` table (credential data)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (credentials tab)

---

### POST `/clients/{clientId}/credentials`
**Purpose**: Create client credential

**Request Body**:
```json
{
  "platform": "WordPress",
  "username": "admin",
  "password": "password123",
  "url": "https://abccorp.com/wp-admin",
  "notes": "Main website credentials"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Credential created successfully",
  "data": {
    "id": 1,
    "platform": "WordPress",
    "username": "admin",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `client_credentials` table (credential data)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (credentials tab)

---

### GET `/clients/{clientId}/credentials/{credentialId}`
**Purpose**: Get specific credential

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "client_id": 1,
    "platform": "WordPress",
    "username": "admin",
    "password": "encrypted_password",
    "url": "https://abccorp.com/wp-admin",
    "notes": "Main website credentials",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Read**: `client_credentials` table (credential data)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (credentials tab)

---

### PUT `/clients/{clientId}/credentials/{credentialId}`
**Purpose**: Update credential

**Request Body**:
```json
{
  "platform": "WordPress Updated",
  "username": "admin_updated",
  "password": "new_password123",
  "url": "https://abccorp-updated.com/wp-admin",
  "notes": "Updated credentials"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Credential updated successfully",
  "data": {
    "id": 1,
    "platform": "WordPress Updated",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `client_credentials` table (credential data)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (credentials tab)

---

### DELETE `/clients/{clientId}/credentials/{credentialId}`
**Purpose**: Delete credential

**Response**:
```json
{
  "success": true,
  "message": "Credential deleted successfully"
}
```

**Database Operations**:
- **Delete**: `client_credentials` table (removes credential)

**Frontend Pages**:
- `/clients/{id}` - Client detail page (credentials tab)

---

## 👤 User Management APIs

### GET `/users`
**Purpose**: Get all users with filtering and pagination

**Query Parameters**:
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `search` (optional): Search in name and email
- `role_id` (optional): Filter by role
- `status` (optional): Filter by status (1=active, 0=inactive)
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort order (asc/desc, default: desc)

**Response**:
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "photo": "user_photo.jpg",
        "status": 1,
        "dark_mode": 0,
        "country_code": "AU",
        "last_login_at": "2025-08-28T06:00:00.000000Z",
        "created_at": "2025-08-28T06:00:00.000000Z",
        "updated_at": "2025-08-28T06:00:00.000000Z",
        "roles": [
          {
            "id": 1,
            "name": "admin"
          }
        ],
        "tasks_count": 15,
        "projects_count": 8
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
- **Read**: `users` table (user data)
- **Read**: `model_has_roles` table (role assignments)
- **Read**: `roles` table (role information)
- **Read**: `model_has_permissions` table (permissions)
- **Read**: `permissions` table (permission information)
- **Read**: `tasks` table (user tasks)
- **Read**: `projects` table (user projects)
- **Read**: `statuses` table (task status)
- **Read**: `project_statuses` table (project status)

**Frontend Pages**:
- `/users` - User list page
- `/dashboard` - Dashboard (team members)

---

### POST `/users`
**Purpose**: Create a new user

**Request Body**:
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_ids": [1, 2],
  "status": 1,
  "country_code": "AU"
}
```

**Response**:
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 2,
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `users` table (user data)
- **Create**: `model_has_roles` table (role assignments)

**Frontend Pages**:
- `/users/create` - Create user page
- `/users` - User list page (redirects after creation)

---

### GET `/users/{id}`
**Purpose**: Get specific user details

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "photo": "user_photo.jpg",
    "status": 1,
    "dark_mode": 0,
    "country_code": "AU",
    "last_login_at": "2025-08-28T06:00:00.000000Z",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z",
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "permissions": [
          {
            "id": 1,
            "name": "view_tasks"
          }
        ]
      }
    ],
    "tasks": [
      {
        "id": 1,
        "title": "Website Design",
        "status": {
          "id": 1,
          "name": "In Progress"
        }
      }
    ],
    "projects": [
      {
        "id": 1,
        "title": "E-commerce Platform",
        "status": {
          "id": 1,
          "name": "Active"
        }
      }
    ],
    "statistics": {
      "total_tasks": 15,
      "completed_tasks": 12,
      "total_projects": 8,
      "active_projects": 6
    }
  }
}
```

**Database Operations**:
- **Read**: `users` table (user data)
- **Read**: `model_has_roles` table (role assignments)
- **Read**: `roles` table (role information)
- **Read**: `model_has_permissions` table (permissions)
- **Read**: `permissions` table (permission information)
- **Read**: `tasks` table (user tasks)
- **Read**: `projects` table (user projects)
- **Read**: `statuses` table (task status)
- **Read**: `project_statuses` table (project status)

**Frontend Pages**:
- `/users/{id}` - User detail page
- `/users/{id}/edit` - User edit page

---

### PUT `/users/{id}`
**Purpose**: Update existing user

**Request Body**:
```json
{
  "first_name": "John Updated",
  "last_name": "Doe Updated",
  "email": "john.updated@example.com",
  "role_ids": [1, 3],
  "status": 1,
  "country_code": "US"
}
```

**Response**:
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 1,
    "first_name": "John Updated",
    "last_name": "Doe Updated",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `users` table (user data)
- **Delete/Create**: `model_has_roles` table (role assignments)

**Frontend Pages**:
- `/users/{id}/edit` - User edit page
- `/users/{id}` - User detail page (redirects after update)

---

### DELETE `/users/{id}`
**Purpose**: Delete specific user

**Response**:
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

**Database Operations**:
- **Delete**: `users` table (cascades to related tables)
- **Delete**: `model_has_roles` table (role assignments)
- **Delete**: `task_user` table (task assignments)
- **Delete**: `project_user` table (project assignments)

**Frontend Pages**:
- `/users/{id}` - User detail page
- `/users` - User list page (redirects after deletion)

---

### PUT `/users/{id}/password`
**Purpose**: Update user password

**Request Body**:
```json
{
  "current_password": "old_password",
  "password": "new_password123",
  "password_confirmation": "new_password123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Password updated successfully"
}
```

**Database Operations**:
- **Update**: `users.password` (hashed password)

**Frontend Pages**:
- `/users/{id}/edit` - User edit page
- `/profile` - User profile page

---

### PUT `/users/{id}/status`
**Purpose**: Update user status (activate/deactivate)

**Request Body**:
```json
{
  "status": 0
}
```

**Response**:
```json
{
  "success": true,
  "message": "User status updated successfully",
  "data": {
    "status": 0
  }
}
```

**Database Operations**:
- **Update**: `users.status`

**Frontend Pages**:
- `/users/{id}` - User detail page
- `/users` - User list page

---

## 🏷️ Status Management APIs

### GET `/statuses`
**Purpose**: Get all statuses

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "In Progress",
      "slug": "in-progress",
      "color": "#3B82F6",
      "type": "task",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Completed",
      "slug": "completed",
      "color": "#10B981",
      "type": "task",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `statuses` table (status data)

**Frontend Pages**:
- `/tasks` - Task list page (status filter)
- `/projects` - Project list page (status filter)
- Task and project creation/edit forms

---

### POST `/statuses`
**Purpose**: Create a new status

**Request Body**:
```json
{
  "name": "On Hold",
  "slug": "on-hold",
  "color": "#F59E0B",
  "type": "task"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Status created successfully",
  "data": {
    "id": 3,
    "name": "On Hold",
    "slug": "on-hold",
    "color": "#F59E0B",
    "type": "task",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `statuses` table (status data)

**Frontend Pages**:
- Status management pages
- Task and project creation/edit forms

---

### GET `/statuses/{id}`
**Purpose**: Get specific status

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "In Progress",
    "slug": "in-progress",
    "color": "#3B82F6",
    "type": "task",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Read**: `statuses` table (status data)

**Frontend Pages**:
- Status management pages

---

### PUT `/statuses/{id}`
**Purpose**: Update existing status

**Request Body**:
```json
{
  "name": "In Progress Updated",
  "slug": "in-progress-updated",
  "color": "#1D4ED8",
  "type": "task"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Status updated successfully",
  "data": {
    "id": 1,
    "name": "In Progress Updated",
    "slug": "in-progress-updated",
    "color": "#1D4ED8",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `statuses` table (status data)

**Frontend Pages**:
- Status management pages

---

### DELETE `/statuses/{id}`
**Purpose**: Delete specific status

**Response**:
```json
{
  "success": true,
  "message": "Status deleted successfully"
}
```

**Database Operations**:
- **Delete**: `statuses` table (removes status)

**Frontend Pages**:
- Status management pages

---

## ⚡ Priority Management APIs

### GET `/priorities`
**Purpose**: Get all priorities

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Low",
      "slug": "low",
      "color": "#6B7280",
      "level": 1,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "High",
      "slug": "high",
      "color": "#EF4444",
      "level": 4,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `priorities` table (priority data)

**Frontend Pages**:
- `/tasks` - Task list page (priority filter)
- `/projects` - Project list page (priority filter)
- Task and project creation/edit forms

---

### POST `/priorities`
**Purpose**: Create a new priority

**Request Body**:
```json
{
  "name": "Critical",
  "slug": "critical",
  "color": "#DC2626",
  "level": 5
}
```

**Response**:
```json
{
  "success": true,
  "message": "Priority created successfully",
  "data": {
    "id": 3,
    "name": "Critical",
    "slug": "critical",
    "color": "#DC2626",
    "level": 5,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `priorities` table (priority data)

**Frontend Pages**:
- Priority management pages
- Task and project creation/edit forms

---

### GET `/priorities/{id}`
**Purpose**: Get specific priority

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "High",
    "slug": "high",
    "color": "#EF4444",
    "level": 4,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Read**: `priorities` table (priority data)

**Frontend Pages**:
- Priority management pages

---

### PUT `/priorities/{id}`
**Purpose**: Update existing priority

**Request Body**:
```json
{
  "name": "High Updated",
  "slug": "high-updated",
  "color": "#DC2626",
  "level": 4
}
```

**Response**:
```json
{
  "success": true,
  "message": "Priority updated successfully",
  "data": {
    "id": 2,
    "name": "High Updated",
    "slug": "high-updated",
    "color": "#DC2626",
    "level": 4,
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `priorities` table (priority data)

**Frontend Pages**:
- Priority management pages

---

### DELETE `/priorities/{id}`
**Purpose**: Delete specific priority

**Response**:
```json
{
  "success": true,
  "message": "Priority deleted successfully"
}
```

**Database Operations**:
- **Delete**: `priorities` table (removes priority)

**Frontend Pages**:
- Priority management pages

---

## 📝 Task Type Management APIs

### GET `/task-types`
**Purpose**: Get all task types

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "task_type": "Web Development",
      "description": "Website development tasks",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    },
    {
      "id": 2,
      "task_type": "Graphic Design",
      "description": "Graphic design tasks",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `task_types` table (task type data)

**Frontend Pages**:
- `/tasks` - Task list page (type filter)
- Task creation/edit forms

---

### POST `/task-types`
**Purpose**: Create a new task type

**Request Body**:
```json
{
  "task_type": "Content Writing",
  "description": "Content writing tasks"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Task type created successfully",
  "data": {
    "id": 3,
    "task_type": "Content Writing",
    "description": "Content writing tasks",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `task_types` table (task type data)

**Frontend Pages**:
- Task type management pages
- Task creation/edit forms

---

### GET `/task-types/{id}`
**Purpose**: Get specific task type

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "task_type": "Web Development",
    "description": "Website development tasks",
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Read**: `task_types` table (task type data)

**Frontend Pages**:
- Task type management pages

---

### PUT `/task-types/{id}`
**Purpose**: Update existing task type

**Request Body**:
```json
{
  "task_type": "Web Development Updated",
  "description": "Updated website development tasks"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Task type updated successfully",
  "data": {
    "id": 1,
    "task_type": "Web Development Updated",
    "description": "Updated website development tasks",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `task_types` table (task type data)

**Frontend Pages**:
- Task type management pages

---

### DELETE `/task-types/{id}`
**Purpose**: Delete specific task type

**Response**:
```json
{
  "success": true,
  "message": "Task type deleted successfully"
}
```

**Database Operations**:
- **Delete**: `task_types` table (removes task type)

**Frontend Pages**:
- Task type management pages

---

## 📋 Task Brief Template APIs

### GET `/task-brief-templates`
**Purpose**: Get all task brief templates

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Web Development Template",
      "description": "Standard web development brief",
      "task_type_id": 1,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z",
      "task_type": {
        "id": 1,
        "task_type": "Web Development"
      },
      "questions_count": 5,
      "checklists_count": 2
    }
  ]
}
```

**Database Operations**:
- **Read**: `task_brief_templates` table (template data)
- **Read**: `task_types` table (task type information)
- **Read**: `task_brief_questions` table (question counts)
- **Read**: `task_brief_checklists` table (checklist counts)

**Frontend Pages**:
- `/templates` - Template list page
- Task creation forms (template selection)

---

### POST `/task-brief-templates`
**Purpose**: Create a new task brief template

**Request Body**:
```json
{
  "title": "Graphic Design Template",
  "description": "Standard graphic design brief",
  "task_type_id": 2
}
```

**Response**:
```json
{
  "success": true,
  "message": "Template created successfully",
  "data": {
    "id": 2,
    "title": "Graphic Design Template",
    "description": "Standard graphic design brief",
    "task_type_id": 2,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `task_brief_templates` table (template data)

**Frontend Pages**:
- `/templates/create` - Create template page
- `/templates` - Template list page (redirects after creation)

---

### GET `/task-brief-templates/{id}`
**Purpose**: Get specific task brief template

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Web Development Template",
    "description": "Standard web development brief",
    "task_type_id": 1,
    "created_at": "2025-08-28T06:00:00.000000Z",
    "updated_at": "2025-08-28T06:00:00.000000Z",
    "task_type": {
      "id": 1,
      "task_type": "Web Development"
    },
    "questions": [
      {
        "id": 1,
        "question_text": "What is your target audience?",
        "question_type": "text",
        "required": true
      }
    ],
    "checklists": [
      {
        "id": 1,
        "checklist": [
          {
            "id": 1,
            "text": "Design mockups completed"
          },
          {
            "id": 2,
            "text": "Client feedback received"
          }
        ]
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `task_brief_templates` table (template data)
- **Read**: `task_types` table (task type information)
- **Read**: `task_brief_questions` table (template questions)
- **Read**: `task_brief_checklists` table (template checklists)

**Frontend Pages**:
- `/templates/{id}` - Template detail page
- `/templates/{id}/edit` - Template edit page

---

### PUT `/task-brief-templates/{id}`
**Purpose**: Update existing task brief template

**Request Body**:
```json
{
  "title": "Web Development Template Updated",
  "description": "Updated web development brief",
  "task_type_id": 1
}
```

**Response**:
```json
{
  "success": true,
  "message": "Template updated successfully",
  "data": {
    "id": 1,
    "title": "Web Development Template Updated",
    "description": "Updated web development brief",
    "updated_at": "2025-08-28T06:30:00.000000Z"
  }
}
```

**Database Operations**:
- **Update**: `task_brief_templates` table (template data)

**Frontend Pages**:
- `/templates/{id}/edit` - Template edit page
- `/templates/{id}` - Template detail page (redirects after update)

---

### DELETE `/task-brief-templates/{id}`
**Purpose**: Delete specific task brief template

**Response**:
```json
{
  "success": true,
  "message": "Template deleted successfully"
}
```

**Database Operations**:
- **Delete**: `task_brief_templates` table (cascades to related tables)
- **Delete**: `task_brief_questions` table (template questions)
- **Delete**: `task_brief_checklists` table (template checklists)

**Frontend Pages**:
- `/templates/{id}` - Template detail page
- `/templates` - Template list page (redirects after deletion)

---

## ❓ Task Brief Question APIs

### GET `/task-brief-questions`
**Purpose**: Get all task brief questions

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "template_id": 1,
      "question_text": "What is your target audience?",
      "question_type": "text",
      "required": true,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `task_brief_questions` table (question data)

**Frontend Pages**:
- `/templates/{id}` - Template detail page
- Task creation forms

---

### POST `/task-brief-questions`
**Purpose**: Create a new task brief question

**Request Body**:
```json
{
  "template_id": 1,
  "question_text": "What is your brand color?",
  "question_type": "text",
  "required": false
}
```

**Response**:
```json
{
  "success": true,
  "message": "Question created successfully",
  "data": {
    "id": 2,
    "question_text": "What is your brand color?",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `task_brief_questions` table (question data)

**Frontend Pages**:
- `/templates/{id}/edit` - Template edit page

---

## ✅ Task Brief Checklist APIs

### GET `/task-brief-checklists`
**Purpose**: Get all task brief checklists

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "template_id": 1,
      "checklist": [
        {
          "id": 1,
          "text": "Design mockups completed"
        },
        {
          "id": 2,
          "text": "Client feedback received"
        }
      ],
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `task_brief_checklists` table (checklist data)

**Frontend Pages**:
- `/templates/{id}` - Template detail page
- Task creation forms

---

### POST `/task-brief-checklists`
**Purpose**: Create a new task brief checklist

**Request Body**:
```json
{
  "template_id": 1,
  "checklist": [
    {
      "text": "Content approved"
    },
    {
      "text": "Design finalized"
    }
  ]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Checklist created successfully",
  "data": {
    "id": 2,
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `task_brief_checklists` table (checklist data)

**Frontend Pages**:
- `/templates/{id}/edit` - Template edit page

---

## 🎨 Portfolio APIs

### GET `/portfolios`
**Purpose**: Get all portfolios

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Web Design Portfolio",
      "description": "Showcase of web design projects",
      "user_id": 1,
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z",
      "projects_count": 5
    }
  ]
}
```

**Database Operations**:
- **Read**: `portfolios` table (portfolio data)
- **Read**: `projects` table (project counts)

**Frontend Pages**:
- `/portfolios` - Portfolio list page

---

### POST `/portfolios`
**Purpose**: Create a new portfolio

**Request Body**:
```json
{
  "title": "Graphic Design Portfolio",
  "description": "Showcase of graphic design work"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Portfolio created successfully",
  "data": {
    "id": 2,
    "title": "Graphic Design Portfolio",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `portfolios` table (portfolio data)

**Frontend Pages**:
- `/portfolios/create` - Create portfolio page

---

## ⚙️ Settings APIs

### GET `/settings`
**Purpose**: Get application settings

**Response**:
```json
{
  "success": true,
  "data": {
    "app_name": "VendorConnect",
    "app_description": "Project management platform",
    "app_logo": "logo.png",
    "app_favicon": "favicon.ico",
    "timezone": "UTC",
    "date_format": "Y-m-d",
    "time_format": "H:i:s",
    "currency": "USD",
    "language": "en"
  }
}
```

**Database Operations**:
- **Read**: `settings` table (application settings)

**Frontend Pages**:
- `/settings` - Settings page
- All pages (app configuration)

---

### PUT `/settings`
**Purpose**: Update application settings

**Request Body**:
```json
{
  "app_name": "VendorConnect Updated",
  "timezone": "America/New_York",
  "currency": "EUR"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Settings updated successfully"
}
```

**Database Operations**:
- **Update**: `settings` table (settings data)

**Frontend Pages**:
- `/settings` - Settings page

---

## 🏷️ Tag Management APIs

### GET `/tags`
**Purpose**: Get all tags

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "urgent",
      "color": "#EF4444",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `tags` table (tag data)

**Frontend Pages**:
- `/tags` - Tag management page
- Task and project creation/edit forms

---

### POST `/tags`
**Purpose**: Create a new tag

**Request Body**:
```json
{
  "name": "feature",
  "color": "#3B82F6"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Tag created successfully",
  "data": {
    "id": 2,
    "name": "feature",
    "color": "#3B82F6",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `tags` table (tag data)

**Frontend Pages**:
- `/tags` - Tag management page

---

## 🔍 Search APIs

### GET `/search`
**Purpose**: Global search across tasks, projects, clients, and users

**Query Parameters**:
- `q` (required): Search query
- `type` (optional): Filter by type (tasks, projects, clients, users)
- `page` (optional): Page number for pagination

**Response**:
```json
{
  "success": true,
  "data": {
    "tasks": [
      {
        "id": 1,
        "title": "Website Design",
        "type": "task"
      }
    ],
    "projects": [
      {
        "id": 1,
        "title": "E-commerce Platform",
        "type": "project"
      }
    ],
    "clients": [
      {
        "id": 1,
        "name": "ABC Company",
        "type": "client"
      }
    ],
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "type": "user"
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task search)
- **Read**: `projects` table (project search)
- **Read**: `clients` table (client search)
- **Read**: `users` table (user search)

**Frontend Pages**:
- Global search component
- Search results page

---

## 📊 Analytics APIs

### GET `/analytics/dashboard`
**Purpose**: Get dashboard analytics

**Response**:
```json
{
  "success": true,
  "data": {
    "tasks": {
      "total": 150,
      "completed": 120,
      "pending": 25,
      "overdue": 5
    },
    "projects": {
      "total": 25,
      "active": 18,
      "completed": 7
    },
    "clients": {
      "total": 45,
      "active": 40
    },
    "users": {
      "total": 12,
      "active": 10
    },
    "revenue": {
      "total": 125000,
      "this_month": 15000
    }
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task statistics)
- **Read**: `projects` table (project statistics)
- **Read**: `clients` table (client statistics)
- **Read**: `users` table (user statistics)

**Frontend Pages**:
- `/dashboard` - Main dashboard
- Analytics dashboard

---

### GET `/analytics/tasks`
**Purpose**: Get task analytics

**Query Parameters**:
- `period` (optional): Time period (week, month, year)
- `user_id` (optional): Filter by user

**Response**:
```json
{
  "success": true,
  "data": {
    "completion_rate": 85.5,
    "average_duration": 4.2,
    "overdue_rate": 8.3,
    "by_status": [
      {
        "status": "Completed",
        "count": 120
      },
      {
        "status": "In Progress",
        "count": 25
      }
    ],
    "by_priority": [
      {
        "priority": "High",
        "count": 45
      },
      {
        "priority": "Medium",
        "count": 80
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task analytics)
- **Read**: `statuses` table (status information)
- **Read**: `priorities` table (priority information)

**Frontend Pages**:
- `/analytics` - Analytics page
- Task management dashboard

---

## 🔄 Import/Export APIs

### POST `/import/tasks`
**Purpose**: Import tasks from CSV/Excel

**Request Body**:
```
multipart/form-data with CSV/Excel file
```

**Response**:
```json
{
  "success": true,
  "message": "Tasks imported successfully",
  "data": {
    "imported": 25,
    "failed": 2,
    "errors": [
      "Row 3: Invalid status",
      "Row 7: Missing required field"
    ]
  }
}
```

**Database Operations**:
- **Create**: `tasks` table (imported tasks)

**Frontend Pages**:
- `/import` - Import page

---

### GET `/export/tasks`
**Purpose**: Export tasks to CSV/Excel

**Query Parameters**:
- `format` (optional): Export format (csv, excel)
- `filters` (optional): Apply filters

**Response**:
```
CSV/Excel file download
```

**Database Operations**:
- **Read**: `tasks` table (task data for export)

**Frontend Pages**:
- `/tasks` - Task list page (export button)

---

## 🔐 Role & Permission APIs

### GET `/roles`
**Purpose**: Get all roles

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "admin",
      "guard_name": "web",
      "permissions": [
        {
          "id": 1,
          "name": "view_tasks"
        }
      ],
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `roles` table (role data)
- **Read**: `role_has_permissions` table (role permissions)
- **Read**: `permissions` table (permission information)

**Frontend Pages**:
- `/roles` - Role management page
- User creation/edit forms

---

### POST `/roles`
**Purpose**: Create a new role

**Request Body**:
```json
{
  "name": "manager",
  "permission_ids": [1, 2, 3]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Role created successfully",
  "data": {
    "id": 2,
    "name": "manager",
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Database Operations**:
- **Create**: `roles` table (role data)
- **Create**: `role_has_permissions` table (role permissions)

**Frontend Pages**:
- `/roles/create` - Create role page

---

### GET `/permissions`
**Purpose**: Get all permissions

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "view_tasks",
      "guard_name": "web",
      "created_at": "2025-08-28T06:00:00.000000Z",
      "updated_at": "2025-08-28T06:00:00.000000Z"
    }
  ]
}
```

**Database Operations**:
- **Read**: `permissions` table (permission data)

**Frontend Pages**:
- `/permissions` - Permission management page
- Role creation/edit forms

---

## 📱 Mobile APIs

### GET `/mobile/dashboard`
**Purpose**: Get mobile-optimized dashboard data

**Response**:
```json
{
  "success": true,
  "data": {
    "quick_stats": {
      "tasks_today": 5,
      "overdue_tasks": 2,
      "notifications": 3
    },
    "recent_tasks": [
      {
        "id": 1,
        "title": "Website Design",
        "status": "In Progress",
        "priority": "High"
      }
    ],
    "upcoming_deadlines": [
      {
        "id": 2,
        "title": "Logo Design",
        "deadline": "2025-08-30"
      }
    ]
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task data)
- **Read**: `notifications` table (notification data)

**Frontend Pages**:
- Mobile dashboard
- Mobile app

---

## 🔗 Webhook APIs

### POST `/webhooks/task-created`
**Purpose**: Webhook for task creation events

**Request Body**:
```json
{
  "event": "task.created",
  "data": {
    "task_id": 1,
    "task_title": "New Task",
    "created_by": 1,
    "created_at": "2025-08-28T06:00:00.000000Z"
  }
}
```

**Response**:
```json
{
  "success": true,
  "message": "Webhook processed successfully"
}
```

**Database Operations**:
- **Read**: `tasks` table (task data)
- **Create**: `webhook_logs` table (webhook logs)

**Frontend Pages**:
- Webhook management
- Integration settings

---

## 📈 Reporting APIs

### GET `/reports/task-performance`
**Purpose**: Generate task performance report

**Query Parameters**:
- `start_date` (optional): Report start date
- `end_date` (optional): Report end date
- `user_id` (optional): Filter by user
- `format` (optional): Report format (pdf, excel)

**Response**:
```json
{
  "success": true,
  "data": {
    "report_data": {
      "total_tasks": 150,
      "completed_tasks": 120,
      "completion_rate": 80.0,
      "average_duration": 4.2,
      "user_performance": [
        {
          "user": "John Doe",
          "tasks_completed": 25,
          "completion_rate": 85.0
        }
      ]
    },
    "download_url": "https://example.com/reports/task-performance.pdf"
  }
}
```

**Database Operations**:
- **Read**: `tasks` table (task performance data)
- **Read**: `users` table (user information)
- **Read**: `time_trackers` table (time tracking data)

**Frontend Pages**:
- `/reports` - Reports page
- Task management dashboard

---

## 🔍 Implementation Notes

### Database Schema Differences
The actual database schema differs from the original documentation in several key ways:

1. **No Client-Task Relationship**: The `client_task` table does not exist in the actual database. Tasks are not directly related to clients.

2. **Task Template Integration**: Tasks can inherit data from templates, with template questions and checklists stored as JSON in the task record.

3. **Role-Based Access Control**: The API implements strict role-based filtering:
   - **Admins/Sub-Admins**: Can see all data
   - **Requesters**: Only see tasks they created
   - **Taskers**: Only see tasks they're assigned to

4. **Field Naming**: The actual database uses `title` fields instead of `name` fields for statuses, priorities, and task types.

### Missing Endpoints
The following endpoints mentioned in the original documentation are not implemented:

1. **Client-Task Relationships**: No direct client assignment to tasks
2. **Task-Client APIs**: No endpoints for managing client-task relationships
3. **Some Analytics Endpoints**: Limited analytics implementation
4. **Webhook APIs**: Not implemented
5. **Mobile-Specific APIs**: Not implemented
6. **Import/Export APIs**: Not implemented

### Response Format Changes
The actual API uses a different response format:
- Success responses include `success`, `message`, and `data` fields
- Pagination is handled differently with separate `pagination` object
- Error responses include `success: false` and error details

## 🎯 Conclusion

This updated API documentation reflects the actual VendorConnect implementation, providing developers with:

- **Accurate endpoint coverage** based on real implementation
- **Correct request/response examples** with actual data structures
- **Real database operation mapping** showing actual table relationships
- **Role-based access control** documentation
- **Template integration** details
- **Field naming consistency** guidance

The API follows RESTful conventions and provides consistent response formats across all endpoints. All endpoints return JSON responses with a standard structure including success status, messages, and data payloads.

**Important**: This documentation has been updated to reflect the actual implementation as of August 29, 2025. For the most current information, please refer to the development team or consult the internal API testing tools.

