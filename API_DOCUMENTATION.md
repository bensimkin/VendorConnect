# VendorConnect API Documentation

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your_token}
```

## Endpoints

### Authentication

#### Login
```
POST /auth/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
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
            "photo": "path/to/photo.jpg",
            "status": 1
        },
        "permissions": ["view_tasks", "create_tasks"],
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### Logout
```
POST /auth/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

#### Forgot Password
```
POST /auth/forgot-password
```

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

#### Reset Password
```
POST /auth/reset-password
```

**Request Body:**
```json
{
    "token": "reset_token",
    "email": "user@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Tasks

#### Get All Tasks
```
GET /tasks
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status_id` (optional): Filter by status
- `priority_id` (optional): Filter by priority
- `user_id` (optional): Filter by assigned user
- `client_id` (optional): Filter by client
- `search` (optional): Search in title and description
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort order (asc/desc, default: desc)

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Tasks retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Task Title",
            "description": "Task description",
            "status_id": 1,
            "priority_id": 1,
            "project_id": 1,
            "start_date": "2024-01-01",
            "end_date": "2024-01-31",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z",
            "users": [...],
            "clients": [...],
            "status": {...},
            "priority": {...},
            "project": {...},
            "tags": [...]
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

#### Create Task
```
POST /tasks
```

**Request Body:**
```json
{
    "title": "New Task",
    "description": "Task description",
    "status_id": 1,
    "priority_id": 1,
    "project_id": 1,
    "user_ids": [1, 2, 3],
    "client_ids": [1, 2],
    "tag_ids": [1, 2],
    "start_date": "2024-01-01",
    "end_date": "2024-01-31"
}
```

#### Get Task
```
GET /tasks/{id}
```

#### Update Task
```
PUT /tasks/{id}
```

**Request Body:** (same as create, but all fields optional)

#### Delete Task
```
DELETE /tasks/{id}
```

#### Delete Multiple Tasks
```
DELETE /tasks
```

**Request Body:**
```json
{
    "task_ids": [1, 2, 3]
}
```

#### Update Task Status
```
PUT /tasks/{id}/status
```

**Request Body:**
```json
{
    "status_id": 2
}
```

#### Update Task Deadline
```
PUT /tasks/{id}/deadline
```

**Request Body:**
```json
{
    "end_date": "2024-02-15"
}
```

#### Get Task Information
```
GET /tasks/{id}/information
```

### Users

#### Get All Users
```
GET /users
```

#### Create User
```
POST /users
```

#### Get User
```
GET /users/{id}
```

#### Update User
```
PUT /users/{id}
```

#### Delete User
```
DELETE /users/{id}
```

### Clients

#### Get All Clients
```
GET /clients
```

#### Create Client
```
POST /clients
```

#### Get Client
```
GET /clients/{id}
```

#### Update Client
```
PUT /clients/{id}
```

#### Delete Client
```
DELETE /clients/{id}
```

### Statuses

#### Get All Statuses
```
GET /statuses
```

#### Create Status
```
POST /statuses
```

#### Get Status
```
GET /statuses/{id}
```

#### Update Status
```
PUT /statuses/{id}
```

#### Delete Status
```
DELETE /statuses/{id}
```

### Priorities

#### Get All Priorities
```
GET /priorities
```

#### Create Priority
```
POST /priorities
```

#### Get Priority
```
GET /priorities/{id}
```

#### Update Priority
```
PUT /priorities/{id}
```

#### Delete Priority
```
DELETE /priorities/{id}
```

### Tags

#### Get All Tags
```
GET /tags
```

#### Create Tag
```
POST /tags
```

#### Get Tag
```
GET /tags/{id}
```

#### Update Tag
```
PUT /tags/{id}
```

#### Delete Tag
```
DELETE /tags/{id}
```

### Task Types

#### Get All Task Types
```
GET /task-types
```

#### Create Task Type
```
POST /task-types
```

#### Get Task Type
```
GET /task-types/{id}
```

#### Update Task Type
```
PUT /task-types/{id}
```

#### Delete Task Type
```
DELETE /task-types/{id}
```

### User Roles

#### Get All User Roles
```
GET /user-roles
```

#### Create User Role
```
POST /user-roles
```

#### Get User Role
```
GET /user-roles/{id}
```

#### Update User Role
```
PUT /user-roles/{id}
```

#### Delete User Role
```
DELETE /user-roles/{id}
```

### Task Brief Templates

#### Get All Task Brief Templates
```
GET /task-brief-templates
```

#### Create Task Brief Template
```
POST /task-brief-templates
```

#### Get Task Brief Template
```
GET /task-brief-templates/{id}
```

#### Update Task Brief Template
```
PUT /task-brief-templates/{id}
```

#### Delete Task Brief Template
```
DELETE /task-brief-templates/{id}
```

### Task Brief Questions

#### Get All Task Brief Questions
```
GET /task-brief-questions
```

#### Create Task Brief Question
```
POST /task-brief-questions
```

#### Get Task Brief Question
```
GET /task-brief-questions/{id}
```

#### Update Task Brief Question
```
PUT /task-brief-questions/{id}
```

#### Delete Task Brief Question
```
DELETE /task-brief-questions/{id}
```

### Task Brief Checklists

#### Get All Task Brief Checklists
```
GET /task-brief-checklists
```

#### Create Task Brief Checklist
```
POST /task-brief-checklists
```

#### Get Task Brief Checklist
```
GET /task-brief-checklists/{id}
```

#### Update Task Brief Checklist
```
PUT /task-brief-checklists/{id}
```

#### Delete Task Brief Checklist
```
DELETE /task-brief-checklists/{id}
```

### Notifications

#### Get All Notifications
```
GET /notifications
```

#### Mark Notification as Read
```
PUT /notifications/{id}/read
```

#### Mark All Notifications as Read
```
PUT /notifications/read-all
```

### Profile

#### Get Profile
```
GET /profile/{id}
```

#### Update Profile
```
PUT /profile/{id}
```

#### Update Profile Photo
```
PUT /profile/{id}/photo
```

**Request:** Multipart form data with photo file

### Dashboard

#### Get Dashboard Data
```
GET /dashboard
```

## Error Responses

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Testing the API

You can test the API using tools like:
- Postman
- Insomnia
- curl
- Thunder Client (VS Code extension)

### Example curl commands:

**Login:**
```bash
curl -X POST http://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

**Get Tasks:**
```bash
curl -X GET http://your-domain.com/api/v1/tasks \
  -H "Authorization: Bearer {your_token}"
```

**Create Task:**
```bash
curl -X POST http://your-domain.com/api/v1/tasks \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{"title":"New Task","description":"Description","status_id":1,"priority_id":1,"user_ids":[1]}'
```
