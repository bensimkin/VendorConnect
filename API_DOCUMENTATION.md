# VendorConnect API Documentation

## Overview
This document provides comprehensive documentation for all VendorConnect API endpoints, including request/response formats, authentication, and best practices.

**Base URL**: `https://vc.themastermind.com.au/api/v1`  
**Authentication**: Bearer Token (Laravel Sanctum)  
**Content Type**: `application/json`  
**Last Updated**: September 1, 2025

---

## 🔐 Authentication & Security

### Token-Based Authentication
- **Token Generation**: JWT-like tokens via Laravel Sanctum
- **Token Expiration**: 7 days (configurable)
- **Token Refresh**: Available without re-authentication
- **Token Revocation**: Immediate on logout

### Security Features
- **Rate Limiting**: 5 login attempts per IP, 15-minute lockout
- **Encryption**: Tokens encrypted with APP_KEY
- **Account Validation**: Only active users can authenticate
- **Password Security**: Bcrypt hashing with salt

### Authentication Flow
```http
POST /api/v1/auth/login
Content-Type: application/json

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
            "roles": [{"id": 1, "name": "admin"}]
        },
        "permissions": ["view_tasks", "create_tasks", "edit_tasks"],
        "token": "1|abc123...",
        "token_type": "Bearer",
        "expires_at": "2025-09-04T06:30:00.000000Z"
    }
}
```

### Using Protected Endpoints
```http
GET /api/v1/dashboard
Authorization: Bearer 1|abc123...
Accept: application/json
```

---

## ⚠️ Important: Field Naming Standards

**CRITICAL**: This API has field naming inconsistencies due to Laravel model `$appends` attributes. Use the primary database field names for consistency:

| Model | Database Field | API Response | Use This |
|-------|----------------|--------------|----------|
| Status | `statuses.title` | `status.title` + `status.name` | `status.title` |
| Priority | `priorities.title` | `priority.title` + `priority.name` | `priority.title` |
| TaskType | `task_types.task_type` | `task_type.task_type` + `task_type.name` | `task_type.task_type` |
| Project | `projects.title` | `project.title` | `project.title` |

**Impact**: Frontend should use primary database field names to maintain consistency.

---

## 📊 Core Endpoints

### Dashboard
```http
GET /api/v1/dashboard
Authorization: Bearer {token}
```

**Response**: Dashboard statistics, recent tasks, user activity, task trends

### Users
```http
GET /api/v1/users
GET /api/v1/users/{id}
POST /api/v1/users
PUT /api/v1/users/{id}
DELETE /api/v1/users/{id}
```

### Clients
```http
GET /api/v1/clients
GET /api/v1/clients/{id}
POST /api/v1/clients
PUT /api/v1/clients/{id}
DELETE /api/v1/clients/{id}
```

### Projects
```http
GET /api/v1/projects
GET /api/v1/projects/{id}
POST /api/v1/projects
PUT /api/v1/projects/{id}
DELETE /api/v1/projects/{id}
```

### Tasks
```http
GET /api/v1/tasks
GET /api/v1/tasks/{id}
POST /api/v1/tasks
PUT /api/v1/tasks/{id}
DELETE /api/v1/tasks/{id}
```

---

## 🎯 Task Management

### Task Creation
```http
POST /api/v1/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Task Title",
    "description": "Task description",
    "project_id": 1,
    "assigned_to": 2,
    "priority_id": 3,
    "status_id": 1,
    "deadline": "2025-09-15T00:00:00.000000Z",
    "deliverable_quantity": 1
}
```

### Task Updates
```http
PUT /api/v1/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status_id": 2,
    "note": "Updated task note"
}
```

### Task Comments
```http
POST /api/v1/tasks/{id}/comments
Authorization: Bearer {token}
Content-Type: application/json

{
    "comment": "Task comment text"
}
```

---

## 🏷️ Reference Data

### Statuses
```http
GET /api/v1/statuses
```
**Response**: List of all task statuses (Active, In Progress, Completed, etc.)

### Priorities
```http
GET /api/v1/priorities
```
**Response**: List of all task priorities (Low, Medium, High, Urgent)

### Task Types
```http
GET /api/v1/task-types
```
**Response**: List of all task types (Web Design, Content Creation, etc.)

### Tags
```http
GET /api/v1/tags
```
**Response**: List of all available tags

---

## 📋 Templates & Briefs

### Task Templates
```http
GET /api/v1/templates
GET /api/v1/templates/{id}
```

### Template Questions
```http
GET /api/v1/templates/{id}/questions
```

### Task Brief Answers
```http
POST /api/v1/tasks/{id}/answers
Authorization: Bearer {token}
Content-Type: application/json

{
    "question_id": 1,
    "answer": "Answer text"
}
```

---

## 📁 Portfolio Management

### Portfolio Items
```http
GET /api/v1/portfolio
GET /api/v1/portfolio/{id}
POST /api/v1/portfolio
PUT /api/v1/portfolio/{id}
DELETE /api/v1/portfolio/{id}
```

### Portfolio by Client
```http
GET /api/v1/clients/{id}/portfolio
```

---

## 🔍 Search & Filtering

### Global Search
```http
GET /api/v1/search?q={query}
Authorization: Bearer {token}
```

**Response**: Search results across clients, projects, tasks, and portfolio items

### Filtered Endpoints
Most endpoints support filtering:
```http
GET /api/v1/tasks?status_id=1&priority_id=3&assigned_to=2
GET /api/v1/projects?client_id=1&status_id=1
```

---

## 📤 File Uploads

### Task Deliverables
```http
POST /api/v1/tasks/{id}/deliverables
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "title": "Deliverable Title",
    "description": "Deliverable description",
    "file": [file upload]
}
```

### Profile Photo
```http
POST /api/v1/profile/photo
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "photo": [image file]
}
```

---

## 🔐 Role-Based Access

### Admin Access
- Full CRUD operations on all resources
- User management and role assignment
- System configuration access

### Requester Access
- Create and manage projects/tasks
- View assigned items and portfolio
- Limited user data access

### Tasker Access
- View and update assigned tasks only
- Upload deliverables and comments
- No administrative functions

---

## 📊 Response Formats

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Error message"]
    }
}
```

### Pagination
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [...],
        "first_page_url": "...",
        "from": 1,
        "last_page": 5,
        "last_page_url": "...",
        "next_page_url": "...",
        "path": "...",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 75
    }
}
```

---

## 🚨 Error Handling

### Common HTTP Status Codes
- **200**: Success
- **201**: Created
- **400**: Bad Request (validation errors)
- **401**: Unauthorized (invalid/missing token)
- **403**: Forbidden (insufficient permissions)
- **404**: Not Found
- **422**: Validation Error
- **429**: Too Many Requests (rate limited)
- **500**: Server Error

### Validation Errors
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

## 🔧 Configuration

### Environment Variables
```env
# API Configuration
APP_URL=https://vc.themastermind.com.au
APP_ENV=production

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vendorconnect
DB_USERNAME=vendorconnect
DB_PASSWORD=your_password

# Sanctum Configuration
SANCTUM_TOKEN_EXPIRATION=10080  # 7 days in minutes
```

---

## 📞 Support & Troubleshooting

### Common Issues

1. **Token Expired**
   - Error: 401 Unauthorized
   - Solution: Refresh token or re-authenticate

2. **Rate Limited**
   - Error: 429 Too Many Requests
   - Solution: Wait 15 minutes or contact admin

3. **Permission Denied**
   - Error: 403 Forbidden
   - Solution: Check user role and permissions

4. **Validation Errors**
   - Error: 422 Unprocessable Entity
   - Solution: Check request payload and field requirements

### Getting Help
- **Documentation**: This file and related guides
- **Logs**: Check Laravel logs for detailed error information
- **Support**: Contact system administrator for assistance

---

## 📋 API Versioning

### Current Version: v1
- All endpoints use `/api/v1/` prefix
- Backward compatibility maintained within v1
- Breaking changes will be introduced in v2

### Deprecation Policy
- Deprecated endpoints will be announced 6 months in advance
- Deprecated features will continue to work for 12 months
- Migration guides will be provided for major changes

---

*This documentation is maintained as part of the VendorConnect project. For questions or updates, please contact the development team.*

