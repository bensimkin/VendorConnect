-- VendorConnect Database Schema
-- Generated from Laravel Migrations

-- Users table
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `country_code` varchar(10) NULL DEFAULT NULL,
  `phone` varchar(20) NULL DEFAULT NULL,
  `address` text NULL DEFAULT NULL,
  `city` varchar(255) NULL DEFAULT NULL,
  `state` varchar(255) NULL DEFAULT NULL,
  `country` varchar(255) NULL DEFAULT NULL,
  `zip` varchar(20) NULL DEFAULT NULL,
  `dob` date NULL DEFAULT NULL,
  `doj` date NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `admin_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_email_index` (`email`),
  KEY `users_admin_id_foreign` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clients table
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NULL DEFAULT NULL,
  `country_code` varchar(10) NULL DEFAULT NULL,
  `company` varchar(255) NULL DEFAULT NULL,
  `address` text NULL DEFAULT NULL,
  `city` varchar(255) NULL DEFAULT NULL,
  `state` varchar(255) NULL DEFAULT NULL,
  `country` varchar(255) NULL DEFAULT NULL,
  `zip` varchar(20) NULL DEFAULT NULL,
  `photo` varchar(255) NULL DEFAULT NULL,
  `dob` date NULL DEFAULT NULL,
  `doj` date NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `website` varchar(255) NULL DEFAULT NULL,
  `notes` text NULL DEFAULT NULL,
  `internal_purpose` text NULL DEFAULT NULL,
  `acct_create_mail_sent` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_mail_sent` tinyint(1) NOT NULL DEFAULT 0,
  `client_note` text NULL DEFAULT NULL,
  `admin_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_email_index` (`email`),
  KEY `clients_admin_id_foreign` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects table
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `status_id` bigint(20) unsigned NULL DEFAULT NULL,
  `budget` decimal(10,2) NULL DEFAULT NULL,
  `start_date` date NULL DEFAULT NULL,
  `end_date` date NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned NULL DEFAULT NULL,
  `client_id` bigint(20) unsigned NULL DEFAULT NULL,
  `workspace_id` bigint(20) unsigned NULL DEFAULT NULL,
  `admin_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_by` varchar(255) NULL DEFAULT NULL,
  `priority_id` bigint(20) unsigned NULL DEFAULT NULL,
  `note` text NULL DEFAULT NULL,
  `task_accessibility` varchar(255) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_status_id_foreign` (`status_id`),
  KEY `projects_user_id_foreign` (`user_id`),
  KEY `projects_client_id_foreign` (`client_id`),
  KEY `projects_workspace_id_foreign` (`workspace_id`),
  KEY `projects_admin_id_foreign` (`admin_id`),
  KEY `projects_priority_id_foreign` (`priority_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks table
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `status_id` bigint(20) unsigned NULL DEFAULT NULL,
  `task_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `template_id` bigint(20) unsigned NULL DEFAULT NULL,
  `project_id` bigint(20) unsigned NULL DEFAULT NULL,
  `start_date` date NULL DEFAULT NULL,
  `close_deadline` date NULL DEFAULT NULL,
  `end_date` date NULL DEFAULT NULL,
  `standard_brief` text NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned NULL DEFAULT NULL,
  `admin_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_by` varchar(255) NULL DEFAULT NULL,
  `priority_id` bigint(20) unsigned NULL DEFAULT NULL,
  `note` text NULL DEFAULT NULL,
  `deliverable_quantity` int(11) NULL DEFAULT NULL,
  `is_repeating` tinyint(1) NOT NULL DEFAULT 0,
  `repeat_frequency` varchar(50) NULL DEFAULT NULL,
  `repeat_interval` int(11) NULL DEFAULT NULL,
  `repeat_until` date NULL DEFAULT NULL,
  `repeat_active` tinyint(1) NOT NULL DEFAULT 1,
  `parent_task_id` bigint(20) unsigned NULL DEFAULT NULL,
  `last_repeated_at` timestamp NULL DEFAULT NULL,
  `workspace_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_status_id_foreign` (`status_id`),
  KEY `tasks_task_type_id_foreign` (`task_type_id`),
  KEY `tasks_template_id_foreign` (`template_id`),
  KEY `tasks_project_id_foreign` (`project_id`),
  KEY `tasks_user_id_foreign` (`user_id`),
  KEY `tasks_admin_id_foreign` (`admin_id`),
  KEY `tasks_priority_id_foreign` (`priority_id`),
  KEY `tasks_parent_task_id_foreign` (`parent_task_id`),
  KEY `tasks_workspace_id_foreign` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project-Client relationship table
CREATE TABLE `client_project` (
  `client_id` bigint(20) unsigned NOT NULL,
  `project_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`client_id`, `project_id`),
  KEY `client_project_client_id_foreign` (`client_id`),
  KEY `client_project_project_id_foreign` (`project_id`),
  CONSTRAINT `client_project_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_project_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project-User relationship table
CREATE TABLE `project_user` (
  `project_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`project_id`, `user_id`),
  KEY `project_user_project_id_foreign` (`project_id`),
  KEY `project_user_user_id_foreign` (`user_id`),
  CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task-User relationship table
CREATE TABLE `task_user` (
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`task_id`, `user_id`),
  KEY `task_user_task_id_foreign` (`task_id`),
  KEY `task_user_user_id_foreign` (`user_id`),
  CONSTRAINT `task_user_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task-Client relationship table
CREATE TABLE `client_task` (
  `client_id` bigint(20) unsigned NOT NULL,
  `task_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`client_id`, `task_id`),
  KEY `client_task_client_id_foreign` (`client_id`),
  KEY `client_task_task_id_foreign` (`task_id`),
  CONSTRAINT `client_task_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_task_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Status table
CREATE TABLE `statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(7) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `statuses_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Priorities table
CREATE TABLE `priorities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(7) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priorities_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Types table
CREATE TABLE `task_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Brief Templates table
CREATE TABLE `task_brief_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `standard_brief` text NULL DEFAULT NULL,
  `deliverable_quantity` int(11) NULL DEFAULT NULL,
  `task_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_brief_templates_task_type_id_foreign` (`task_type_id`),
  CONSTRAINT `task_brief_templates_task_type_id_foreign` FOREIGN KEY (`task_type_id`) REFERENCES `task_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Brief Questions table
CREATE TABLE `task_brief_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'text',
  `options` json NULL DEFAULT NULL,
  `task_brief_template_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_brief_questions_task_brief_template_id_foreign` (`task_brief_template_id`),
  CONSTRAINT `task_brief_questions_task_brief_template_id_foreign` FOREIGN KEY (`task_brief_template_id`) REFERENCES `task_brief_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Brief Checklists table
CREATE TABLE `task_brief_checklists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item` varchar(255) NOT NULL,
  `task_brief_template_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_brief_checklists_task_brief_template_id_foreign` (`task_brief_template_id`),
  CONSTRAINT `task_brief_checklists_task_brief_template_id_foreign` FOREIGN KEY (`task_brief_template_id`) REFERENCES `task_brief_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Question Answers table
CREATE TABLE `question_answered` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `answer` text NULL DEFAULT NULL,
  `task_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_answered_task_id_foreign` (`task_id`),
  CONSTRAINT `question_answered_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Checklist Answers table
CREATE TABLE `checklist_answered` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item` varchar(255) NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `task_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist_answered_task_id_foreign` (`task_id`),
  CONSTRAINT `checklist_answered_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Deliverables table
CREATE TABLE `task_deliverables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `file_path` varchar(255) NULL DEFAULT NULL,
  `link_url` varchar(255) NULL DEFAULT NULL,
  `task_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_deliverables_task_id_foreign` (`task_id`),
  CONSTRAINT `task_deliverables_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client Credentials table
CREATE TABLE `client_credentials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `username` varchar(255) NULL DEFAULT NULL,
  `password` text NOT NULL,
  `url` varchar(255) NULL DEFAULT NULL,
  `notes` text NULL DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_credentials_client_id_foreign` (`client_id`),
  CONSTRAINT `client_credentials_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personal Access Tokens table
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL UNIQUE,
  `abilities` text NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles table
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions table
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model Has Roles table
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model Has Permissions table
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Has Permissions table
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NULL DEFAULT NULL,
  `group` varchar(255) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client Notifications table
CREATE TABLE `client_notifications` (
  `client_id` bigint(20) unsigned NOT NULL,
  `notification_id` char(36) NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`client_id`, `notification_id`),
  KEY `client_notifications_client_id_foreign` (`client_id`),
  KEY `client_notifications_notification_id_foreign` (`notification_id`),
  CONSTRAINT `client_notifications_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_notifications_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chatify Messages table
CREATE TABLE `ch_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_id` bigint(20) unsigned NOT NULL,
  `to_id` bigint(20) unsigned NOT NULL,
  `body` text NOT NULL,
  `attachment` varchar(255) NULL DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ch_messages_from_id_foreign` (`from_id`),
  KEY `ch_messages_to_id_foreign` (`to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workspaces table
CREATE TABLE `workspaces` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workspace User table
CREATE TABLE `workspace_user` (
  `workspace_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`workspace_id`, `user_id`),
  KEY `workspace_user_workspace_id_foreign` (`workspace_id`),
  KEY `workspace_user_user_id_foreign` (`user_id`),
  CONSTRAINT `workspace_user_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workspace_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workspace Client table
CREATE TABLE `workspace_client` (
  `workspace_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`workspace_id`, `client_id`),
  KEY `workspace_client_workspace_id_foreign` (`workspace_id`),
  KEY `workspace_client_client_id_foreign` (`client_id`),
  CONSTRAINT `workspace_client_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workspace_client_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media table (for file uploads)
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) NULL DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NULL DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) NULL DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int(10) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`, `model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolios table
CREATE TABLE `portfolios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `task_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `portfolios_client_id_foreign` (`client_id`),
  KEY `portfolios_task_id_foreign` (`task_id`),
  CONSTRAINT `portfolios_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portfolios_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client User table (for client-user relationships)
CREATE TABLE `client_user` (
  `client_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `enable_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`client_id`, `user_id`),
  KEY `client_user_client_id_foreign` (`client_id`),
  KEY `client_user_user_id_foreign` (`user_id`),
  CONSTRAINT `client_user_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints for existing relationships
ALTER TABLE `users` ADD CONSTRAINT `users_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `clients` ADD CONSTRAINT `clients_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `projects` ADD CONSTRAINT `projects_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL;
ALTER TABLE `projects` ADD CONSTRAINT `projects_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `projects` ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;
ALTER TABLE `projects` ADD CONSTRAINT `projects_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE SET NULL;
ALTER TABLE `projects` ADD CONSTRAINT `projects_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `projects` ADD CONSTRAINT `projects_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_task_type_id_foreign` FOREIGN KEY (`task_type_id`) REFERENCES `task_types` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_brief_templates` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_parent_task_id_foreign` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE SET NULL;
ALTER TABLE `ch_messages` ADD CONSTRAINT `ch_messages_from_id_foreign` FOREIGN KEY (`from_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `ch_messages` ADD CONSTRAINT `ch_messages_to_id_foreign` FOREIGN KEY (`to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
