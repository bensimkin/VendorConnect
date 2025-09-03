# VendorConnect Frontend API Documentation

## Overview
This document provides comprehensive documentation of all frontend pages in the VendorConnect application, including their API calls, data usage, and variable mappings.

**Base URL**: Defined in the `NEXT_PUBLIC_API_URL` environment variable (e.g., `https://example.com/api/v1`)
**Frontend Framework**: Next.js 14 with TypeScript
**State Management**: Zustand (auth-store)
**UI Library**: Custom components with Lucide React icons

---

## 🔐 Authentication & Authorization

### Login Page (`/login`)
**File**: `src/app/login/page.tsx`

**API Calls**:
- `POST /auth/login` - User authentication

**Data Used**:
- `email` - User email input
- `password` - User password input
- `token` - Stored in localStorage as 'auth_token'
- `user` - Stored in localStorage as 'user' (JSON string)

**Variables**:
- `formData` - Login form state
- `loading` - Loading state during authentication
- `error` - Error message display

---

## 📊 Dashboard Pages

### Main Dashboard (`/dashboard`)
**File**: `src/app/dashboard/page.tsx`

**API Calls**:
- `GET /dashboard` - Fetch dashboard statistics and data

**Data Used**:
```typescript
interface DashboardData {
  overview: {
    total_tasks: number;
    total_users: number;
    total_clients: number;
    total_projects: number;
  };
  task_statistics: {
    by_status: Record<string, number>;
    completed_this_week: number;
    overdue: number;
  };
  recent_tasks: Array<{
    id: number;
    title: string;
    description?: string;
    note?: string;
    deliverable_quantity?: number;
    status?: { title: string };
    priority?: { title: string };
    project?: { title: string };
    template?: {
      id: number;
      title: string;
      standard_brief?: string;
      description?: string;
      deliverable_quantity?: number;
    };
    created_at: string;
  }>;
  user_activity: any[];
  task_trend: Array<{
    date: string;
    completed_tasks: number;
  }>;
  project_management?: Array<{
    id: number;
    title: string;
    status?: { id: number; title: string };
    clients?: Array<{ id: number; name: string }>;
    total_tasks?: number;
    active_tasks?: number;
    overdue_tasks?: number;
    completed_this_week_tasks?: number;
    updated_at: string;
  }>;
  statuses?: Array<{ id: number; title: string }>;
}
```

**Variables**:
- `data` - Main dashboard data state
- `loading` - Loading state
- `taskStats` - Calculated task statistics
- `taskCompletionData` - Chart data for task completion
- `taskTrendData` - Chart data for task trends

**Features**:
- Role-based redirects (Tasker → `/dashboard/tasker`, Requester → `/dashboard/requester`)
- Real-time statistics display
- Interactive charts (Line, Bar, Doughnut)
- Project management overview
- Recent tasks list
- Active users list

---

## 📋 Task Management Pages

### Tasks List (`/tasks`)
**File**: `src/app/tasks/page.tsx`

**API Calls**:
- `GET /tasks` - Fetch all tasks with optional search parameters

**Query Parameters**:
- `search` - Search term for task title/description
- `page` - Pagination page number
- `per_page` - Items per page

**Data Used**:
```typescript
interface Task {
  id: number;
  title: string;
  description?: string;
  note?: string;
  deliverable_quantity?: number;
  status?: { id: number; title: string };
  priority?: { id: number; title: string };
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
  }>;
  project?: {
    id: number;
    title: string;
    description?: string;
    admin_id?: number | null;
    workspace_id?: number;
    status_id?: number;
    priority_id?: number | null;
    budget?: number | null;
    start_date?: string;
    end_date?: string;
    created_by?: number;
    is_favorite?: number;
    task_accessibility?: string;
    note?: string | null;
    created_at?: string;
    updated_at?: string;
  };
  template?: {
    id: number;
    title: string;
    standard_brief?: string;
    description?: string;
    deliverable_quantity?: number;
  };
  start_date?: string;
  end_date?: string;
  created_at: string;
  tags?: Array<{ id: number; name: string }>;
}
```

**Variables**:
- `tasks` - Array of task objects
- `loading` - Loading state
- `searchTerm` - Search input value
- `selectedStatus` - Status filter selection
- `filteredTasks` - Client-side filtered tasks

**Features**:
- Real-time search with 500ms debounce
- Status-based filtering
- Task card grid layout
- Click navigation to task details
- Template information display
- User assignment display

### Task Detail (`/tasks/[id]`)
**File**: `src/app/tasks/[id]/page.tsx`

**API Calls**:
- `GET /tasks/{id}` - Fetch task details
- `GET /statuses` - Fetch available statuses
- `GET /priorities` - Fetch available priorities
- `GET /tasks/{id}/question-answers` - Fetch template question answers
- `GET /tasks/{id}/checklist-status` - Fetch checklist completion status
- `POST /tasks/{id}/question-answer` - Update question answer
- `POST /tasks/{id}/checklist-status` - Update checklist item status
- `POST /tasks/{id}/messages` - Add comment
- `PUT /tasks/{id}/status` - Update task status
- `PUT /tasks/{id}/priority` - Update task priority
- `POST /tasks/{taskId}/deliverables` - Create deliverable
- `POST /tasks/{taskId}/deliverables/{deliverableId}/complete` - Complete deliverable
- `POST /tasks/{taskId}/deliverables/{deliverableId}/media` - Upload deliverable media

**Data Used**:
```typescript
interface TaskDetail {
  id: number;
  title: string;
  description?: string;
  status?: { id: number; name: string };
  priority?: { id: number; name: string };
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
    email: string;
  }>;
  project?: { id: number; title: string };
  start_date?: string;
  end_date?: string;
  close_deadline?: boolean;
  created_at: string;
  updated_at: string;
  tags?: Array<{ id: number; name: string }>;
  note?: string;
  deliverable_quantity?: number;
  template_id?: number;
  template_questions?: any[];
  template_checklist?: any[];
  template_standard_brief?: string;
  template_description?: string;
  template_deliverable_quantity?: number;
  template?: {
    id: number;
    title: string;
    standard_brief?: string;
    description?: string;
    deliverable_quantity?: number;
  };
  question_answers?: Array<{
    id: number;
    question_id: number;
    question_answer: string;
    brief_questions: {
      id: number;
      question_text: string;
      question_type: string;
      options?: string[];
    };
  }>;
  checklist_answers?: Array<{
    id: number;
    checklist_id: number;
    completed: boolean;
    notes: string;
  }>;
  has_deliverable?: boolean;
  deliverable_title?: string;
  deliverable_description?: string;
  deliverable_type?: string;
  deliverable_completed_at?: string;
}
```

**Variables**:
- `task` - Task detail object
- `loading` - Loading state
- `comment` - New comment input
- `templateQuestions` - Template questions array
- `questionAnswers` - Question answers array
- `checklistItems` - Checklist items array
- `checklistCompleted` - Checklist completion state
- `deliverables` - Task deliverables array
- `comments` - Task comments/messages array
- `statuses` - Available statuses
- `priorities` - Available priorities

**Features**:
- Template question answering
- Checklist management
- Deliverable creation and upload
- Comment system
- Status and priority editing
- File upload support

### New Task (`/tasks/new`)
**File**: `src/app/tasks/new/page.tsx`

**API Calls**:
- `GET /task-types` - Fetch task types
- `GET /task-brief-templates` - Fetch task templates
- `GET /statuses` - Fetch available statuses
- `GET /priorities` - Fetch available priorities
- `GET /projects` - Fetch available projects
- `GET /users` - Fetch available users
- `POST /tasks` - Create new task

**Data Used**:
- Task creation form data
- Template selection data
- User assignment data
- Project association data

**Variables**:
- `formData` - Task creation form state
- `taskTypes` - Available task types
- `templates` - Available templates
- `statuses` - Available statuses
- `priorities` - Available priorities
- `projects` - Available projects
- `users` - Available users

---

## 🏗️ Project Management Pages

### Projects List (`/projects`)
**File**: `src/app/projects/page.tsx`

**API Calls**:
- `GET /projects` - Fetch all projects with optional search parameters

**Query Parameters**:
- `search` - Search term for project title/description
- `page` - Pagination page number
- `per_page` - Items per page

**Data Used**:
```typescript
interface Project {
  id: number;
  title: string;
  description?: string;
  clients?: Array<{ id: number; name: string }>;
  status?: { id: number; title: string };
  start_date?: string;
  end_date?: string;
  budget?: number;
  users?: Array<{
    id: number;
    first_name: string;
    last_name: string;
  }>;
  progress?: number;
  tasks_count?: number;
  completed_tasks?: number;
  team_members_count?: number;
}
```

**Variables**:
- `projects` - Array of project objects
- `loading` - Loading state
- `searchTerm` - Search input value
- `statusFilter` - Status filter selection
- `filteredProjects` - Client-side filtered projects

**Features**:
- Real-time search with 500ms debounce
- Status-based filtering
- Project card grid layout
- Progress visualization
- Click navigation to project details

### Project Detail (`/projects/[id]`)
**File**: `src/app/projects/[id]/page.tsx`

**API Calls**:
- `GET /projects/{id}` - Fetch project details
- `DELETE /projects/{id}` - Delete project

**Data Used**:
```typescript
interface Project {
  id: number;
  title: string;
  description?: string;
  start_date: string;
  end_date?: string;
  status_id: number;
  budget?: number;
  created_at: string;
  updated_at: string;
  status?: { id: number; title: string };
  clients?: Array<{ id: number; name: string; company?: string }>;
  users?: Array<{ id: number; first_name: string; last_name: string; email: string }>;
  tasks?: Array<{ id: number; title: string; status: { title: string } }>;
}
```

**Variables**:
- `project` - Project detail object
- `loading` - Loading state

**Features**:
- Project information display
- Client association display
- Team member display
- Task list display
- Project deletion

### New Project (`/projects/new`)
**File**: `src/app/projects/new/page.tsx`

**API Calls**:
- `GET /clients` - Fetch available clients
- `GET /statuses` - Fetch available statuses
- `GET /priorities` - Fetch available priorities
- `GET /users` - Fetch available users
- `POST /projects` - Create new project

**Data Used**:
- Project creation form data
- Client selection data
- User assignment data

**Variables**:
- `formData` - Project creation form state
- `clients` - Available clients
- `statuses` - Available statuses
- `priorities` - Available priorities
- `users` - Available users

---

## 👥 Client Management Pages

### Clients List (`/clients`)
**File**: `src/app/clients/page.tsx`

**API Calls**:
- `GET /clients` - Fetch all clients with optional search parameters
- `DELETE /clients/{id}` - Delete client
- `PUT /clients/{id}` - Update client status

**Query Parameters**:
- `search` - Search term for client name/company/email
- `per_page` - Items per page (default: 50)

**Data Used**:
```typescript
interface Client {
  id: number;
  first_name: string;
  last_name: string;
  email?: string;
  phone?: string;
  address?: string;
  company?: string;
  status?: number;
  created_at: string;
  projects_count?: number;
  active_projects?: number;
}
```

**Variables**:
- `clients` - Array of client objects
- `loading` - Loading state
- `searchTerm` - Search input value
- `filteredClients` - Client-side filtered clients

**Features**:
- Real-time search with 500ms debounce
- Role-based data filtering (admin sees sensitive data)
- Client card grid layout
- Status toggle functionality
- Client deletion
- Click navigation to client details

### Client Detail (`/clients/[id]`)
**File**: `src/app/clients/[id]/page.tsx`

**API Calls**:
- `GET /clients/{id}` - Fetch client details
- `GET /clients/{id}/projects` - Fetch client projects
- `PUT /clients/{id}` - Update client information
- `DELETE /clients/{id}` - Delete client

**Data Used**:
- Client detail information
- Associated projects
- Contact information

**Variables**:
- `client` - Client detail object
- `projects` - Client's projects array
- `loading` - Loading state

### New Client (`/clients/new`)
**File**: `src/app/clients/new/page.tsx`

**API Calls**:
- `POST /clients` - Create new client

**Data Used**:
- Client creation form data

**Variables**:
- `formData` - Client creation form state

---

## 📝 Template Management Pages

### Templates List (`/templates`)
**File**: `src/app/templates/page.tsx`

**API Calls**:
- `GET /task-brief-templates` - Fetch all templates
- `DELETE /task-brief-templates/{id}` - Delete template
- `POST /task-brief-templates` - Duplicate template

**Data Used**:
```typescript
interface Template {
  id: number;
  title: string;
  task_type_id: number;
  standard_brief?: string;
  description?: string;
  deliverable_quantity?: number;
  task_type?: TaskType;
  created_at: string;
  updated_at: string;
}

interface TaskType {
  id: number;
  task_type: string;
}
```

**Variables**:
- `templates` - Array of template objects
- `loading` - Loading state
- `searchTerm` - Search input value
- `filteredTemplates` - Client-side filtered templates

**Features**:
- Template search functionality
- Template duplication
- Template deletion
- Click navigation to template details

### Template Detail (`/templates/[id]`)
**File**: `src/app/templates/[id]/page.tsx`

**API Calls**:
- `GET /task-brief-templates/{id}` - Fetch template details
- `GET /task-brief-questions` - Fetch template questions
- `PUT /task-brief-templates/{id}` - Update template
- `DELETE /task-brief-templates/{id}` - Delete template
- `POST /task-brief-questions` - Add question to template
- `PUT /task-brief-questions/{id}` - Update question
- `DELETE /task-brief-questions/{id}` - Delete question

**Data Used**:
- Template detail information
- Template questions
- Question types and options

**Variables**:
- `template` - Template detail object
- `questions` - Template questions array
- `loading` - Loading state

### New Template (`/templates/new`)
**File**: `src/app/templates/new/page.tsx`

**API Calls**:
- `GET /task-types` - Fetch task types
- `POST /task-brief-templates` - Create new template
- `POST /task-brief-questions` - Add questions to template

**Data Used**:
- Template creation form data
- Question creation data

**Variables**:
- `formData` - Template creation form state
- `taskTypes` - Available task types
- `questions` - Template questions array

---

## 👤 User Management Pages

### Users List (`/users`)
**File**: `src/app/users/page.tsx`

**API Calls**:
- `GET /users` - Fetch all users

**Data Used**:
- User list information
- Role information
- User statistics

**Variables**:
- `users` - Array of user objects
- `loading` - Loading state

### User Detail (`/users/[id]`)
**File**: `src/app/users/[id]/page.tsx`

**API Calls**:
- `GET /users/{id}` - Fetch user details
- `PUT /users/{id}` - Update user information

**Data Used**:
- User detail information
- User's tasks and projects

**Variables**:
- `user` - User detail object
- `loading` - Loading state

---

## ⚙️ Settings Pages

### Settings (`/settings`)
**File**: `src/app/settings/page.tsx`

**API Calls**:
- `GET /user/profile` - Fetch user profile
- `PUT /user/profile` - Update user profile
- `PUT /user/password` - Change password

**Data Used**:
- User profile information
- Password change data

**Variables**:
- `profile` - User profile object
- `loading` - Loading state

---

## 🎨 Portfolio Pages

### Portfolio (`/portfolio`)
**File**: `src/app/portfolio/page.tsx`

**API Calls**:
- `GET /portfolio` - Fetch portfolio items

**Data Used**:
- Portfolio project information
- Project deliverables
- Project statistics

**Variables**:
- `portfolio` - Portfolio data object
- `loading` - Loading state

---

## 🔧 Task Types Pages

### Task Types (`/task-types`)
**File**: `src/app/task-types/page.tsx`

**API Calls**:
- `GET /task-types` - Fetch all task types
- `POST /task-types` - Create new task type
- `PUT /task-types/{id}` - Update task type
- `DELETE /task-types/{id}` - Delete task type

**Data Used**:
- Task type information
- Task type statistics

**Variables**:
- `taskTypes` - Array of task type objects
- `loading` - Loading state

---

## 🔄 Project Management Overview

### Project Management (`/project-management`)
**File**: `src/app/project-management/page.tsx`

**API Calls**:
- `GET /project-management` - Fetch project management overview

**Data Used**:
- Project overview statistics
- Project progress data
- Team performance metrics

**Variables**:
- `projectData` - Project management data object
- `loading` - Loading state

---

## 🎯 Key Features Across All Pages

### Authentication & Authorization
- **Token Management**: Automatic token injection via axios interceptors
- **Role-Based Access**: Different data visibility based on user roles
- **Auto-Logout**: Automatic logout on 401 responses
- **Error Handling**: Comprehensive error handling with toast notifications

### Data Management
- **Real-time Search**: Debounced search with 500ms delay
- **Pagination**: Server-side pagination support
- **Filtering**: Client-side and server-side filtering
- **State Management**: Local state management with React hooks

### UI/UX Features
- **Loading States**: Consistent loading indicators
- **Error States**: User-friendly error messages
- **Responsive Design**: Mobile-first responsive layouts
- **Interactive Elements**: Hover effects and transitions
- **Form Validation**: Client-side and server-side validation

### API Integration Patterns
- **Consistent Error Handling**: Centralized error handling via interceptors
- **Authentication Headers**: Automatic token injection
- **Response Formatting**: Consistent response data structure
- **Request Debouncing**: Search input debouncing
- **Optimistic Updates**: Immediate UI updates with API fallback

---

## 📊 Data Flow Summary

### Authentication Flow
1. User enters credentials on `/login`
2. `POST /auth/login` returns token and user data
3. Token stored in localStorage
4. User redirected to role-appropriate dashboard
5. All subsequent API calls include Authorization header

### Dashboard Data Flow
1. Dashboard page loads
2. `GET /dashboard` fetches comprehensive statistics
3. Data used to populate charts and statistics
4. Real-time updates via periodic API calls

### Task Management Flow
1. Tasks list loads with `GET /tasks`
2. Search and filtering via query parameters
3. Task detail loads with `GET /tasks/{id}`
4. Related data (questions, deliverables, comments) loaded separately
5. Updates via PUT/POST endpoints

### Project Management Flow
1. Projects list loads with `GET /projects`
2. Project detail loads with `GET /projects/{id}`
3. Associated tasks and team members loaded
4. Project updates via PUT endpoints

### Client Management Flow
1. Clients list loads with `GET /clients`
2. Role-based data filtering applied
3. Client detail loads with `GET /clients/{id}`
4. Associated projects loaded separately

---

## 🔧 Technical Implementation Notes

### API Client Configuration
- **Base URL**: Provided by the `NEXT_PUBLIC_API_URL` environment variable
- **Headers**: Automatic Content-Type and Authorization
- **Interceptors**: Request and response interceptors for auth and error handling
- **Error Handling**: Centralized error handling with toast notifications

### State Management
- **Local State**: React useState for component-level state
- **Global State**: Zustand for authentication state
- **Data Fetching**: useEffect hooks for API calls
- **Loading States**: Consistent loading indicators across all pages

### Security Features
- **Token Storage**: Secure localStorage token storage
- **Role-Based Access**: Different data visibility based on user roles
- **Input Validation**: Client-side and server-side validation
- **CSRF Protection**: Laravel CSRF token handling

### Performance Optimizations
- **Debounced Search**: 500ms debounce for search inputs
- **Pagination**: Server-side pagination for large datasets
- **Lazy Loading**: Component lazy loading where appropriate
- **Caching**: Browser-level caching for static assets

---

*This documentation provides a comprehensive overview of all frontend pages, their API integrations, and data usage patterns in the VendorConnect application.*
