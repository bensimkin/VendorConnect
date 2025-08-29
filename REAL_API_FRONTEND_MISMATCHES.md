# REAL API vs Frontend Interface Mismatches

## Overview
This document contains the ACTUAL mismatches found between API responses and frontend interfaces, based on real API calls and code analysis.

## 1. Task API Response vs Frontend Interface

### API Response Fields (from actual API call):
```json
{
  "admin_id": null,
  "checklist_answers": [],
  "clients": [],
  "close_deadline": 1,
  "created_at": "2025-08-28T23:17:32.000000Z",
  "created_by": 1,
  "deliverable_quantity": 3,
  "deliverables": [],
  "description": "Updated description from frontend edit",
  "end_date": null,
  "id": 67,
  "is_repeating": false,
  "last_repeated_at": null,
  "messages": [],
  "note": "Test note from frontend edit - UPDATED",
  "parent_task_id": null,
  "priority": { "id": 3, "title": "High", "slug": "high", ... },
  "priority_id": 3,
  "project": { "id": 7, "title": "Summer Marketing Campaign", ... },
  "project_id": 7,
  "question_answers": [...],
  "repeat_active": false,
  "repeat_frequency": null,
  "repeat_interval": 1,
  "repeat_until": null,
  "standard_brief": null,
  "start_date": "2025-08-28",
  "status": { "id": 21, "title": "Inactive", "slug": "inactive", ... },
  "status_id": 21,
  "task_type": { "id": 7, "task_type": "Graphics", ... },
  "task_type_id": 7,
  "template": { "id": 13, "title": "Test Template", ... },
  "template_checklist": [...],
  "template_deliverable_quantity": 5,
  "template_description": "This is a test template description",
  "template_id": 13,
  "template_questions": [...],
  "template_standard_brief": "This is a test standard brief",
  "title": "Updated Test Template",
  "updated_at": "2025-08-29T00:35:43.000000Z",
  "users": []
}
```

### Frontend Interface (current):
```typescript
interface Task {
  id: number;
  title: string;
  description?: string;
  note?: string;
  deliverable_quantity?: number;
  status_id?: number;        // ✅ MATCHES API
  priority_id?: number;      // ✅ MATCHES API
  project_id?: number;       // ✅ MATCHES API
  task_type_id?: number;     // ✅ MATCHES API
  status?: { id: number; title: string; };     // ✅ MATCHES API
  priority?: { id: number; title: string; };   // ✅ MATCHES API
  project?: { id: number; title: string; };    // ✅ MATCHES API
  task_type?: { id: number; task_type: string; }; // ✅ MATCHES API
  template?: { id: number; title: string; ... };  // ✅ MATCHES API
  close_deadline?: boolean;  // ❌ MISMATCH: API returns number (1/0), frontend expects boolean
  due_date?: string;         // ❌ MISMATCH: API has 'end_date', frontend expects 'due_date'
  assigned_to?: { id: number; first_name: string; last_name: string; }; // ❌ MISMATCH: API has 'users' array, frontend expects 'assigned_to' object
  client?: { id: number; name: string; };      // ❌ MISMATCH: API has 'clients' array, frontend expects 'client' object
  question_answers?: Array<{ question_id: number; question_answer: string; }>; // ❌ MISMATCH: API has complex structure with brief_questions, answer_by, etc.
  checklist_answers?: Array<{ completed: boolean; }>; // ❌ MISMATCH: API has complex structure with item_index, notes, etc.
}
```

### MISMATCHES FOUND:

1. **close_deadline**: API returns `1` or `0` (number), frontend expects `boolean`
2. **due_date vs end_date**: API has `end_date`, frontend expects `due_date`
3. **assigned_to vs users**: API has `users` array, frontend expects `assigned_to` object
4. **client vs clients**: API has `clients` array, frontend expects `client` object
5. **checklist_answers**: API has complex structure with `item_index`, `notes`, `completed`, frontend expects simple `{completed: boolean}`

## 2. Status API Response vs Frontend Interface

### API Response Fields:
```json
{
  "admin_id": null,
  "created_at": "2025-08-24T22:51:23.000000Z",
  "id": 21,
  "slug": "inactive",
  "title": "Inactive",
  "updated_at": "2025-08-24T22:51:23.000000Z"
}
```

### Frontend Interface:
```typescript
interface Status {
  id: number;
  title: string;
}
```

### MISMATCHES FOUND:
1. **Missing fields**: Frontend doesn't include `admin_id`, `slug`, `created_at`, `updated_at` that exist in API
2. **No mismatch in core fields**: `id` and `title` match correctly

## 3. Client API Response vs Frontend Interface

### API Response Fields:
```json
{
  "acct_create_mail_sent": 0,
  "active_projects": 0,
  "address": null,
  "admin_id": 1,
  "city": null,
  "client_note": null,
  "company": "Test Company",
  "country": null,
  "country_code": null,
  "created_at": "2025-08-28T00:54:05.000000Z",
  "dob": null,
  "doj": null,
  "email": "test@example.com",
  "email_verification_mail_sent": 0,
  "email_verified_at": null,
  "first_name": "John",
  "id": 1,
  "internal_purpose": null,
  "lang": "en",
  "last_name": "Doe",
  "phone": null,
  "photo": null,
  "projects_count": 0,
  "state": null,
  "status": 1,
  "tasks_count": 0,
  "updated_at": "2025-08-28T00:54:05.000000Z",
  "zip": null
}
```

### Frontend Interface:
```typescript
interface Client {
  id: number;
  first_name: string;
  last_name: string;
  name?: string; // For backward compatibility
  company?: string;
}
```

### MISMATCHES FOUND:
1. **Missing fields**: Frontend doesn't include many API fields like `email`, `phone`, `address`, `status`, etc.
2. **name field**: Frontend has optional `name` field that doesn't exist in API (uses `first_name` + `last_name`)
3. **Core fields match**: `id`, `first_name`, `last_name`, `company` match correctly

## 4. Nested Object Field Mismatches

### Priority Object API vs Frontend:
**API Response:**
```json
{
  "id": 3,
  "admin_id": 1,
  "title": "High",
  "slug": "high",
  "created_at": null,
  "updated_at": null
}
```

**Frontend Interface:**
```typescript
priority?: {
  id: number;
  title: string;
}
```

**MISMATCH:** Frontend missing `admin_id`, `slug`, `created_at`, `updated_at`

### Project Object API vs Frontend:
**API Response:**
```json
{
  "id": 7,
  "admin_id": null,
  "workspace_id": 1,
  "title": "Summer Marketing Campaign",
  "description": "Facebook advertising campaign for summer product launch",
  "status_id": 20,
  "priority_id": null,
  "budget": null,
  "start_date": "2025-08-28",
  "end_date": "2025-09-15",
  "created_by": 1,
  "is_favorite": 0,
  "task_accessibility": "assigned_users",
  "note": null,
  "created_at": "2025-08-28T00:54:05.000000Z",
  "updated_at": "2025-08-28T00:58:26.000000Z"
}
```

**Frontend Interface:**
```typescript
project?: {
  id: number;
  title: string;
}
```

**MISMATCH:** Frontend missing 14 fields including `description`, `status_id`, `start_date`, `end_date`, etc.

### TaskType Object API vs Frontend:
**API Response:**
```json
{
  "id": 7,
  "task_type": "Graphics",
  "created_at": "2024-08-24T01:54:36.000000Z",
  "updated_at": "2025-08-24T11:37:35.000000Z"
}
```

**Frontend Interface:**
```typescript
task_type?: {
  id: number;
  task_type: string;
}
```

**MISMATCH:** Frontend missing `created_at`, `updated_at`

## 5. Question Answers Structure Mismatch

### API Response:
```json
{
  "id": 29,
  "task_id": 67,
  "question_id": 29,
  "question_answer": "Updated: Deep Blue",
  "answer_by": 1,
  "check_brief": 0,
  "created_at": "2025-08-28T23:17:41.000000Z",
  "updated_at": "2025-08-28T23:28:29.000000Z",
  "brief_questions": {
    "id": 29,
    "task_brief_templates_id": 13,
    "question_text": "What is your brand color?",
    "question_type": "text",
    "options": null,
    "created_at": "2025-08-28T23:14:01.000000Z",
    "updated_at": "2025-08-28T23:14:01.000000Z"
  }
}
```

### Frontend Interface:
```typescript
question_answers?: Array<{
  question_id: number;
  question_answer: string;
}>;
```

**MISMATCH:** Frontend missing `id`, `task_id`, `answer_by`, `check_brief`, `created_at`, `updated_at`, and entire `brief_questions` object

## 6. Checklist Answers Structure Mismatch

### API Response:
```json
{
  "id": 10,
  "task_id": 67,
  "checklist_id": 19,
  "checklist_answer": {
    "completed": true,
    "notes": "Get client feedback",
    "item_index": 2
  },
  "answer_by": 1,
  "created_at": "2025-08-28T23:21:01.000000Z",
  "updated_at": "2025-08-28T23:27:07.000000Z"
}
```

### Frontend Interface:
```typescript
checklist_answers?: Array<{
  completed: boolean;
}>;
```

**MISMATCH:** Frontend missing `id`, `task_id`, `checklist_id`, `answer_by`, `created_at`, `updated_at`, and `notes`/`item_index` in the answer object

## 7. Client Name Field Mismatch

### API Response for Clients:
```json
{
  "id": 83,
  "first_name": "John",
  "last_name": "Marketing",
  "company": "Acme Corporation",
  "email": "test@example.com",
  // ... other fields
}
```

### Frontend Interface:
```typescript
clients?: Array<{
  id: number;
  name: string;  // ❌ MISMATCH: This field doesn't exist in API
}>;
```

### Frontend Usage (from tasks/page.tsx):
```typescript
{task.clients.map(client => client.name || 'Unnamed Client').join(', ')}
```

### MISMATCH:
- **API**: Clients have `first_name` and `last_name` fields
- **Frontend**: Expects a `name` field that doesn't exist
- **Result**: `client.name` is `undefined`, shows "Unnamed Client"

### Frontend Helper Function (in some files):
```typescript
const getClientDisplayName = (client: Client) => {
  return client.name || `${client.first_name} ${client.last_name}`.trim();
};
```

**PROBLEM**: The helper function exists but the interface still expects `name` field, and many places directly access `client.name`.

## 8. Portfolio Client and CreatedBy Name Field Mismatches

### Portfolio API Response Structure:
```json
{
  "id": 1,
  "client_id": 83,
  "task_id": 67,
  "project_id": 7,
  "title": "Sample Portfolio",
  "description": "Portfolio description",
  "deliverable_type": "design",
  "status": "completed",
  "created_by": 1,
  "completed_at": "2025-08-28T23:17:32.000000Z",
  "created_at": "2025-08-28T23:17:32.000000Z",
  "updated_at": "2025-08-28T23:17:32.000000Z",
  "client": {
    "id": 83,
    "first_name": "John",
    "last_name": "Marketing",
    "company": "Acme Corporation",
    // ... other client fields
  },
  "createdBy": {
    "id": 1,
    "first_name": "Admin",
    "last_name": "User",
    // ... other user fields
  }
}
```

### Frontend Interface:
```typescript
interface Portfolio {
  // ... other fields
  client: {
    id: number;
    name: string;  // ❌ MISMATCH: This field doesn't exist in API
    company?: string;
  };
  createdBy: {
    id: number;
    name: string;  // ❌ MISMATCH: This field doesn't exist in API
  };
}
```

### Frontend Usage (from portfolio/page.tsx):
```typescript
{client.name} {client.company ? `(${client.company})` : ''}
<span>{portfolio.client.name}</span>
```

### MISMATCHES:
1. **Portfolio Client**: Same issue as task clients - frontend expects `client.name` but API has `first_name` + `last_name`
2. **Portfolio CreatedBy**: Frontend expects `createdBy.name` but API has `first_name` + `last_name`
3. **Result**: Both show as undefined/empty in the UI

## 9. Dropdown Value Type Mismatch (FIXED)

### Issue Found:
- **Form data**: Uses numbers (`status_id: 20`)
- **Dropdown first option**: Had `value=""` (empty string)
- **Result**: No match, dropdowns appeared blank

### Fix Applied:
- Changed dropdown first options from `value=""` to `value="0"`
- Now all dropdown values are numbers and match form data

## Summary of Critical Issues:

1. **✅ FIXED**: Dropdown value type mismatch (empty string vs number)
2. **❌ UNFIXED**: `close_deadline` type mismatch (number vs boolean)
3. **❌ UNFIXED**: `due_date` vs `end_date` field name mismatch
4. **❌ UNFIXED**: `assigned_to` vs `users` structure mismatch
5. **❌ UNFIXED**: `client` vs `clients` structure mismatch
6. **❌ UNFIXED**: `checklist_answers` structure mismatch
7. **❌ UNFIXED**: `question_answers` structure mismatch
8. **❌ UNFIXED**: Missing fields in nested objects (priority, project, task_type)
9. **❌ UNFIXED**: Client name field mismatch (`name` vs `first_name` + `last_name`)
10. **❌ UNFIXED**: Frontend accessing non-existent `client.name` field
11. **❌ UNFIXED**: Portfolio client name field mismatch (same as #9)
12. **❌ UNFIXED**: Portfolio createdBy name field mismatch (`name` vs `first_name` + `last_name`)

## Recommendations:

1. Fix `close_deadline` type handling in frontend
2. Standardize on `end_date` or `due_date` field name
3. Update frontend to handle `users` array instead of `assigned_to` object
4. Update frontend to handle `clients` array instead of `client` object
5. Update `checklist_answers` interface to match API structure
6. Update `question_answers` interface to match API structure
7. Add missing fields to nested object interfaces (priority, project, task_type)
8. Fix client name field mismatch - remove `name` from interfaces, use `first_name` + `last_name`
9. Update all frontend code to use `getClientDisplayName()` helper instead of `client.name`
10. Fix portfolio client and createdBy name field mismatches (same as #8)
11. Consider adding missing fields to interfaces for completeness
