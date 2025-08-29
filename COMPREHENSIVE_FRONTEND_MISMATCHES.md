# COMPREHENSIVE FRONTEND PAGE MISMATCHES

## Overview
This document contains ALL mismatches found between API responses and frontend interfaces across every page in the VendorConnect frontend application.

## 1. TASKS PAGES

### `/tasks/page.tsx` (Task List)
**MISMATCHES:**
- ✅ **FIXED**: `status.name` → `status.title`
- ✅ **FIXED**: `priority.name` → `priority.title`
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `users` array expects `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: `end_date` field (matches API)

### `/tasks/[id]/page.tsx` (Task Detail)
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `question_answers` structure mismatch (API has complex structure)
- ❌ **UNFIXED**: `checklist_answers` structure mismatch (API has complex structure)

### `/tasks/[id]/edit/page.tsx` (Task Edit)
**MISMATCHES:**
- ✅ **FIXED**: Dropdown value type mismatch (empty string vs number)
- ✅ **FIXED**: `status.title`, `priority.title`, `project.title` (matches API)
- ❌ **UNFIXED**: `close_deadline` type mismatch (API returns number, frontend expects boolean)
- ❌ **UNFIXED**: `client` expects `name` field but API has `first_name` + `last_name`

### `/tasks/new/page.tsx` (New Task)
**MISMATCHES:**
- ✅ **FIXED**: `status.title`, `priority.title`, `task_type.task_type` (matches API)
- ❌ **UNFIXED**: `client` expects `name` field but API has `first_name` + `last_name`

## 2. PROJECTS PAGES

### `/projects/page.tsx` (Project List)
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `status.title` (matches API)
- ❌ **UNFIXED**: `users` array expects `first_name` + `last_name` (matches API)

### `/projects/[id]/page.tsx` (Project Detail)
**MISMATCHES:**
- ✅ **FIXED**: `status.title` (matches API)
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `tasks` array expects `status.title` (matches API)

### `/projects/[id]/edit/page.tsx` (Project Edit)
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `status.title` (matches API)

### `/projects/new/page.tsx` (New Project)
**MISMATCHES:**
- ❌ **UNFIXED**: `clients` array expects `client.name` but API has `first_name` + `last_name`

## 3. CLIENTS PAGES

### `/clients/page.tsx` (Client List)
**MISMATCHES:**
- ✅ **FIXED**: API response parsing (`response.data.data || []`)
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ❌ **UNFIXED**: Uses `getClientDisplayName()` helper but interface still expects `name`

### `/clients/[id]/page.tsx` (Client Detail)
**MISMATCHES:**
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ✅ **FIXED**: `Project.status.title` (matches API)
- ✅ **FIXED**: `Task.status.title` and `Task.priority.title` (matches API)

### `/clients/[id]/edit/page.tsx` (Client Edit)
**MISMATCHES:**
- ❌ **UNFIXED**: `Client` interface has `name` field but API has `first_name` + `last_name`
- ❌ **UNFIXED**: Form expects `name` field but should use `first_name` + `last_name`

### `/clients/new/page.tsx` (New Client)
**MISMATCHES:**
- ❌ **UNFIXED**: Form expects `name` field but API expects `first_name` + `last_name`

## 4. USERS PAGES

### `/users/page.tsx` (User List)
**MISMATCHES:**
- ✅ **FIXED**: `first_name` + `last_name` (matches API)
- ❌ **UNFIXED**: `roles` array structure (API may have different structure)

### `/users/[id]/page.tsx` (User Detail)
**MISMATCHES:**
- ❌ **UNFIXED**: Need to check interface vs API response

### `/users/[id]/edit/page.tsx` (User Edit)
**MISMATCHES:**
- ❌ **UNFIXED**: Need to check interface vs API response

### `/users/new/page.tsx` (New User)
**MISMATCHES:**
- ❌ **UNFIXED**: Need to check interface vs API response

## 5. PORTFOLIO PAGES

### `/portfolio/page.tsx` (Portfolio List)
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/[id]/page.tsx` (Portfolio Detail)
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/[id]/edit/page.tsx` (Portfolio Edit)
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

### `/portfolio/new/page.tsx` (New Portfolio)
**MISMATCHES:**
- ❌ **UNFIXED**: `client.name` but API has `first_name` + `last_name`
- ❌ **UNFIXED**: `createdBy.name` but API has `first_name` + `last_name`

## 6. TEMPLATES PAGES

### `/templates/page.tsx` (Template List)
**MISMATCHES:**
- ✅ **FIXED**: `task_type.task_type` (matches API)

### `/templates/[id]/page.tsx` (Template Detail)
**MISMATCHES:**
- ❌ **UNFIXED**: Need to check interface vs API response

### `/templates/[id]/edit/page.tsx` (Template Edit)
**MISMATCHES:**
- ❌ **UNFIXED**: Need to check interface vs API response

### `/templates/new/page.tsx` (New Template)
**MISMATCHES:**
- ❌ **UNFIXED**: Need to check interface vs API response

## 7. DASHBOARD PAGES

### `/dashboard/page.tsx` (Main Dashboard)
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

### `/dashboard/tasker/page.tsx` (Tasker Dashboard)
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

### `/dashboard/requester/page.tsx` (Requester Dashboard)
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

## 8. PROJECT MANAGEMENT PAGE

### `/project-management/page.tsx`
**MISMATCHES:**
- ✅ **FIXED**: `status.title` and `priority.title` (matches API)

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

### 3. DATA TYPE MISMATCHES
**Affects:** Task edit page
- **API**: `close_deadline` as number (1/0)
- **Frontend**: Expects boolean
- **Impact**: Form validation issues

### 4. STRUCTURE MISMATCHES
**Affects:** Task detail page
- **API**: Complex `question_answers` and `checklist_answers` structures
- **Frontend**: Expects simple structures
- **Impact**: Data not displaying correctly

### 5. WORKSPACE_ID ISSUE
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
