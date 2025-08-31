# VendorConnect Database Schema Documentation

## Overview
This document provides the complete database schema for the VendorConnect application based on the actual production database structure.

## Core Tables

### 1. Users Table (`users`)
**Purpose**: Stores all user accounts (vendors, contractors, taskers)

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| client_id | bigint unsigned | YES | MUL | NULL | Associated client ID |
| first_name | varchar(255) | NO | | NULL | User's first name |
| last_name | varchar(255) | NO | | NULL | User's last name |
| phone | varchar(56) | YES | | NULL | Phone number |
| email | varchar(191) | NO | UNI | NULL | Email address (unique) |
| address | varchar(255) | YES | | NULL | Street address |
| city | varchar(255) | YES | | NULL | City |
| state | varchar(255) | YES | | NULL | State/Province |
| country | varchar(255) | YES | | NULL | Country |
| zip | int | YES | | NULL | ZIP/Postal code |
| password | varchar(255) | NO | | NULL | Hashed password |
| dob | date | YES | | NULL | Date of birth |
| doj | date | YES | | NULL | Date of joining |
| photo | varchar(255) | YES | | NULL | Profile photo path |
| avatar | varchar(255) | NO | | 'avatar.png' | Default avatar |
| active_status | tinyint(1) | NO | | 0 | Account active status |
| dark_mode | tinyint(1) | NO | | 0 | Dark mode preference |
| messenger_color | varchar(255) | YES | | NULL | Chat color preference |
| lang | varchar(28) | NO | | 'en' | Language preference |
| remember_token | text | YES | | NULL | Remember me token |
| email_verified_at | timestamp | YES | | NULL | Email verification timestamp |
| last_login_at | timestamp | YES | | NULL | Last login timestamp |
| created_at | timestamp | YES | | NULL | Creation timestamp |
| updated_at | timestamp | YES | | NULL | Update timestamp |
| status | tinyint | NO | | 0 | Account status |
| country_code | text | YES | | NULL | Country code |

### 2. Clients Table (`clients`)
**Purpose**: Stores client information

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| admin_id | bigint unsigned | YES | MUL | NULL | Associated admin ID |
| first_name | varchar(255) | NO | | NULL | Client's first name |
| last_name | varchar(255) | NO | | NULL | Client's last name |
| company | varchar(255) | YES | | NULL | Company name |
| email | varchar(191) | NO | UNI | NULL | Email address (unique) |
| phone | varchar(255) | YES | | NULL | Phone number |
| dob | date | YES | | NULL | Date of birth |
| doj | date | YES | | NULL | Date of joining |
| address | varchar(255) | YES | | NULL | Street address |
| city | varchar(255) | YES | | NULL | City |
| state | varchar(255) | YES | | NULL | State/Province |
| country | varchar(255) | YES | | NULL | Country |
| zip | varchar(255) | YES | | NULL | ZIP/Postal code |
| photo | varchar(255) | YES | | NULL | Profile photo path |
| status | tinyint | NO | | 0 | Client status |
| lang | varchar(28) | NO | | 'en' | Language preference |
| remember_token | text | YES | | NULL | Remember me token |
| email_verification_mail_sent | tinyint | YES | | NULL | Email verification status |
| email_verified_at | timestamp | YES | | NULL | Email verification timestamp |
| internal_purpose | tinyint | NO | | 0 | Internal purpose flag |
| acct_create_mail_sent | tinyint | NO | | 1 | Account creation email status |
| created_at | timestamp | YES | | NULL | Creation timestamp |
| updated_at | timestamp | YES | | NULL | Update timestamp |
| country_code | varchar(255) | YES | | NULL | Country code |
| client_note | longtext | YES | | NULL | Client notes |

### 3. Projects Table (`projects`)
**Purpose**: Stores project information

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| admin_id | bigint unsigned | YES | MUL | NULL | Associated admin ID |
| workspace_id | bigint unsigned | NO | | NULL | Workspace ID |
| title | varchar(255) | NO | | NULL | Project title |
| description | longtext | YES | | NULL | Project description |
| status_id | bigint unsigned | NO | MUL | 1 | Project status ID |
| priority_id | bigint unsigned | YES | MUL | NULL | Project priority ID |
| budget | varchar(255) | YES | | NULL | Project budget |
| start_date | date | NO | | NULL | Project start date |
| end_date | date | YES | | NULL | Project end date |
| created_by | bigint unsigned | NO | | NULL | Creator user ID |
| is_favorite | tinyint | NO | | 0 | Favorite flag |
| task_accessibility | varchar(28) | NO | | 'assigned_users' | Task access level |
| note | longtext | YES | | NULL | Project notes |
| created_at | timestamp | YES | | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | timestamp | YES | | CURRENT_TIMESTAMP | Update timestamp |

### 4. Tasks Table (`tasks`)
**Purpose**: Stores task information

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| admin_id | bigint unsigned | YES | MUL | NULL | Associated admin ID |
| task_type_id | bigint unsigned | YES | MUL | NULL | Task type ID |
| template_id | bigint unsigned | YES | MUL | NULL | Template ID |
| project_id | bigint unsigned | YES | MUL | NULL | Project ID |
| title | varchar(255) | NO | | NULL | Task title |
| description | longtext | YES | | NULL | Task description |
| standard_brief | text | YES | | NULL | Standard brief |
| start_date | date | NO | | NULL | Task start date |
| end_date | date | YES | | NULL | Task end date |
| status_id | int | NO | | NULL | Task status ID |
| priority_id | int | NO | | NULL | Task priority ID |
| close_deadline | int | NO | | NULL | Close deadline |
| note | longtext | YES | | NULL | Task notes |
| deliverable_quantity | int | YES | | 1 | Deliverable quantity |
| is_repeating | tinyint(1) | YES | | 0 | Repeating task flag |
| repeat_frequency | enum | YES | | NULL | Repeat frequency |
| repeat_interval | int | YES | | 1 | Repeat interval |
| repeat_until | date | YES | | NULL | Repeat until date |
| repeat_active | tinyint(1) | YES | | 1 | Repeat active flag |
| parent_task_id | bigint unsigned | YES | MUL | NULL | Parent task ID |
| last_repeated_at | timestamp | YES | | NULL | Last repeated timestamp |
| created_by | bigint unsigned | NO | | NULL | Creator user ID |
| created_at | timestamp | YES | | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | timestamp | YES | | CURRENT_TIMESTAMP | Update timestamp |
| template_questions | json | YES | | NULL | Template questions |
| template_checklist | json | YES | | NULL | Template checklist |
| template_standard_brief | text | YES | | NULL | Template standard brief |
| template_description | text | YES | | NULL | Template description |
| template_deliverable_quantity | int | YES | | NULL | Template deliverable quantity |

### 5. Task Brief Templates Table (`task_brief_templates`)
**Purpose**: Stores task template information

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| title | varchar(255) | NO | | NULL | Template title |
| standard_brief | text | YES | | NULL | Standard brief |
| description | text | YES | | NULL | Template description |
| deliverable_quantity | int | YES | | 1 | Deliverable quantity |
| task_type_id | bigint unsigned | NO | MUL | NULL | Task type ID |
| created_at | timestamp | YES | | NULL | Creation timestamp |
| updated_at | timestamp | YES | | NULL | Update timestamp |

### 6. Statuses Table (`statuses`)
**Purpose**: Stores status options

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| admin_id | bigint unsigned | YES | MUL | NULL | Associated admin ID |
| title | varchar(255) | NO | | NULL | Status title |
| slug | varchar(255) | NO | | NULL | Status slug |
| created_at | timestamp | YES | | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | timestamp | YES | | CURRENT_TIMESTAMP | Update timestamp |

### 7. Priorities Table (`priorities`)
**Purpose**: Stores priority options

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| admin_id | bigint unsigned | YES | MUL | NULL | Associated admin ID |
| title | varchar(255) | NO | | NULL | Priority title |
| slug | varchar(255) | NO | | NULL | Priority slug |
| created_at | timestamp | YES | | NULL | Creation timestamp |
| updated_at | timestamp | YES | | NULL | Update timestamp |

### 8. Task Types Table (`task_types`)
**Purpose**: Stores task type categories

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| task_type | varchar(50) | NO | | NULL | Task type name |
| created_at | timestamp | YES | | NULL | Creation timestamp |
| updated_at | timestamp | YES | | NULL | Update timestamp |

### 9. Tags Table (`tags`)
**Purpose**: Stores tags for categorization

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| id | bigint unsigned | NO | PRI | auto_increment | Primary key |
| admin_id | bigint unsigned | YES | MUL | NULL | Associated admin ID |
| title | varchar(255) | NO | | NULL | Tag title |
| slug | varchar(255) | NO | | NULL | Tag slug |
| created_at | timestamp | YES | | NULL | Creation timestamp |
| updated_at | timestamp | YES | | NULL | Update timestamp |

## Relationship Tables

### 10. Model Has Roles Table (`model_has_roles`)
**Purpose**: Links users to their roles

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| role_id | bigint unsigned | NO | PRI | NULL | Role ID |
| model_type | varchar(255) | NO | PRI | NULL | Model type (e.g., 'App\\Models\\User') |
| model_id | bigint unsigned | NO | PRI | NULL | Model ID (user ID) |

### 11. Task User Table (`task_user`)
**Purpose**: Links tasks to assigned users

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| task_id | bigint unsigned | NO | PRI | NULL | Task ID |
| user_id | bigint unsigned | NO | PRI | NULL | User ID |

### 12. Project User Table (`project_user`)
**Purpose**: Links projects to assigned users

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| project_id | bigint unsigned | NO | PRI | NULL | Project ID |
| user_id | bigint unsigned | NO | PRI | NULL | User ID |

## Key Issues Identified

### 1. Schema Mismatches in Demo Data
- **Clients table**: Expected `name` field but actual schema uses `first_name`, `last_name`, `company`
- **Tags table**: Expected `name` field but actual schema uses `title` and `slug`
- **Task Brief Templates**: Missing required `task_type_id` field

### 2. Required Fields
- `task_brief_templates.task_type_id` is NOT NULL but was missing from demo data
- `tags.slug` is NOT NULL but was missing from demo data
- `statuses.slug` and `priorities.slug` are NOT NULL but were missing from demo data

### 3. Foreign Key Relationships
- Tasks reference `status_id` and `priority_id` as integers, not bigint
- Projects reference `status_id` and `priority_id` as bigint
- Inconsistent data types for similar fields across tables

## Recommendations

1. **Update Demo Data Scripts**: Fix all schema mismatches in demo data files
2. **Standardize Data Types**: Ensure consistent data types for similar fields
3. **Document Relationships**: Create ERD (Entity Relationship Diagram)
4. **Add Constraints**: Ensure proper foreign key constraints
5. **Create Migration Scripts**: For any schema changes needed

## Current Demo Data Status

✅ **Successfully Loaded**:
- Users (8 demo users with IDs 200-207)
- Clients (5 demo clients with IDs 200-204)

❌ **Failed to Load**:
- Statuses, Priorities, Task Types (due to missing slug fields)
- Task Brief Templates (due to missing task_type_id)
- Projects, Tasks (due to dependency issues)

This documentation should be used as the authoritative source for all database operations and demo data creation.
