-- =============================================================================
-- VENDORCONNECT DATABASE SCHEMA
-- =============================================================================
-- 
-- This file contains the complete database schema for the VendorConnect application
-- with detailed comments about which APIs use each table and column.
-- 
-- Generated: August 28, 2025
-- Database: vendorconnect
-- 
-- ⚠️ CRITICAL: Field Naming Inconsistencies
-- IMPORTANT: The following tables have field naming inconsistencies between database and API:
-- 
-- 1. statuses table: Uses 'title' field, but API also returns 'name' (duplicate)
-- 2. priorities table: Uses 'title' field, but API also returns 'name' (duplicate)  
-- 3. task_types table: Uses 'task_type' field, but API also returns 'name' (duplicate)
-- 4. projects table: Uses 'title' field, API returns 'title' (correct)
--
-- Frontend should use the primary database field names for consistency.
-- 
-- API Endpoints:
-- - /api/v1/auth/* - Authentication (login, logout, password reset)
-- - /api/v1/users/* - User management
-- - /api/v1/clients/* - Client management
-- - /api/v1/projects/* - Project management
-- - /api/v1/tasks/* - Task management
-- - /api/v1/notifications/* - Notification system
-- - /api/v1/portfolios/* - Portfolio management
-- - /api/v1/settings/* - Application settings
-- =============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================================================
-- CORE USER MANAGEMENT TABLES
-- =============================================================================

-- Users table - Core user data
-- APIs: /api/v1/auth/*, /api/v1/users/*, /api/v1/profile/*
-- Used by: AuthController, UserController, ProfileController
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User first name - used in /api/v1/users/* and /api/v1/profile/*',
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User last name - used in /api/v1/users/* and /api/v1/profile/*',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'User email - used in /api/v1/auth/login and /api/v1/users/*',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Email verification timestamp - used in /api/v1/auth/verify-email',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hashed password - used in /api/v1/auth/login',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Remember me token - used in /api/v1/auth/*',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User profile photo - used in /api/v1/profile/*/photo',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT 'User status (1=active, 0=inactive) - used in /api/v1/users/*',
  `dark_mode` tinyint NOT NULL DEFAULT '0' COMMENT 'Dark mode preference - used in /api/v1/profile/*',
  `messenger_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Messenger color preference',
  `country_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Country code - used in /api/v1/users/*',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Last login timestamp - used in /api/v1/auth/login',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Core users table - used by authentication and user management APIs';

-- Password resets table - Password reset functionality
-- APIs: /api/v1/auth/forgot-password, /api/v1/auth/reset-password
-- Used by: AuthController
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email for password reset - used in /api/v1/auth/forgot-password',
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Reset token - used in /api/v1/auth/reset-password',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Token creation timestamp',
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens - used by authentication APIs';

-- Personal access tokens table - API authentication
-- APIs: All authenticated endpoints via Sanctum middleware
-- Used by: Laravel Sanctum middleware
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tokenable model type (usually User)',
  `tokenable_id` bigint unsigned NOT NULL COMMENT 'Tokenable model ID (usually user ID)',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token name',
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Hashed token value',
  `abilities` text COLLATE utf8mb4_unicode_ci COMMENT 'Token abilities/permissions',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Last token usage timestamp',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Token expiration timestamp',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API authentication tokens - used by Sanctum middleware';

-- =============================================================================
-- ROLE AND PERMISSION TABLES
-- =============================================================================

-- Roles table - User roles
-- APIs: /api/v1/roles/*, /api/v1/users/* (role assignment)
-- Used by: RoleController, UserController
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Role name (admin, sub_admin, requester, tasker) - used in /api/v1/roles/*',
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web' COMMENT 'Guard name for role',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User roles - used by role management APIs';

-- Permissions table - System permissions
-- APIs: /api/v1/roles/* (permission assignment)
-- Used by: RoleController
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Permission name - used in /api/v1/roles/*',
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web' COMMENT 'Guard name for permission',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System permissions - used by role management APIs';

-- Model has roles table - User-role relationships
-- APIs: /api/v1/users/*, /api/v1/user-roles/*
-- Used by: UserController, UserRoleController
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL COMMENT 'Role ID - used in /api/v1/user-roles/*',
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Model type (usually User)',
  `model_id` bigint unsigned NOT NULL COMMENT 'Model ID (usually user ID) - used in /api/v1/users/*',
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User-role relationships - used by user and role management APIs';

-- Model has permissions table - User-permission relationships
-- APIs: /api/v1/roles/* (permission assignment)
-- Used by: RoleController
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL COMMENT 'Permission ID',
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Model type',
  `model_id` bigint unsigned NOT NULL COMMENT 'Model ID',
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User-permission relationships - used by role management APIs';

-- Role has permissions table - Role-permission relationships
-- APIs: /api/v1/roles/* (permission assignment)
-- Used by: RoleController
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL COMMENT 'Permission ID',
  `role_id` bigint unsigned NOT NULL COMMENT 'Role ID',
  PRIMARY KEY (`permission_id`,`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role-permission relationships - used by role management APIs';

-- =============================================================================
-- CLIENT MANAGEMENT TABLES
-- =============================================================================

-- Clients table - Client data
-- APIs: /api/v1/clients/*, /api/v1/projects/* (client assignment)
-- Used by: ClientController, ProjectController
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Client name - used in /api/v1/clients/*',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client email - used in /api/v1/clients/* (admin only)',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client phone - used in /api/v1/clients/* (admin only)',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT 'Client address - used in /api/v1/clients/* (admin only)',
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client company - used in /api/v1/clients/*',
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client website - used in /api/v1/clients/* (admin only)',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Client notes - used in /api/v1/clients/* (admin only)',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT 'Client status (1=active, 0=inactive) - used in /api/v1/clients/*',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client city - used in /api/v1/clients/* (admin only)',
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client state - used in /api/v1/clients/* (admin only)',
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client country - used in /api/v1/clients/* (admin only)',
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client zip code - used in /api/v1/clients/* (admin only)',
  `dob` date DEFAULT NULL COMMENT 'Client date of birth - used in /api/v1/clients/* (admin only)',
  `doj` date DEFAULT NULL COMMENT 'Client date of joining - used in /api/v1/clients/* (admin only)',
  `country_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Country code - used in /api/v1/clients/*',
  `internal_purpose` text COLLATE utf8mb4_unicode_ci COMMENT 'Internal purpose notes - used in /api/v1/clients/* (admin only)',
  `acc_mail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Account email - used in /api/v1/clients/* (admin only)',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Client photo - used in /api/v1/clients/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Client data - used by client management APIs';

-- Client user table - Client-user relationships
-- APIs: /api/v1/clients/* (user assignment)
-- Used by: ClientController
CREATE TABLE `client_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/clients/*',
  `user_id` bigint unsigned NOT NULL COMMENT 'User ID - used in /api/v1/clients/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_user_client_id_foreign` (`client_id`),
  KEY `client_user_user_id_foreign` (`user_id`),
  CONSTRAINT `client_user_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Client-user relationships - used by client management APIs';

-- Client credentials table - Client login credentials
-- APIs: /api/v1/clients/*/credentials/*
-- Used by: ClientCredentialController
CREATE TABLE `client_credentials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/clients/*/credentials/*',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Username - used in /api/v1/clients/*/credentials/*',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Encrypted password - used in /api/v1/clients/*/credentials/*',
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Platform name - used in /api/v1/clients/*/credentials/*',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes - used in /api/v1/clients/*/credentials/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_credentials_client_id_foreign` (`client_id`),
  CONSTRAINT `client_credentials_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Client credentials - used by client credential management APIs';

-- Client files table - Client file attachments
-- APIs: /api/v1/clients/* (file management)
-- Used by: ClientController
CREATE TABLE `client_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/clients/*',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'File name - used in /api/v1/clients/*',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'File path - used in /api/v1/clients/*',
  `file_size` bigint unsigned DEFAULT NULL COMMENT 'File size in bytes - used in /api/v1/clients/*',
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File MIME type - used in /api/v1/clients/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_files_client_id_foreign` (`client_id`),
  CONSTRAINT `client_files_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Client file attachments - used by client management APIs';

-- =============================================================================
-- PROJECT MANAGEMENT TABLES
-- =============================================================================

-- Projects table - Project data
-- APIs: /api/v1/projects/*, /api/v1/clients/*/projects
-- Used by: ProjectController, ClientController
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Project title - used in /api/v1/projects/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Project description - used in /api/v1/projects/*',
  `start_date` date DEFAULT NULL COMMENT 'Project start date - used in /api/v1/projects/*',
  `end_date` date DEFAULT NULL COMMENT 'Project end date - used in /api/v1/projects/*',
  `status_id` bigint unsigned DEFAULT NULL COMMENT 'Project status ID - used in /api/v1/projects/*',
  `priority_id` bigint unsigned DEFAULT NULL COMMENT 'Project priority ID - used in /api/v1/projects/*',
  `workspace_id` bigint unsigned DEFAULT NULL COMMENT 'Workspace ID - used in /api/v1/projects/*',
  `created_by` bigint unsigned DEFAULT NULL COMMENT 'User who created the project - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_status_id_foreign` (`status_id`),
  KEY `projects_priority_id_foreign` (`priority_id`),
  KEY `projects_workspace_id_foreign` (`workspace_id`),
  KEY `projects_created_by_foreign` (`created_by`),
  CONSTRAINT `projects_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project data - used by project management APIs';

-- Project user table - Project-user relationships
-- APIs: /api/v1/projects/* (user assignment)
-- Used by: ProjectController
CREATE TABLE `project_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL COMMENT 'Project ID - used in /api/v1/projects/*',
  `user_id` bigint unsigned NOT NULL COMMENT 'User ID - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_user_project_id_foreign` (`project_id`),
  KEY `project_user_user_id_foreign` (`user_id`),
  CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project-user relationships - used by project management APIs';

-- Project client table - Project-client relationships
-- APIs: /api/v1/projects/* (client assignment), /api/v1/clients/*/projects
-- Used by: ProjectController, ClientController
CREATE TABLE `project_client` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL COMMENT 'Project ID - used in /api/v1/projects/* and /api/v1/clients/*/projects',
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/projects/* and /api/v1/clients/*/projects',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_client_project_id_foreign` (`project_id`),
  KEY `project_client_client_id_foreign` (`client_id`),
  CONSTRAINT `project_client_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_client_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project-client relationships - used by project and client management APIs';

-- Project statuses table - Project status definitions
-- APIs: /api/v1/projects/* (status assignment)
-- Used by: ProjectController
CREATE TABLE `project_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Status name - used in /api/v1/projects/*',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status color (hex) - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project status definitions - used by project management APIs';

-- =============================================================================
-- TASK MANAGEMENT TABLES
-- =============================================================================

-- Tasks table - Task data
-- APIs: /api/v1/tasks/*, /api/v1/clients/*/tasks, /api/v1/projects/*/tasks
-- Used by: TaskController, ClientController, ProjectController
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Task title - used in /api/v1/tasks/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Task description - used in /api/v1/tasks/*',
  `note` text COLLATE utf8mb4_unicode_ci COMMENT 'Task notes - used in /api/v1/tasks/*',
  `status_id` bigint unsigned DEFAULT NULL COMMENT 'Task status ID - used in /api/v1/tasks/*',
  `priority_id` bigint unsigned DEFAULT NULL COMMENT 'Task priority ID - used in /api/v1/tasks/*',
  `project_id` bigint unsigned DEFAULT NULL COMMENT 'Project ID - used in /api/v1/tasks/* and /api/v1/projects/*/tasks',
  `task_type_id` bigint unsigned DEFAULT NULL COMMENT 'Task type ID - used in /api/v1/tasks/*',
  `template_id` bigint unsigned DEFAULT NULL COMMENT 'Task template ID - used in /api/v1/tasks/*',
  `start_date` date DEFAULT NULL COMMENT 'Task start date - used in /api/v1/tasks/*',
  `end_date` date DEFAULT NULL COMMENT 'Task end date - used in /api/v1/tasks/*',
  `close_deadline` tinyint NOT NULL DEFAULT '0' COMMENT 'Close deadline flag - used in /api/v1/tasks/*',
  `deliverable_quantity` int DEFAULT NULL COMMENT 'Deliverable quantity - used in /api/v1/tasks/*',
  `standard_brief` text COLLATE utf8mb4_unicode_ci COMMENT 'Standard brief - used in /api/v1/tasks/*',
  `repeat_frequency` enum('daily','weekly','monthly','yearly') DEFAULT NULL COMMENT 'Repetition frequency - used in /api/v1/tasks/*',
  `repetition_interval` int DEFAULT NULL COMMENT 'Repetition interval - used in /api/v1/tasks/*',
  `repeat_until` date DEFAULT NULL COMMENT 'Repetition end date - used in /api/v1/tasks/*',
  `is_repeating` tinyint NOT NULL DEFAULT '0' COMMENT 'Is repeating task - used in /api/v1/tasks/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_status_id_foreign` (`status_id`),
  KEY `tasks_priority_id_foreign` (`priority_id`),
  KEY `tasks_project_id_foreign` (`project_id`),
  KEY `tasks_task_type_id_foreign` (`task_type_id`),
  KEY `tasks_template_id_foreign` (`template_id`),
  CONSTRAINT `tasks_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_task_type_id_foreign` FOREIGN KEY (`task_type_id`) REFERENCES `task_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_brief_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task data - used by task management APIs';

-- Task user table - Task-user relationships
-- APIs: /api/v1/tasks/* (user assignment)
-- Used by: TaskController
CREATE TABLE `task_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL COMMENT 'Task ID - used in /api/v1/tasks/*',
  `user_id` bigint unsigned NOT NULL COMMENT 'User ID - used in /api/v1/tasks/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_user_task_id_foreign` (`task_id`),
  KEY `task_user_user_id_foreign` (`user_id`),
  CONSTRAINT `task_user_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task-user relationships - used by task management APIs';

-- Task client table - Task-client relationships
-- APIs: /api/v1/tasks/* (client assignment), /api/v1/clients/*/tasks
-- Used by: TaskController, ClientController
CREATE TABLE `task_client` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL COMMENT 'Task ID - used in /api/v1/tasks/* and /api/v1/clients/*/tasks',
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/tasks/* and /api/v1/clients/*/tasks',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_client_task_id_foreign` (`task_id`),
  KEY `task_client_client_id_foreign` (`client_id`),
  CONSTRAINT `task_client_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_client_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task-client relationships - used by task and client management APIs';

-- Task deliverables table - Task deliverable data
-- APIs: /api/v1/tasks/*/deliverables/*
-- Used by: TaskDeliverableController
CREATE TABLE `task_deliverables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL COMMENT 'Task ID - used in /api/v1/tasks/*/deliverables/*',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Deliverable title - used in /api/v1/tasks/*/deliverables/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Deliverable description - used in /api/v1/tasks/*/deliverables/*',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Deliverable type - used in /api/v1/tasks/*/deliverables/*',
  `quantity` int DEFAULT NULL COMMENT 'Deliverable quantity - used in /api/v1/tasks/*/deliverables/*',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'Completion timestamp - used in /api/v1/tasks/*/deliverables/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_deliverables_task_id_foreign` (`task_id`),
  CONSTRAINT `task_deliverables_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task deliverables - used by task deliverable management APIs';

-- =============================================================================
-- SUPPORTING TABLES (STATUSES, PRIORITIES, TYPES)
-- =============================================================================

-- Statuses table - Task status definitions
-- APIs: /api/v1/statuses/*, /api/v1/tasks/* (status assignment)
-- Used by: StatusController, TaskController
CREATE TABLE `statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Status name - used in /api/v1/statuses/* and /api/v1/tasks/*',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Status slug - used in /api/v1/statuses/*',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status color (hex) - used in /api/v1/statuses/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `statuses_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task status definitions - used by status and task management APIs';

-- Priorities table - Task priority definitions
-- APIs: /api/v1/priorities/*, /api/v1/tasks/* (priority assignment)
-- Used by: PriorityController, TaskController
CREATE TABLE `priorities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Priority name - used in /api/v1/priorities/* and /api/v1/tasks/*',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Priority slug - used in /api/v1/priorities/*',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Priority color (hex) - used in /api/v1/priorities/*',
  `level` int DEFAULT NULL COMMENT 'Priority level (1=lowest, 5=highest) - used in /api/v1/priorities/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priorities_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task priority definitions - used by priority and task management APIs';

-- Task types table - Task type definitions
-- APIs: /api/v1/task-types/*, /api/v1/tasks/* (type assignment)
-- Used by: TaskTypeController, TaskController
CREATE TABLE `task_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Task type name - used in /api/v1/task-types/* and /api/v1/tasks/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Task type description - used in /api/v1/task-types/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task type definitions - used by task type and task management APIs';

-- =============================================================================
-- TASK TEMPLATE TABLES
-- =============================================================================

-- Task brief templates table - Task template definitions
-- APIs: /api/v1/task-brief-templates/*, /api/v1/tasks/* (template assignment)
-- Used by: TaskBriefTemplateController, TaskController
CREATE TABLE `task_brief_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Template title - used in /api/v1/task-brief-templates/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Template description - used in /api/v1/task-brief-templates/*',
  `standard_brief` text COLLATE utf8mb4_unicode_ci COMMENT 'Standard brief text - used in /api/v1/task-brief-templates/*',
  `task_type_id` bigint unsigned DEFAULT NULL COMMENT 'Associated task type - used in /api/v1/task-brief-templates/*',
  `deliverable_quantity` int DEFAULT NULL COMMENT 'Default deliverable quantity - used in /api/v1/task-brief-templates/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_brief_templates_task_type_id_foreign` (`task_type_id`),
  CONSTRAINT `task_brief_templates_task_type_id_foreign` FOREIGN KEY (`task_type_id`) REFERENCES `task_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task brief templates - used by task template management APIs';

-- Task brief questions table - Template questions
-- APIs: /api/v1/task-brief-questions/*, /api/v1/tasks/* (question answers)
-- Used by: TaskBriefQuestionController, TaskController
CREATE TABLE `task_brief_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL COMMENT 'Template ID - used in /api/v1/task-brief-questions/*',
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Question text - used in /api/v1/task-brief-questions/*',
  `question_type` enum('text','textarea','select','checkbox','radio') NOT NULL COMMENT 'Question type - used in /api/v1/task-brief-questions/*',
  `options` json DEFAULT NULL COMMENT 'Question options (for select/radio) - used in /api/v1/task-brief-questions/*',
  `required` tinyint NOT NULL DEFAULT '0' COMMENT 'Is question required - used in /api/v1/task-brief-questions/*',
  `order` int DEFAULT NULL COMMENT 'Question order - used in /api/v1/task-brief-questions/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_brief_questions_template_id_foreign` (`template_id`),
  CONSTRAINT `task_brief_questions_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_brief_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task brief questions - used by task template and question management APIs';

-- Task brief checklists table - Template checklists
-- APIs: /api/v1/task-brief-checklists/*, /api/v1/tasks/* (checklist answers)
-- Used by: TaskBriefChecklistController, TaskController
CREATE TABLE `task_brief_checklists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL COMMENT 'Template ID - used in /api/v1/task-brief-checklists/*',
  `checklist` json NOT NULL COMMENT 'Checklist items - used in /api/v1/task-brief-checklists/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_brief_checklists_template_id_foreign` (`template_id`),
  CONSTRAINT `task_brief_checklists_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_brief_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Task brief checklists - used by task template and checklist management APIs';

-- =============================================================================
-- NOTIFICATION TABLES
-- =============================================================================

-- Notifications table - User notifications
-- APIs: /api/v1/notifications/*
-- Used by: NotificationController
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'User ID - used in /api/v1/notifications/*',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Notification type - used in /api/v1/notifications/*',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Notification title - used in /api/v1/notifications/*',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Notification message - used in /api/v1/notifications/*',
  `data` json DEFAULT NULL COMMENT 'Additional notification data - used in /api/v1/notifications/*',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Read timestamp - used in /api/v1/notifications/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User notifications - used by notification management APIs';

-- Client notifications table - Client-specific notifications
-- APIs: /api/v1/notifications/* (client-related)
-- Used by: NotificationController
CREATE TABLE `client_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/notifications/*',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Notification type - used in /api/v1/notifications/*',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Notification title - used in /api/v1/notifications/*',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Notification message - used in /api/v1/notifications/*',
  `data` json DEFAULT NULL COMMENT 'Additional notification data - used in /api/v1/notifications/*',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Read timestamp - used in /api/v1/notifications/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_notifications_client_id_foreign` (`client_id`),
  CONSTRAINT `client_notifications_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Client notifications - used by notification management APIs';

-- =============================================================================
-- PORTFOLIO TABLES
-- =============================================================================

-- Portfolios table - Portfolio data
-- APIs: /api/v1/portfolios/*, /api/v1/clients/*/portfolio
-- Used by: PortfolioController, ClientController
CREATE TABLE `portfolios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Portfolio title - used in /api/v1/portfolios/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Portfolio description - used in /api/v1/portfolios/*',
  `client_id` bigint unsigned DEFAULT NULL COMMENT 'Associated client - used in /api/v1/portfolios/* and /api/v1/clients/*/portfolio',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `portfolios_client_id_foreign` (`client_id`),
  CONSTRAINT `portfolios_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Portfolio data - used by portfolio management APIs';

-- =============================================================================
-- MEDIA TABLES
-- =============================================================================

-- Media table - File attachments
-- APIs: /api/v1/tasks/*/media, /api/v1/portfolios/*/media
-- Used by: TaskController, PortfolioController
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Model type (Task, Portfolio, etc.)',
  `model_id` bigint unsigned NOT NULL COMMENT 'Model ID - used in /api/v1/tasks/*/media and /api/v1/portfolios/*/media',
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Collection name - used in media APIs',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'File name - used in media APIs',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Stored file name - used in media APIs',
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'File MIME type - used in media APIs',
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Storage disk - used in media APIs',
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Conversions disk - used in media APIs',
  `size` bigint unsigned NOT NULL COMMENT 'File size in bytes - used in media APIs',
  `manipulations` json NOT NULL COMMENT 'Image manipulations - used in media APIs',
  `custom_properties` json NOT NULL COMMENT 'Custom properties - used in media APIs',
  `generated_conversions` json NOT NULL COMMENT 'Generated conversions - used in media APIs',
  `responsive_images` json NOT NULL COMMENT 'Responsive images - used in media APIs',
  `order_column` int DEFAULT NULL COMMENT 'Order column - used in media APIs',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File attachments - used by media management APIs';

-- =============================================================================
-- SETTINGS TABLES
-- =============================================================================

-- Settings table - Application settings
-- APIs: /api/v1/settings/*
-- Used by: SettingsController
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Setting key - used in /api/v1/settings/*',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT 'Setting value - used in /api/v1/settings/*',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Setting group - used in /api/v1/settings/group/*',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'string' COMMENT 'Setting type - used in /api/v1/settings/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Setting description - used in /api/v1/settings/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Application settings - used by settings management APIs';

-- =============================================================================
-- WORKSPACE TABLES
-- =============================================================================

-- Workspaces table - Workspace data
-- APIs: /api/v1/projects/* (workspace assignment)
-- Used by: ProjectController
CREATE TABLE `workspaces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Workspace name - used in /api/v1/projects/*',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Workspace description - used in /api/v1/projects/*',
  `is_primary` tinyint NOT NULL DEFAULT '0' COMMENT 'Is primary workspace - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workspace data - used by project management APIs';

-- Workspace user table - Workspace-user relationships
-- APIs: /api/v1/projects/* (workspace access)
-- Used by: ProjectController
CREATE TABLE `workspace_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workspace_id` bigint unsigned NOT NULL COMMENT 'Workspace ID - used in /api/v1/projects/*',
  `user_id` bigint unsigned NOT NULL COMMENT 'User ID - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workspace_user_workspace_id_foreign` (`workspace_id`),
  KEY `workspace_user_user_id_foreign` (`user_id`),
  CONSTRAINT `workspace_user_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workspace_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workspace-user relationships - used by project management APIs';

-- Workspace client table - Workspace-client relationships
-- APIs: /api/v1/projects/* (workspace access)
-- Used by: ProjectController
CREATE TABLE `workspace_client` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workspace_id` bigint unsigned NOT NULL COMMENT 'Workspace ID - used in /api/v1/projects/*',
  `client_id` bigint unsigned NOT NULL COMMENT 'Client ID - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workspace_client_workspace_id_foreign` (`workspace_id`),
  KEY `workspace_client_client_id_foreign` (`client_id`),
  CONSTRAINT `workspace_client_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workspace_client_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workspace-client relationships - used by project management APIs';

-- =============================================================================
-- TAGS TABLES
-- =============================================================================

-- Tags table - Tag definitions
-- APIs: /api/v1/tags/*
-- Used by: TagController
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Tag name - used in /api/v1/tags/*',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tag color (hex) - used in /api/v1/tags/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tag definitions - used by tag management APIs';

-- Project tag table - Project-tag relationships
-- APIs: /api/v1/projects/* (tag assignment)
-- Used by: ProjectController
CREATE TABLE `project_tag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL COMMENT 'Project ID - used in /api/v1/projects/*',
  `tag_id` bigint unsigned NOT NULL COMMENT 'Tag ID - used in /api/v1/projects/*',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_tag_project_id_foreign` (`project_id`),
  KEY `project_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `project_tag_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project-tag relationships - used by project management APIs';

-- =============================================================================
-- MESSAGING TABLES (CHATIFY)
-- =============================================================================

-- Chatify messages table - Internal messaging
-- APIs: Internal messaging system
-- Used by: ChatifyMessenger
CREATE TABLE `ch_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_id` bigint unsigned NOT NULL COMMENT 'Sender user ID',
  `to_id` bigint unsigned NOT NULL COMMENT 'Recipient user ID',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Message content',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Message attachment',
  `workspace_id` bigint unsigned DEFAULT NULL COMMENT 'Workspace ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ch_messages_from_id_foreign` (`from_id`),
  KEY `ch_messages_to_id_foreign` (`to_id`),
  KEY `ch_messages_workspace_id_foreign` (`workspace_id`),
  CONSTRAINT `ch_messages_from_id_foreign` FOREIGN KEY (`from_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ch_messages_to_id_foreign` FOREIGN KEY (`to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ch_messages_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Internal messaging - used by Chatify messaging system';

-- Chatify favorites table - Message favorites
-- APIs: Internal messaging system
-- Used by: ChatifyMessenger
CREATE TABLE `ch_favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'User ID',
  `favorite_id` bigint unsigned NOT NULL COMMENT 'Favorite user ID',
  `workspace_id` bigint unsigned DEFAULT NULL COMMENT 'Workspace ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ch_favorites_user_id_foreign` (`user_id`),
  KEY `ch_favorites_favorite_id_foreign` (`favorite_id`),
  KEY `ch_favorites_workspace_id_foreign` (`workspace_id`),
  CONSTRAINT `ch_favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ch_favorites_favorite_id_foreign` FOREIGN KEY (`favorite_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ch_favorites_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Message favorites - used by Chatify messaging system';

-- =============================================================================
-- SYSTEM TABLES
-- =============================================================================

-- Failed jobs table - Queue job failures
-- APIs: Internal queue system
-- Used by: Laravel Queue system
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE COMMENT 'Job UUID',
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Queue connection',
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Queue name',
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Job payload',
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Exception details',
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Failure timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Failed queue jobs - used by Laravel Queue system';

-- =============================================================================
-- COMMIT TRANSACTION
-- =============================================================================

COMMIT;
