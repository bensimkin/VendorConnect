# COMPREHENSIVE FRONTEND PAGE MISMATCHES

## Overview
This document contains ALL mismatches found between API responses and frontend interfaces across every page in the VendorConnect frontend application.

## 1. TASKS PAGES

### `/tasks/page.tsx` (Task List)
**API Endpoint:** `GET /api/v1/tasks`
**MISMATCHES:**
- ✅ **FIXED**: `status.name` → `status.title`
- ✅ **FIXED**: `priority.name` → `priority.title`
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `users` array expects `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: `end_date` field (matches API)

### `/tasks/[id]/page.tsx` (Task Detail)
**API Endpoints:** 
- `GET /api/v1/tasks/{id}` (main task data)
- `GET /api/v1/tasks/{id}/question-answers` (question answers)
- `GET /api/v1/tasks/{id}/checklist-answers` (checklist answers)
- `GET /api/v1/tasks/{id}/checklist-status` (checklist status)
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `question_answers` structure mismatch (API has complex structure)
- ❌ **UNFIXED**: `checklist_answers` structure mismatch (API has complex structure)

### `/tasks/[id]/edit/page.tsx` (Task Edit)
**API Endpoints:**
- `GET /api/v1/tasks/{id}` (load task data)
- `GET /api/v1/statuses` (status dropdown)
- `GET /api/v1/priorities` (priority dropdown)
- `GET /api/v1/projects` (project dropdown)
- `GET /api/v1/clients` (client dropdown)
- `GET /api/v1/users` (user dropdown)
- `GET /api/v1/task-types` (task type dropdown)
- `PUT /api/v1/tasks/{id}` (update task)
**MISMATCHES:**
- ✅ **FIXED**: Dropdown value type mismatch (empty string vs number)
- ✅ **FIXED**: `status.title`, `priority.title`, `project.title` (matches API)
- ❌ **UNFIXED**: `close_deadline` type mismatch (API returns number, frontend expects boolean)
- ❌ **UNFIXED**: `client` expects `name` field but API has `first_name` + `last_name`

### `/tasks/new/page.tsx` (New Task)
**API Endpoints:**
- `GET /api/v1/statuses` (status dropdown)
- `GET /api/v1/priorities` (priority dropdown)
- `GET /api/v1/task-types` (task type dropdown)
- `GET /api/v1/clients` (client dropdown)
- `GET /api/v1/users` (user dropdown)
- `GET /api/v1/projects` (project dropdown)
- `POST /api/v1/tasks` (create task)
**MISMATCHES:**
- ✅ **FIXED**: `status.title`, `priority.title`, `task_type.task_type` (matches API)
- ❌ **UNFIXED**: `client` expects `name` field but API has `first_name` + `last_name`

## 2. PROJECTS PAGES

### `/projects/page.tsx` (Project List)
**API Endpoint:** `GET /api/v1/projects`
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `status.title` (matches API)
- ❌ **UNFIXED**: `users` array expects `first_name` + `last_name` (matches API)

### `/projects/[id]/page.tsx` (Project Detail)
**API Endpoint:** `GET /api/v1/projects/{id}`
**MISMATCHES:**
- ✅ **FIXED**: `status.title` (matches API)
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `tasks` array expects `status.title` (matches API)

### `/projects/[id]/edit/page.tsx` (Project Edit)
**API Endpoints:**
- `GET /api/v1/projects/{id}` (load project data)
- `GET /api/v1/clients` (client dropdown)
- `GET /api/v1/statuses` (status dropdown)
- `PUT /api/v1/projects/{id}` (update project)
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `status.title` (matches API)

### `/projects/new/page.tsx` (New Project)
**API Endpoints:**
- `GET /api/v1/clients` (client dropdown)
- `POST /api/v1/projects` (create project)
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`

## 3. CLIENTS PAGES

### `/clients/page.tsx` (Client List)
**API Endpoint:** `GET /api/v1/clients`
**MISMATCHES:**
- ✅ **FIXED**: API response parsing (`response.data.data || []`)
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ❌ **UNFIXED**: Uses `getClientDisplayName()` helper but interface still expects `name`

### `/clients/[id]/page.tsx` (Client Detail)
**API Endpoints:**
- `GET /api/v1/clients/{id}` (client data)
- `GET /api/v1/clients/{id}/projects` (client projects)
- `GET /api/v1/clients/{id}/tasks` (client tasks)
**MISMATCHES:**
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ✅ **FIXED**: `Project.status.title` (matches API)
- ✅ **FIXED**: `Task.status.title` and `Task.priority.title` (matches API)

### `/clients/[id]/edit/page.tsx` (Client Edit)
**API Endpoints:**
- `GET /api/v1/clients/{id}` (load client data)
- `PUT /api/v1/clients/{id}` (update client)
**MISMATCHES:**
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ❌ **UNFIXED**: Form expects `name` field but should use `first_name` + `last_name`

### `/clients/new/page.tsx` (New Client)
**API Endpoint:** `POST /api/v1/clients`
**MISMATCHES:**
- ❌ **UNFIXED**: Form expects `name` field but API expects `first_name` + `last_name`

## 4. USERS PAGES

### `/users/page.tsx` (User List)
**API Endpoint:** `GET /api/v1/users`
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: `roles` array structure (API may have different structure)

### `/users/[id]/page.tsx` (User Detail)
**API Endpoint:** `GET /api/v1/users/{id}`
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ✅ **FIXED**: `roles` array has `name` field (matches API)
- ❌ **UNFIXED**: `permissions` array structure (API may have different structure)

### `/users/[id]/edit/page.tsx` (User Edit)
**API Endpoints:**
- `GET /api/v1/users/{id}` (load user data)
- `GET /api/v1/roles` (roles dropdown)
- `PUT /api/v1/users/{id}` (update user)
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ✅ **FIXED**: `roles` array has `name` field (matches API)
- ❌ **UNFIXED**: Form expects `role_ids` but API may expect different field name

### `/users/new/page.tsx` (New User)
**API Endpoint:** `POST /api/v1/users`
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: Form expects `role` field but API may expect `role_ids` array

## 5. PORTFOLIO PAGES

### `/portfolio/page.tsx` (Portfolio List)
**API Endpoint:** `GET /api/v1/portfolios`
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/[id]/page.tsx` (Portfolio Detail)
**API Endpoint:** `GET /api/v1/portfolios/{id}`
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/[id]/edit/page.tsx` (Portfolio Edit)
**API Endpoints:**
- `GET /api/v1/portfolios/{id}` (load portfolio data)
- `GET /api/v1/clients` (client dropdown)
- `GET /api/v1/projects` (project dropdown)
- `GET /api/v1/tasks` (task dropdown)
- `GET /api/v1/task-types` (task type dropdown)
- `PUT /api/v1/portfolios/{id}` (update portfolio)
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/new/page.tsx` (New Portfolio)
**API Endpoints:**
- `GET /api/v1/clients` (client dropdown)
- `GET /api/v1/tasks` (task dropdown)
- `GET /api/v1/projects` (project dropdown)
- `GET /api/v1/task-types` (task type dropdown)
- `POST /api/v1/portfolios` (create portfolio)
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

## 6. TEMPLATES PAGES

### `/templates/page.tsx` (Template List)
**API Endpoint:** `GET /api/v1/task-brief-templates`
**MISMATCHES:**
- ✅ **FIXED**: `task_type.task_type` (matches API)

### `/templates/[id]/page.tsx` (Template Detail)
**API Endpoint:** N/A (page doesn't exist)
**MISMATCHES:**
- ❌ **UNFIXED**: Page doesn't exist (only edit page exists)

### `/templates/[id]/edit/page.tsx` (Template Edit)
**API Endpoints:**
- `GET /api/v1/task-brief-templates/{id}` (load template data)
- `GET /api/v1/task-types` (task type dropdown)
- `GET /api/v1/task-brief-questions?template_id={id}` (template questions)
- `GET /api/v1/task-brief-checklists?template_id={id}` (template checklists)
- `PUT /api/v1/task-brief-templates/{id}` (update template)
- `POST /api/v1/task-brief-questions` (create question)
- `POST /api/v1/task-brief-checklists` (create checklist)
**MISMATCHES:**
- ✅ **FIXED**: `task_type.task_type` (matches API)
- ✅ **FIXED**: `Question` interface uses `task_brief_templates_id` (matches API)
- ✅ **FIXED**: `ChecklistItem` interface uses `task_brief_templates_id` (matches API)

### `/templates/new/page.tsx` (New Template)
**API Endpoints:**
- `GET /api/v1/task-types` (task type dropdown)
- `POST /api/v1/task-brief-templates` (create template)
**MISMATCHES:**
- ✅ **FIXED**: `task_type.task_type` (matches API)

## 7. DASHBOARD PAGES

### `/dashboard/page.tsx` (Main Dashboard)
**API Endpoint:** `GET /api/v1/dashboard`
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

### `/dashboard/tasker/page.tsx` (Tasker Dashboard)
**API Endpoint:** `GET /api/v1/dashboard/tasker`
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

### `/dashboard/requester/page.tsx` (Requester Dashboard)
**API Endpoint:** `GET /api/v1/dashboard/requester`
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

## 8. PROJECT MANAGEMENT PAGE

### `/project-management/page.tsx`
**API Endpoint:** `GET /api/v1/project-management`
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

## 9. TASK TYPES PAGE

### `/task-types/page.tsx`
**API Endpoints:**
- `GET /api/v1/task-types` (list task types)
- `POST /api/v1/task-types` (create task type)
- `PUT /api/v1/task-types/{id}` (update task type)
- `DELETE /api/v1/task-types/{id}` (delete task type)
**MISMATCHES:**
- ✅ **FIXED**: `task_type` field (matches API)

## 10. SETTINGS PAGE

### `/settings/page.tsx`
**API Endpoints:**
- `PUT /api/v1/user/profile` (update profile)
- `POST /api/v1/user/change-password` (change password)
- `PUT /api/v1/user/notifications` (update notifications)
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: Notification settings structure (API may have different field names)

## SUMMARY OF CRITICAL ISSUES

### 1. CLIENT NAME FIELD MISMATCH (MOST CRITICAL)
**Affects:** ALL pages that display client information
- **API**: `first_name` + `last_name`
- **Frontend**: Expects `name` field
- **Impact**: Shows "Unnamed Client" everywhere

### 2. USER NAME FIELD MISMATCH
**Affects:** Portfolio pages, user-related displays
- **API**: `first_name` + `last_name`
- **Frontend**: Expects `name` field
- **Impact**: Shows undefined/empty names

### 3. ROLE FIELD MISMATCHES
**Affects:** User pages, role assignments
- **API**: Uses `roles` array with `name` field (matches frontend)
- **Frontend**: Expects `roles` array with `name` field
- **Impact**: ✅ **FIXED** - No mismatch found

### 4. DATA TYPE MISMATCHES
**Affects:** Task edit page
- **API**: `close_deadline` as number (1/0)
- **Frontend**: Expects boolean
- **Impact**: Form validation issues

### 5. STRUCTURE MISMATCHES
**Affects:** Task detail page
- **API**: Complex `question_answers` and `checklist_answers` structures
- **Frontend**: Expects simple structures
- **Impact**: Data not displaying correctly

### 6. TEMPLATE FIELD MISMATCHES
**Affects:** Template edit page
- **API**: Uses `task_brief_templates_id` (matches frontend)
- **Frontend**: Expects `task_brief_templates_id`
- **Impact**: ✅ **FIXED** - No mismatch found

### 7. WORKSPACE_ID ISSUE
**Affects:** All API responses
- **API**: Still includes `workspace_id: 1`
- **System**: Single tenant (should not appear)
- **Impact**: Unnecessary data transfer

## RECOMMENDATIONS

### IMMEDIATE FIXES NEEDED:
1. **Fix Client Name Fields**: Update all client interfaces to use `first_name` + `last_name`
2. **Fix User Name Fields**: Update all user interfaces to use `first_name` + `last_name`
3. **Fix Data Types**: Update `close_deadline` handling in frontend
4. **Fix Answer Structures**: Update `question_answers` and `checklist_answers` interfaces
5. **Remove workspace_id**: Clean up API responses for single tenancy

### PAGES WITH MOST MISMATCHES:
1. **Task Edit Page** - Multiple field mismatches
2. **Client Pages** - Name field mismatches throughout
3. **Portfolio Pages** - Client and user name mismatches
4. **Project Pages** - Client name mismatches

### PAGES WITH FEWEST MISMATCHES:
1. **Dashboard Pages** - Mostly fixed
2. **Template Pages** - Mostly correct
3. **User List Page** - Mostly correct
