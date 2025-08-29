# COMPREHENSIVE FRONTEND PAGE MISMATCHES

## Overview
This document contains ALL mismatches found between API responses and frontend interfaces across every page in the VendorConnect frontend application.

## 1. TASKS PAGES

### `/tasks/page.tsx` (Task List)
**API Endpoint:** `GET /api/v1/tasks`
**SQL Queries:**
```sql
-- Main task query with relationships
SELECT * FROM tasks 
LEFT JOIN task_user ON tasks.id = task_user.task_id
LEFT JOIN users ON task_user.user_id = users.id
LEFT JOIN statuses ON tasks.status_id = statuses.id
LEFT JOIN priorities ON tasks.priority_id = priorities.id
LEFT JOIN task_types ON tasks.task_type_id = task_types.id
LEFT JOIN task_brief_templates ON tasks.template_id = task_brief_templates.id
LEFT JOIN projects ON tasks.project_id = projects.id
LEFT JOIN client_task ON tasks.id = client_task.task_id
LEFT JOIN clients ON client_task.client_id = clients.id
WHERE tasks.created_by = ? -- Role-based filtering for requesters
-- OR WHERE users.id = ? -- Role-based filtering for taskers
ORDER BY tasks.created_at DESC
LIMIT 15 OFFSET 0;

-- Role-based filtering queries
SELECT * FROM tasks WHERE created_by = ?; -- For requesters
SELECT * FROM tasks WHERE EXISTS (SELECT 1 FROM task_user WHERE task_id = tasks.id AND user_id = ?); -- For taskers
```
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
**SQL Queries:**
```sql
-- Main task data
SELECT * FROM tasks 
LEFT JOIN statuses ON tasks.status_id = statuses.id
LEFT JOIN priorities ON tasks.priority_id = priorities.id
LEFT JOIN task_types ON tasks.task_type_id = task_types.id
LEFT JOIN projects ON tasks.project_id = projects.id
WHERE tasks.id = ?;

-- Task relationships
SELECT * FROM task_user WHERE task_id = ?;
SELECT * FROM users WHERE id IN (SELECT user_id FROM task_user WHERE task_id = ?);
SELECT * FROM client_task WHERE task_id = ?;
SELECT * FROM clients WHERE id IN (SELECT client_id FROM client_task WHERE task_id = ?);

-- Question answers
SELECT * FROM question_answered WHERE task_id = ?;

-- Checklist answers  
SELECT * FROM checklist_answered WHERE task_id = ?;

-- Checklist status
SELECT checklist_id, status FROM checklist_answered WHERE task_id = ?;
```
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
**SQL Queries:**
```sql
-- Load task data
SELECT * FROM tasks WHERE id = ?;

-- Dropdown data
SELECT id, title FROM statuses ORDER BY created_at DESC;
SELECT id, title FROM priorities ORDER BY created_at DESC;
SELECT id, title FROM projects ORDER BY created_at DESC;
SELECT id, first_name, last_name FROM clients ORDER BY created_at DESC;
SELECT id, first_name, last_name FROM users ORDER BY created_at DESC;
SELECT id, task_type FROM task_types ORDER BY created_at DESC;

-- Update task
UPDATE tasks SET 
    title = ?, description = ?, status_id = ?, priority_id = ?, 
    project_id = ?, end_date = ?, close_deadline = ?, note = ?, 
    deliverable_quantity = ?, updated_at = NOW()
WHERE id = ?;

-- Update task relationships
DELETE FROM task_user WHERE task_id = ?;
INSERT INTO task_user (task_id, user_id) VALUES (?, ?), (?, ?), ...;

DELETE FROM client_task WHERE task_id = ?;
INSERT INTO client_task (task_id, client_id) VALUES (?, ?), (?, ?), ...;
```
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
**SQL Queries:**
```sql
-- Dropdown data
SELECT id, title FROM statuses ORDER BY created_at DESC;
SELECT id, title FROM priorities ORDER BY created_at DESC;
SELECT id, task_type FROM task_types ORDER BY created_at DESC;
SELECT id, first_name, last_name FROM clients ORDER BY created_at DESC;
SELECT id, first_name, last_name FROM users ORDER BY created_at DESC;
SELECT id, title FROM projects ORDER BY created_at DESC;

-- Create task
INSERT INTO tasks (
    title, description, status_id, priority_id, task_type_id, 
    project_id, start_date, end_date, close_deadline, 
    standard_brief, created_by, note, deliverable_quantity,
    template_questions, template_checklist, template_standard_brief,
    template_description, template_deliverable_quantity, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());

-- Create task relationships
INSERT INTO task_user (task_id, user_id) VALUES (?, ?), (?, ?), ...;
INSERT INTO client_task (task_id, client_id) VALUES (?, ?), (?, ?), ...;
```
**MISMATCHES:**
- ✅ **FIXED**: `status.title`, `priority.title`, `task_type.task_type` (matches API)
- ❌ **UNFIXED**: `client` expects `name` field but API has `first_name` + `last_name`

## 2. PROJECTS PAGES

### `/projects/page.tsx` (Project List)
**API Endpoint:** `GET /api/v1/projects`
**SQL Queries:**
```sql
-- Main project query with relationships
SELECT * FROM projects 
LEFT JOIN project_user ON projects.id = project_user.project_id
LEFT JOIN users ON project_user.user_id = users.id
LEFT JOIN project_client ON projects.id = project_client.project_id
LEFT JOIN clients ON project_client.client_id = clients.id
LEFT JOIN tasks ON projects.id = tasks.project_id
ORDER BY projects.created_at DESC
LIMIT 15 OFFSET 0;

-- Task counts
SELECT project_id, COUNT(*) as tasks_count FROM tasks GROUP BY project_id;
SELECT project_id, COUNT(*) as completed_tasks FROM tasks WHERE status_id = 17 GROUP BY project_id;
SELECT project_id, COUNT(DISTINCT user_id) as team_members_count FROM project_user GROUP BY project_id;

-- Overdue tasks
SELECT project_id, COUNT(*) as overdue_tasks 
FROM tasks 
WHERE end_date < NOW() AND status_id != 17 
GROUP BY project_id;
```
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `status.title` (matches API)
- ❌ **UNFIXED**: `users` array expects `first_name` + `last_name` (matches API)

### `/projects/[id]/page.tsx` (Project Detail)
**API Endpoint:** `GET /api/v1/projects/{id}`
**SQL Queries:**
```sql
-- Main project data
SELECT * FROM projects WHERE id = ?;

-- Project relationships
SELECT * FROM project_user WHERE project_id = ?;
SELECT * FROM users WHERE id IN (SELECT user_id FROM project_user WHERE project_id = ?);
SELECT * FROM project_client WHERE project_id = ?;
SELECT * FROM clients WHERE id IN (SELECT client_id FROM project_client WHERE project_id = ?);

-- Project tasks
SELECT * FROM tasks 
LEFT JOIN statuses ON tasks.status_id = statuses.id
LEFT JOIN priorities ON tasks.priority_id = priorities.id
WHERE tasks.project_id = ?;
```
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
**SQL Queries:**
```sql
-- Load project data
SELECT * FROM projects WHERE id = ?;

-- Dropdown data
SELECT id, first_name, last_name FROM clients ORDER BY created_at DESC;
SELECT id, title FROM statuses ORDER BY created_at DESC;

-- Update project
UPDATE projects SET 
    title = ?, description = ?, status_id = ?, updated_at = NOW()
WHERE id = ?;

-- Update project relationships
DELETE FROM project_client WHERE project_id = ?;
INSERT INTO project_client (project_id, client_id) VALUES (?, ?), (?, ?), ...;
```
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `status.title` (matches API)

### `/projects/new/page.tsx` (New Project)
**API Endpoints:**
- `GET /api/v1/clients` (client dropdown)
- `POST /api/v1/projects` (create project)
**SQL Queries:**
```sql
-- Dropdown data
SELECT id, first_name, last_name FROM clients ORDER BY created_at DESC;

-- Create project
INSERT INTO projects (
    title, description, status_id, created_by, created_at, updated_at
) VALUES (?, ?, ?, ?, NOW(), NOW());

-- Create project relationships
INSERT INTO project_client (project_id, client_id) VALUES (?, ?), (?, ?), ...;
```
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`

## 3. CLIENTS PAGES

### `/clients/page.tsx` (Client List)
**API Endpoint:** `GET /api/v1/clients`
**SQL Queries:**
```sql
-- Main client query
SELECT * FROM clients 
ORDER BY clients.created_at DESC
LIMIT 15 OFFSET 0;

-- Client counts
SELECT client_id, COUNT(*) as projects_count FROM projects GROUP BY client_id;
SELECT client_id, COUNT(*) as tasks_count FROM tasks GROUP BY client_id;
SELECT client_id, COUNT(*) as active_projects FROM projects WHERE status_id = 20 GROUP BY client_id;

-- Role-based data filtering (removes sensitive data for non-admins)
-- Email, phone, address, city, state, country, zip, dob, notes are excluded
```
**MISMATCHES:**
- ✅ **FIXED**: API response parsing (`response.data.data || []`)
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ❌ **UNFIXED**: Uses `getClientDisplayName()` helper but interface still expects `name`

### `/clients/[id]/page.tsx` (Client Detail)
**API Endpoints:**
- `GET /api/v1/clients/{id}` (client data)
- `GET /api/v1/clients/{id}/projects` (client projects)
- `GET /api/v1/clients/{id}/tasks` (client tasks)
**SQL Queries:**
```sql
-- Client data
SELECT * FROM clients WHERE id = ?;

-- Client projects
SELECT * FROM projects 
LEFT JOIN statuses ON projects.status_id = statuses.id
WHERE projects.client_id = ?;

-- Client tasks (multiple approaches)
SELECT * FROM tasks 
LEFT JOIN statuses ON tasks.status_id = statuses.id
LEFT JOIN priorities ON tasks.priority_id = priorities.id
WHERE tasks.id IN (
    SELECT task_id FROM client_task WHERE client_id = ?
    UNION
    SELECT id FROM tasks WHERE project_id IN (
        SELECT id FROM projects WHERE client_id = ?
    )
);
```
**MISMATCHES:**
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ✅ **FIXED**: `Project.status.title` (matches API)
- ✅ **FIXED**: `Task.status.title` and `Task.priority.title` (matches API)

### `/clients/[id]/edit/page.tsx` (Client Edit)
**API Endpoints:**
- `GET /api/v1/clients/{id}` (load client data)
- `PUT /api/v1/clients/{id}` (update client)
**SQL Queries:**
```sql
-- Load client data
SELECT * FROM clients WHERE id = ?;

-- Update client
UPDATE clients SET 
    first_name = ?, last_name = ?, email = ?, phone = ?, company = ?,
    address = ?, website = ?, notes = ?, status = ?, city = ?, 
    state = ?, country = ?, zip = ?, dob = ?, updated_at = NOW()
WHERE id = ?;
```
**MISMATCHES:**
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ❌ **UNFIXED**: Form expects `name` field but should use `first_name` + `last_name`

### `/clients/new/page.tsx` (New Client)
**API Endpoint:** `POST /api/v1/clients`
**SQL Queries:**
```sql
-- Create client
INSERT INTO clients (
    first_name, last_name, email, phone, company, address, website, 
    notes, status, city, state, country, zip, dob, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
```
**MISMATCHES:**
- ❌ **UNFIXED**: Form expects `name` field but API expects `first_name` + `last_name`

## 4. USERS PAGES

### `/users/page.tsx` (User List)
**API Endpoint:** `GET /api/v1/users`
**SQL Queries:**
```sql
-- Main user query with relationships
SELECT * FROM users 
LEFT JOIN model_has_roles ON users.id = model_has_roles.model_id
LEFT JOIN roles ON model_has_roles.role_id = roles.id
WHERE users.first_name LIKE ? OR users.last_name LIKE ?
-- OR users.email LIKE ? (admin only)
ORDER BY users.created_at DESC
LIMIT 15 OFFSET 0;

-- Role-based data filtering (removes email, phone for non-admins)
```
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: `roles` array structure (API may have different structure)

### `/users/[id]/page.tsx` (User Detail)
**API Endpoint:** `GET /api/v1/users/{id}`
**SQL Queries:**
```sql
-- User data
SELECT * FROM users WHERE id = ?;

-- User roles
SELECT * FROM model_has_roles WHERE model_id = ? AND model_type = 'App\\Models\\User';
SELECT * FROM roles WHERE id IN (SELECT role_id FROM model_has_roles WHERE model_id = ?);

-- User permissions
SELECT * FROM model_has_permissions WHERE model_id = ? AND model_type = 'App\\Models\\User';
SELECT * FROM permissions WHERE id IN (SELECT permission_id FROM model_has_permissions WHERE model_id = ?);
```
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ✅ **FIXED**: `roles` array has `name` field (matches API)
- ❌ **UNFIXED**: `permissions` array structure (API may have different structure)

### `/users/[id]/edit/page.tsx` (User Edit)
**API Endpoints:**
- `GET /api/v1/users/{id}` (load user data)
- `GET /api/v1/roles` (roles dropdown)
- `PUT /api/v1/users/{id}` (update user)
**SQL Queries:**
```sql
-- Load user data
SELECT * FROM users WHERE id = ?;

-- Roles dropdown
SELECT * FROM roles ORDER BY created_at DESC;

-- Update user
UPDATE users SET 
    first_name = ?, last_name = ?, email = ?, phone = ?, 
    profile = ?, address = ?, city = ?, state = ?, country = ?, 
    zip = ?, dob = ?, doj = ?, status = ?, updated_at = NOW()
WHERE id = ?;

-- Update user roles
DELETE FROM model_has_roles WHERE model_id = ? AND model_type = 'App\\Models\\User';
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\Models\\User', ?), (?, 'App\\Models\\User', ?), ...;
```
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ✅ **FIXED**: `roles` array has `name` field (matches API)
- ❌ **UNFIXED**: Form expects `role_ids` but API may expect different field name

### `/users/new/page.tsx` (New User)
**API Endpoint:** `POST /api/v1/users`
**SQL Queries:**
```sql
-- Create user
INSERT INTO users (
    first_name, last_name, email, password, phone, profile, address, 
    city, state, country, zip, dob, doj, status, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());

-- Assign roles
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\Models\\User', ?), (?, 'App\\Models\\User', ?), ...;
```
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: Form expects `role` field but API may expect `role_ids` array

## 5. PORTFOLIO PAGES

### `/portfolio/page.tsx` (Portfolio List)
**API Endpoint:** `GET /api/v1/portfolios`
**SQL Queries:**
```sql
-- Main portfolio query with relationships
SELECT * FROM portfolios 
LEFT JOIN clients ON portfolios.client_id = clients.id
LEFT JOIN tasks ON portfolios.task_id = tasks.id
LEFT JOIN projects ON portfolios.project_id = projects.id
LEFT JOIN users ON portfolios.created_by = users.id
LEFT JOIN task_types ON portfolios.task_type_id = task_types.id
WHERE portfolios.title LIKE ? OR portfolios.description LIKE ?
ORDER BY portfolios.created_at DESC
LIMIT 15 OFFSET 0;
```
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/[id]/page.tsx` (Portfolio Detail)
**API Endpoint:** `GET /api/v1/portfolios/{id}`
**SQL Queries:**
```sql
-- Portfolio data with relationships
SELECT * FROM portfolios 
LEFT JOIN clients ON portfolios.client_id = clients.id
LEFT JOIN tasks ON portfolios.task_id = tasks.id
LEFT JOIN projects ON portfolios.project_id = projects.id
LEFT JOIN users ON portfolios.created_by = users.id
LEFT JOIN task_types ON portfolios.task_type_id = task_types.id
WHERE portfolios.id = ?;
```
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
**SQL Queries:**
```sql
-- Load portfolio data
SELECT * FROM portfolios WHERE id = ?;

-- Dropdown data
SELECT id, first_name, last_name FROM clients ORDER BY created_at DESC;
SELECT id, title FROM projects ORDER BY created_at DESC;
SELECT id, title FROM tasks ORDER BY created_at DESC;
SELECT id, task_type FROM task_types ORDER BY created_at DESC;

-- Update portfolio
UPDATE portfolios SET 
    title = ?, description = ?, client_id = ?, task_id = ?, 
    project_id = ?, task_type_id = ?, deliverable_type = ?, 
    status = ?, updated_at = NOW()
WHERE id = ?;
```
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
**SQL Queries:**
```sql
-- Dropdown data
SELECT id, first_name, last_name FROM clients ORDER BY created_at DESC;
SELECT id, title FROM tasks ORDER BY created_at DESC;
SELECT id, title FROM projects ORDER BY created_at DESC;
SELECT id, task_type FROM task_types ORDER BY created_at DESC;

-- Create portfolio
INSERT INTO portfolios (
    title, description, client_id, task_id, project_id, task_type_id,
    deliverable_type, status, created_by, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
```
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

## 6. TEMPLATES PAGES

### `/templates/page.tsx` (Template List)
**API Endpoint:** `GET /api/v1/task-brief-templates`
**SQL Queries:**
```sql
-- Template list with task type
SELECT * FROM task_brief_templates 
LEFT JOIN task_types ON task_brief_templates.task_type_id = task_types.id
ORDER BY task_brief_templates.created_at DESC
LIMIT 15 OFFSET 0;
```
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
**SQL Queries:**
```sql
-- Load template data
SELECT * FROM task_brief_templates WHERE id = ?;

-- Task type dropdown
SELECT id, task_type FROM task_types ORDER BY created_at DESC;

-- Template questions
SELECT * FROM task_brief_questions WHERE task_brief_templates_id = ?;

-- Template checklists
SELECT * FROM task_brief_checklists WHERE task_brief_templates_id = ?;

-- Update template
UPDATE task_brief_templates SET 
    title = ?, description = ?, task_type_id = ?, updated_at = NOW()
WHERE id = ?;

-- Create question
INSERT INTO task_brief_questions (
    task_brief_templates_id, question, type, required, created_at, updated_at
) VALUES (?, ?, ?, ?, NOW(), NOW());

-- Create checklist
INSERT INTO task_brief_checklists (
    task_brief_templates_id, item, required, created_at, updated_at
) VALUES (?, ?, ?, NOW(), NOW());
```
**MISMATCHES:**
- ✅ **FIXED**: `task_type.task_type` (matches API)
- ✅ **FIXED**: `Question` interface uses `task_brief_templates_id` (matches API)
- ✅ **FIXED**: `ChecklistItem` interface uses `task_brief_templates_id` (matches API)

### `/templates/new/page.tsx` (New Template)
**API Endpoints:**
- `GET /api/v1/task-types` (task type dropdown)
- `POST /api/v1/task-brief-templates` (create template)
**SQL Queries:**
```sql
-- Task type dropdown
SELECT id, task_type FROM task_types ORDER BY created_at DESC;

-- Create template
INSERT INTO task_brief_templates (
    title, description, task_type_id, created_by, created_at, updated_at
) VALUES (?, ?, ?, ?, NOW(), NOW());
```
**MISMATCHES:**
- ✅ **FIXED**: `task_type.task_type` (matches API)

## 7. DASHBOARD PAGES

### `/dashboard/page.tsx` (Main Dashboard)
**API Endpoint:** `GET /api/v1/dashboard`
**SQL Queries:**
```sql
-- Basic counts with role-based filtering
SELECT COUNT(*) as total_tasks FROM tasks WHERE created_by = ?; -- For requesters
SELECT COUNT(*) as total_tasks FROM tasks WHERE EXISTS (SELECT 1 FROM task_user WHERE task_id = tasks.id AND user_id = ?); -- For taskers
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_clients FROM clients;
SELECT COUNT(*) as total_projects FROM projects;

-- Recent tasks with relationships
SELECT * FROM tasks 
LEFT JOIN users ON tasks.created_by = users.id
LEFT JOIN statuses ON tasks.status_id = statuses.id
LEFT JOIN priorities ON tasks.priority_id = priorities.id
LEFT JOIN task_types ON tasks.task_type_id = task_types.id
WHERE tasks.created_by = ? -- Role-based filtering
ORDER BY tasks.created_at DESC
LIMIT 5;

-- Task statistics
SELECT status_id, COUNT(*) as count FROM tasks GROUP BY status_id;
SELECT priority_id, COUNT(*) as count FROM tasks GROUP BY priority_id;
```
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
**SQL Queries:**
```sql
-- List task types
SELECT * FROM task_types ORDER BY created_at DESC;

-- Create task type
INSERT INTO task_types (task_type, created_at, updated_at) VALUES (?, NOW(), NOW());

-- Update task type
UPDATE task_types SET task_type = ?, updated_at = NOW() WHERE id = ?;

-- Delete task type
DELETE FROM task_types WHERE id = ?;
```
**MISMATCHES:**
- ✅ **FIXED**: `task_type` field (matches API)

## 10. SETTINGS PAGE

### `/settings/page.tsx`
**API Endpoints:**
- `PUT /api/v1/user/profile` (update profile)
- `POST /api/v1/user/change-password` (change password)
- `PUT /api/v1/user/notifications` (update notifications)
**SQL Queries:**
```sql
-- Update user profile
UPDATE users SET 
    first_name = ?, last_name = ?, email = ?, phone = ?, 
    profile = ?, address = ?, city = ?, state = ?, country = ?, 
    zip = ?, dob = ?, updated_at = NOW()
WHERE id = ?;

-- Change password
UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?;

-- Update notification settings (if notification_settings table exists)
UPDATE notification_settings SET 
    email_notifications = ?, sms_notifications = ?, push_notifications = ?,
    updated_at = NOW()
WHERE user_id = ?;
```
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
