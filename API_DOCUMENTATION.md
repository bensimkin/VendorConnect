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
