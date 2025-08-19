-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 27, 2024 at 01:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taskifysaasBlank`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_id` bigint(20) NOT NULL,
  `actor_type` varchar(56) NOT NULL,
  `type_id` bigint(20) NOT NULL,
  `type` varchar(56) NOT NULL,
  `parent_type_id` bigint(20) DEFAULT NULL,
  `parent_type` varchar(56) DEFAULT NULL,
  `activity` varchar(56) NOT NULL,
  `message` varchar(512) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `amount` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `allowance_payslip`
--

CREATE TABLE `allowance_payslip` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `allowance_id` bigint(20) UNSIGNED NOT NULL,
  `payslip_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ch_favorites`
--

CREATE TABLE `ch_favorites` (
  `id` char(36) NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `favorite_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ch_messages`
--

CREATE TABLE `ch_messages` (
  `id` char(36) NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `from_id` bigint(20) NOT NULL,
  `to_id` bigint(20) NOT NULL,
  `body` varchar(5000) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `doj` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `photo` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `lang` varchar(28) NOT NULL DEFAULT 'en',
  `remember_token` text DEFAULT NULL,
  `email_verification_mail_sent` tinyint(4) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `internal_purpose` tinyint(4) NOT NULL DEFAULT 0,
  `acct_create_mail_sent` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_meeting`
--

CREATE TABLE `client_meeting` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `meeting_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_notifications`
--

CREATE TABLE `client_notifications` (
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_project`
--

CREATE TABLE `client_project` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_workspace`
--

CREATE TABLE `client_workspace` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `contract_type_id` bigint(20) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `promisor_sign` text DEFAULT NULL,
  `promisee_sign` text DEFAULT NULL,
  `created_by` varchar(56) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contract_types`
--

CREATE TABLE `contract_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(512) NOT NULL DEFAULT 'amount',
  `percentage` double DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deduction_payslip`
--

CREATE TABLE `deduction_payslip` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deduction_id` bigint(20) UNSIGNED NOT NULL,
  `payslip_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimates_invoices`
--

CREATE TABLE `estimates_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `zip_code` int(11) DEFAULT NULL,
  `phone` bigint(20) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `note` longtext DEFAULT NULL,
  `personal_note` longtext DEFAULT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `total` double NOT NULL,
  `tax_amount` double DEFAULT NULL,
  `final_total` double NOT NULL,
  `created_by` varchar(56) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimates_invoice_item`
--

CREATE TABLE `estimates_invoice_item` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `estimates_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `qty` double NOT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rate` double NOT NULL,
  `tax_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `expense_type_id` bigint(20) UNSIGNED NOT NULL,
  `amount` double NOT NULL,
  `note` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_by` varchar(56) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_types`
--

CREATE TABLE `expense_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(2, 'Hindi', 'hn', '2024-03-28 12:49:57', '2024-03-28 12:49:57'),
(8, 'English', 'en', '2024-07-12 04:19:44', '2024-07-12 04:19:44');

-- --------------------------------------------------------

--
-- Table structure for table `leave_editors`
--

CREATE TABLE `leave_editors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `visible_to_all` tinyint(4) NOT NULL DEFAULT 0,
  `action_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `from_time` time DEFAULT NULL,
  `to_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_request_visibility`
--

CREATE TABLE `leave_request_visibility` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `leave_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date_time` timestamp NULL DEFAULT NULL,
  `end_date_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meeting_user`
--

CREATE TABLE `meeting_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `meeting_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_01_05_044027_create_clients_table', 2),
(6, '2023_01_09_041709_create_projects_table', 3),
(7, '2023_01_12_095035_create_tasks_table', 4),
(8, '2023_01_25_061517_create_project_user_table', 5),
(9, '2023_01_27_044336_create_project_client_table', 6),
(10, '2023_01_30_050742_create_task_user_table', 7),
(11, '2023_01_30_060853_create_task_user_table', 8),
(12, '2023_03_02_051733_create_todo_table', 9),
(13, '2023_03_06_043616_create_status_table', 9),
(14, '2023_03_14_045106_create_permission_tables', 10),
(15, '2023_06_14_074411_create_settings_table', 11),
(16, '2023_06_15_074941_create_meetings_table', 12),
(19, '2023_06_15_073618_create_meeting_users_table', 13),
(21, '2023_06_15_074054_create_meeting_clients_table', 14),
(22, '2023_06_15_111007_create_meetings_table', 15),
(23, '2023_06_15_121717_create_meeting_user_table', 16),
(24, '2023_06_15_121752_create_client_meeting_table', 16),
(25, '2023_06_16_999999_add_active_status_to_users', 17),
(26, '2023_06_16_999999_add_avatar_to_users', 17),
(27, '2023_06_16_999999_add_dark_mode_to_users', 17),
(28, '2023_06_16_999999_add_messenger_color_to_users', 17),
(29, '2023_06_16_999999_create_chatify_favorites_table', 17),
(30, '2023_06_16_999999_create_chatify_messages_table', 17),
(34, '2023_06_16_132714_create_workspaces_table', 18),
(36, '2023_06_16_133749_create_workspace_client_table', 18),
(37, '2023_06_16_133505_create_workspace_user_table', 19),
(39, '2023_06_19_100748_alter_projects_table_add_column_workspace_id', 20),
(40, '2023_06_19_111816_alter_projects_table_add_column_created_by', 21),
(41, '2023_06_19_124749_alter_tasks_table_add_columns', 22),
(42, '2023_06_19_134537_alter_meeting_and_todo_tables_add_columns', 23),
(44, '2023_06_20_152434_alter_ch_messages_add_column_workspace_id', 24),
(45, '2023_06_24_162358_alter_ch_messages_add_column_workspace_id', 25),
(46, '2023_06_28_101921_alter_ch_favorites_table_add_column_workspace_id', 25),
(47, '2023_06_29_105758_create_languages_table', 26),
(48, '2023_08_22_124156_create_tags_table', 27),
(49, '2023_08_22_175355_create_project_tags_table', 28),
(50, '2023_08_22_180431_create_project_tag_table', 29),
(51, '2023_08_22_181203_create_project_tag_table', 30),
(52, '2023_08_24_050550_create_project_tag_table', 31),
(53, '2023_09_11_140028_create_todos_table', 32),
(54, '2023_09_11_085224_create_todos_table', 33),
(55, '2023_09_19_063727_create_leave_requests_table', 34),
(56, '2023_09_19_134017_create_leave_editors_table', 35),
(59, '2023_09_20_124013_create_contract_types_table', 36),
(60, '2023_09_20_123424_create_contracts_table', 37),
(61, '2023_09_25_084705_create_payment_methods_table', 38),
(62, '2023_09_25_070729_create_payslips_table', 39),
(63, '2023_09_25_094532_create_allowances_table', 40),
(64, '2023_09_25_094632_create_deductions_table', 40),
(65, '2023_09_27_124345_create_allowance_payslip_table', 41),
(66, '2023_09_27_124747_create_deduction_payslip_table', 41),
(67, '2023_09_29_131131_create_notes_table', 42),
(68, '2023_10_03_115025_create_updates_table', 43),
(69, '2023_10_03_115438_create_updates_table', 44),
(70, '2023_12_18_100056_create_time_trackers_table', 45),
(71, '2023_12_19_044413_create_time_trackers_table', 46),
(74, '2023_12_19_065522_create_time_trackers_table', 47),
(75, '2024_01_17_121328_create_media_table', 48),
(76, '2024_02_14_120904_create_plans_table', 49),
(77, '2024_02_15_090930_create_subscriptions_table', 49),
(78, '2024_02_22_060640_create_admins_table', 49),
(79, '2024_02_22_093044_add_admin_id_to_multiple_tables', 49),
(80, '2024_02_22_104645_create_team_members_table', 50),
(81, '2024_02_23_053155_create_transactions_table', 51),
(82, '2024_01_29_135126_create_taxes_table', 52),
(83, '2024_01_30_070848_create_units_table', 52),
(84, '2024_01_31_045129_create_items_table', 52),
(85, '2024_02_02_142029_create_estimates_invoices_table', 52),
(86, '2024_02_02_162343_create_estimates_invoice_item_table', 52),
(87, '2024_02_12_093738_create_expense_types_table', 52),
(88, '2024_02_12_094118_create_expenses_table', 52),
(89, '2024_02_16_043457_create_payments_table', 52),
(90, '2024_02_26_101512_create_milestones_table', 52),
(91, '2024_03_18_044224_add_status_to_transactions_table', 52),
(92, '2024_06_24_054902_create_notifications_table', 53),
(93, ' 2024_06_24_054902_create_client_notifications_table ', 54),
(94, '2024_03_05_092139_create_notification_user_table', 54),
(95, '2024_03_11_120312_create_templates_table', 54),
(96, '2024_06_26_103749_add_country_code_in_users_and_clients__tables', 55),
(97, '2024_07_02_041735_create_priorities_table', 56),
(98, '2024_07_02_042519_add_priority_id_in_project_and_task_tables', 56),
(99, '2024_07_02_114130_create_role_status_table', 57),
(100, '2024_07_03_072616_create_user_client_preferences_table', 58),
(101, '2024_06_25_054902_create_client_notifications_table', 59),
(102, '2024_07_08_110247_add_from_time_and_to_time_in_leave_requests', 60),
(103, '2024_07_08_111326_create_leave_request_visibility_table', 60),
(105, '2024_07_09_103437_add_internal_purpose_and_acc_mail_field_in_clients', 61),
(106, '2024_07_09_110959_make_fields_nullable_in_clients_table', 61),
(107, '2024_07_10_040334_description_null_in_many_tables', 62),
(109, '2024_07_12_043901_add_enable_notifications_in_user_client_preferences', 63),
(110, '2024_07_15_051212_add_is_primary_field_in_workspaces_table', 64);

-- --------------------------------------------------------

--
-- Table structure for table `milestones`
--

CREATE TABLE `milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `cost` double NOT NULL,
  `progress` double NOT NULL DEFAULT 0,
  `description` longtext DEFAULT NULL,
  `created_by` varchar(56) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `creator_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `from_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `type_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_user`
--

CREATE TABLE `notification_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` double NOT NULL,
  `payment_date` date NOT NULL,
  `note` text DEFAULT NULL,
  `created_by` varchar(56) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `month` varchar(128) NOT NULL,
  `working_days` double NOT NULL,
  `lop_days` double NOT NULL,
  `paid_days` double NOT NULL,
  `basic_salary` double NOT NULL,
  `leave_deduction` double NOT NULL,
  `ot_hours` double NOT NULL DEFAULT 0,
  `ot_rate` double NOT NULL DEFAULT 0,
  `ot_payment` double NOT NULL DEFAULT 0,
  `total_allowance` double NOT NULL DEFAULT 0,
  `incentives` double NOT NULL DEFAULT 0,
  `bonus` double NOT NULL DEFAULT 0,
  `total_earnings` double NOT NULL,
  `total_deductions` double NOT NULL DEFAULT 0,
  `net_pay` double NOT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `note` text DEFAULT NULL,
  `created_by` varchar(56) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'edit_tasks', 'web', '2023-03-14 00:32:57', '2023-03-14 00:32:57'),
(3, 'delete_tasks', 'web', '2023-03-14 01:09:18', '2023-03-14 01:09:18'),
(4, 'edit_projects', 'web', '2023-03-14 03:22:52', '2023-03-14 03:22:52'),
(5, 'delete_projects', 'web', '2023-03-14 03:23:43', '2023-03-14 03:23:43'),
(6, 'edit_clients', 'web', '2023-03-14 03:23:53', '2023-03-14 03:23:53'),
(7, 'delete_clients', 'web', '2023-03-14 03:24:01', '2023-03-14 03:24:01'),
(8, 'create_projects', 'web', '2023-03-14 23:12:29', '2023-03-14 23:12:29'),
(9, 'create_tasks', 'web', '2023-03-14 23:12:34', '2023-03-14 23:12:34'),
(10, 'create_clients', 'web', '2023-03-14 23:12:38', '2023-03-14 23:12:38'),
(11, 'edit_users', 'web', '2023-03-17 00:51:15', '2023-03-17 00:51:15'),
(12, 'delete_users', 'web', '2023-03-17 00:57:41', '2023-03-17 00:57:41'),
(13, 'manage_users', 'web', '2023-03-27 00:04:49', '2023-03-27 00:04:49'),
(14, 'manage_projects', 'web', '2023-03-27 00:04:54', '2023-03-27 00:04:54'),
(15, 'manage_tasks', 'web', '2023-03-27 00:04:58', '2023-03-27 00:04:58'),
(16, 'manage_clients', 'web', '2023-03-27 00:05:02', '2023-03-27 00:05:02'),
(17, 'create_users', 'web', '2023-03-27 00:19:25', '2023-03-27 00:19:25'),
(22, 'create_workspaces', 'web', '2023-08-12 10:48:04', '2023-08-12 10:48:04'),
(23, 'manage_workspaces', 'web', '2023-08-12 10:48:04', '2023-08-12 10:48:04'),
(24, 'edit_workspaces', 'web', '2023-08-12 10:48:04', '2023-08-12 10:48:04'),
(25, 'delete_workspaces', 'web', '2023-08-12 10:48:04', '2023-08-12 10:48:04'),
(26, 'create_meetings', 'web', '2023-08-12 10:50:22', '2023-08-12 10:50:22'),
(27, 'manage_meetings', 'web', '2023-08-12 10:50:22', '2023-08-12 10:50:22'),
(28, 'edit_meetings', 'web', '2023-08-12 10:50:22', '2023-08-12 10:50:22'),
(29, 'delete_meetings', 'web', '2023-08-12 10:50:22', '2023-08-12 10:50:22'),
(37, 'delete_projects', 'client', '2023-09-08 11:57:55', '2023-09-08 11:57:55'),
(38, 'manage_projects', 'client', '2023-09-08 11:57:55', '2023-09-08 11:57:55'),
(39, 'create_tasks', 'client', '2023-09-08 12:32:02', '2023-09-08 12:32:02'),
(40, 'create_projects', 'client', '2023-09-08 12:33:36', '2023-09-08 12:33:36'),
(41, 'manage_tasks', 'client', '2023-09-08 12:33:48', '2023-09-08 12:33:48'),
(42, 'edit_tasks', 'client', '2023-09-08 12:33:59', '2023-09-08 12:33:59'),
(43, 'delete_tasks', 'client', '2023-09-08 12:34:13', '2023-09-08 12:34:13'),
(44, 'create_users', 'client', '2023-09-08 12:34:25', '2023-09-08 12:34:25'),
(45, 'manage_users', 'client', '2023-09-08 12:34:37', '2023-09-08 12:34:37'),
(46, 'edit_users', 'client', '2023-09-08 12:34:56', '2023-09-08 12:34:56'),
(47, 'delete_users', 'client', '2023-09-08 12:35:08', '2023-09-08 12:35:08'),
(48, 'create_clients', 'client', '2023-09-08 12:35:25', '2023-09-08 12:35:25'),
(49, 'manage_clients', 'client', '2023-09-08 12:35:37', '2023-09-08 12:35:37'),
(50, 'edit_clients', 'client', '2023-09-08 12:35:49', '2023-09-08 12:35:49'),
(51, 'delete_clients', 'client', '2023-09-08 12:36:01', '2023-09-08 12:36:01'),
(52, 'create_workspaces', 'client', '2023-09-08 12:36:17', '2023-09-08 12:36:17'),
(53, 'manage_workspaces', 'client', '2023-09-08 12:36:34', '2023-09-08 12:36:34'),
(54, 'edit_workspaces', 'client', '2023-09-08 12:36:45', '2023-09-08 12:36:45'),
(55, 'delete_workspaces', 'client', '2023-09-08 12:37:01', '2023-09-08 12:37:01'),
(56, 'create_meetings', 'client', '2023-09-08 12:37:16', '2023-09-08 12:37:16'),
(57, 'manage_meetings', 'client', '2023-09-08 12:37:29', '2023-09-08 12:37:29'),
(58, 'edit_meetings', 'client', '2023-09-08 12:37:42', '2023-09-08 12:37:42'),
(59, 'delete_meetings', 'client', '2023-09-08 12:37:55', '2023-09-08 12:37:55'),
(60, 'edit_projects', 'client', '2023-09-08 13:10:48', '2023-09-08 13:10:48'),
(61, 'create_contracts', 'client', '2023-09-23 23:32:01', '2023-09-23 23:32:01'),
(62, 'manage_contracts', 'client', '2023-09-23 23:32:44', '2023-09-23 23:32:44'),
(63, 'edit_contracts', 'client', '2023-09-23 23:32:56', '2023-09-23 23:32:56'),
(64, 'delete_contracts', 'client', '2023-09-23 23:33:04', '2023-09-23 23:33:04'),
(65, 'delete_contracts', 'web', '2023-09-23 23:33:16', '2023-09-23 23:33:16'),
(66, 'edit_contracts', 'web', '2023-09-23 23:33:26', '2023-09-23 23:33:26'),
(67, 'manage_contracts', 'web', '2023-09-23 23:33:37', '2023-09-23 23:33:37'),
(68, 'create_contracts', 'web', '2023-09-23 23:33:50', '2023-09-23 23:33:50'),
(69, 'create_timesheet', 'web', '2023-12-19 07:13:52', '2023-12-19 07:13:52'),
(70, 'delete_timesheet', 'web', '2023-12-19 07:13:55', '2023-12-19 07:13:55'),
(71, 'manage_timesheet', 'web', '2023-12-19 07:13:55', '2023-12-19 07:13:55'),
(72, 'create_timesheet', 'client', '2023-12-19 07:15:11', '2023-12-19 07:15:11'),
(73, 'delete_timesheet', 'client', '2023-12-19 07:15:11', '2023-12-19 07:15:11'),
(74, 'manage_timesheet', 'client', '2023-12-19 07:15:11', '2023-12-19 07:15:11'),
(75, 'create_payslips', 'client', '2023-12-22 05:46:34', '2023-12-22 05:46:34'),
(76, 'edit_payslips', 'client', '2023-12-22 05:46:35', '2023-12-22 05:46:35'),
(77, 'delete_payslips', 'client', '2023-12-22 05:46:35', '2023-12-22 05:46:35'),
(78, 'manage_payslips', 'client', '2023-12-22 05:46:35', '2023-12-22 05:46:35'),
(79, 'create_payslips', 'web', '2023-12-22 05:46:52', '2023-12-22 05:46:52'),
(80, 'edit_payslips', 'web', '2023-12-22 05:46:53', '2023-12-22 05:46:53'),
(81, 'delete_payslips', 'web', '2023-12-22 05:46:53', '2023-12-22 05:46:53'),
(82, 'manage_payslips', 'web', '2023-12-22 05:46:53', '2023-12-22 05:46:53'),
(83, 'access_all_data', 'web', '2024-01-08 07:22:04', '2024-01-08 13:47:12'),
(85, 'access_all_data', 'client', '2024-01-08 07:22:04', '2024-01-08 13:47:12'),
(86, 'manage_activity_log', 'web', '2024-01-15 13:46:23', '2024-01-15 13:46:23'),
(87, 'delete_activity_log', 'web', '2024-01-15 13:46:23', '2024-01-15 13:46:23'),
(88, 'manage_activity_log', 'client', '2024-01-15 13:46:23', '2024-01-15 13:46:23'),
(89, 'delete_activity_log', 'client', '2024-01-15 13:46:23', '2024-01-15 13:46:23'),
(90, 'manage_estimates_invoices', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(91, 'create_estimates_invoices', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(92, 'edit_estimates_invoices', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(93, 'delete_estimates_invoices', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(94, 'manage_estimates_invoices', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(95, 'create_estimates_invoices', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(96, 'edit_estimates_invoices', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(97, 'delete_estimates_invoices', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(98, 'manage_expenses', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(99, 'create_expenses', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(100, 'edit_expenses', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(101, 'delete_expenses', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(102, 'manage_expenses', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(103, 'create_expenses', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(104, 'edit_expenses', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(105, 'delete_expenses', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(106, 'manage_milestones', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(107, 'create_milestones', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(108, 'edit_milestones', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(109, 'delete_milestones', 'web', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(110, 'manage_milestones', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(111, 'create_milestones', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(112, 'edit_milestones', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(113, 'delete_milestones', 'client', '2024-04-02 05:45:31', '2024-04-02 05:45:31'),
(114, 'manage_system_notifications', 'client', '2024-03-05 01:27:10', '2024-03-05 01:27:10'),
(115, 'delete_system_notifications', 'client', '2024-03-05 01:27:10', '2024-03-05 01:27:10'),
(116, 'manage_system_notifications', 'web', '2024-03-05 01:27:21', '2024-03-05 01:27:21'),
(117, 'delete_system_notifications', 'web', '2024-03-05 01:27:21', '2024-03-05 01:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `max_projects` int(11) NOT NULL,
  `max_clients` int(11) NOT NULL,
  `max_team_members` int(11) NOT NULL,
  `max_worksapces` int(11) NOT NULL,
  `plan_type` varchar(255) NOT NULL,
  `image` varchar(512) DEFAULT NULL,
  `modules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`modules`)),
  `monthly_price` decimal(10,2) DEFAULT NULL,
  `monthly_discounted_price` decimal(10,2) DEFAULT NULL,
  `yearly_price` decimal(10,2) DEFAULT NULL,
  `yearly_discounted_price` decimal(10,2) DEFAULT NULL,
  `lifetime_price` decimal(10,2) DEFAULT NULL,
  `lifetime_discounted_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

CREATE TABLE `priorities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `priority_id` bigint(20) UNSIGNED DEFAULT NULL,
  `budget` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `is_favorite` tinyint(4) NOT NULL DEFAULT 0,
  `task_accessibility` varchar(28) NOT NULL DEFAULT 'assigned_users',
  `note` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_tag`
--

CREATE TABLE `project_tag` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_user`
--

CREATE TABLE `project_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2023-03-13 23:28:49', '2023-09-24 06:53:41'),
(4, 'superadmin', 'web', NULL, NULL),
(9, 'member', 'web', '2023-03-31 06:21:47', '2023-03-31 06:21:47'),
(21, 'client', 'client', '2023-12-22 07:45:49', '2023-12-22 07:45:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_status`
--

CREATE TABLE `role_status` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `variable` varchar(255) DEFAULT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `variable`, `value`, `created_at`, `updated_at`) VALUES
(5, 'general_settings', '{\"company_title\":\"Taskify SaaS\",\"support_email\":\"info@infinitietech.com\",\"currency_full_form\":\"Indian Rupee\",\"currency_symbol\":\"$\",\"currency_code\":\"USD\",\"currency_symbol_position\":\"before\",\"currency_format\":\"dot_separated\",\"decimal_points_in_currency\":\"1\",\"timezone\":\"Asia\\/Kolkata\",\"date_format\":\"DD-MM-YYYY|d-m-Y\",\"footer_text\":\"<p>made with \\u2764\\ufe0f by <a href=\\\"https:\\/\\/codecanyon.net\\/user\\/infinitietech\\\" target=\\\"_blank\\\" rel=\\\"noopener\\\">Infinitie Technologies<\\/a><\\/p>\",\"company_address\":\"<p>#237, Time Square Empire, Bhuj Kutch - India<\\/p>\",\"full_logo\":\"\",\"half_logo\":\"\",\"favicon\":\"logos\\/Flabc538OBOxo4gIFsSOwsOwyRi2EaSzjIPZ9mIo.png\",\"footer_logo\":\"logos\\/bUnIHGMoqsPbkiiyVzmoRFGplkKXCg7jDATWJbSd.png\"}', '2023-06-14 07:48:25', '2024-06-18 01:24:12'),
(9, 'pusher_settings', '{\"pusher_app_id\":\"\",\"pusher_app_key\":\"\",\"pusher_app_secret\":\"\",\"pusher_app_cluster\":\"ap2\"}', '2023-06-21 05:33:13', '2024-02-22 23:59:52'),
(10, 'email_settings', '{\"email\":\"\",\"password\":\"\",\"smtp_host\":\"smtp.googlemail.com\",\"smtp_port\":\"465\",\"email_content_type\":\"text\",\"smtp_encryption\":\"ssl\"}', '2023-06-21 08:43:07', '2023-11-26 22:08:55'),
(11, 'media_storage_settings', '{\"media_storage_type\":\"local\",\"s3_key\":null,\"s3_secret\":null,\"s3_region\":null,\"s3_bucket\":null}', '2024-01-22 07:03:48', '2024-05-30 10:39:55'),
(12, 'pay_pal_settings', '{\"paypal_client_id\":\"\",\"paypal_secret_key\":\"\",\"payment_mode\":\"sandbox\",\"paypal_business_email\":\"\",\"currency_code\":\"USD\"}', NULL, '2024-06-16 22:39:03'),
(13, 'phone_pe_settings', '{\"merchant_id\":\"\",\"app_id\":\"\",\"salt_index\":\"1\",\"salt_key\":\"\",\"phonepe_mode\":\"sandbox\",\"payment_endpoint_url\":\"http:\\/\\/127.0.0.1:8000\\/master-panel\\/subscription-plan\\/checkout\\/phone_pe-webhook\"}', NULL, '2024-07-23 00:23:49'),
(14, 'stripe_settings', '{\"stripe_publishable_key\":\"\",\"stripe_secret_key\":\"\",\"payment_endpoint_url\":\"Your-Domain\\/master-panel\\/subscription-plan\\/checkout\\/stripe-webhook\",\"stripe_webhook_secret_key\":\"\",\"payment_mode\":\"sandbox\",\"currency_code\":\"INR\"}', NULL, '2024-05-30 10:45:14'),
(15, 'paystack_settings', '{\"paystack_key_id\":\"\",\"paystack_secret_key\":\"\",\"payment_endpoint_url\":\"Your-Domain\\/master-panel\\/subscription-plan\\/checkout\\/paystack-webhook\"}', NULL, '2024-05-30 10:45:18'),
(20, 'refund_policy', '{\"refund_policy\":\"<p><strong>REFUND POLICY<\\/strong><br>The following terms are applicable for any products that You purchased with Us.<br>First of all, we thank you and appreciate your service or product purchase with us on our Website Taskify-SaaS.taskhub.company. Please read this policy carefully as it will give you important information and guidelines about your rights and obligations as our customer, with respect to any purchase or service we provide to you.<\\/p>\\r\\n<p>At Taskify-SaaS, we take pride in the services delivered by us and guarantee your satisfaction with our services and support. We constantly improve and strive to deliver the best accounting, financial or secretarial services through the internet. We make every effort to provide the service to you as per the specifications and timelines mentioned against each service or product purchased by you from Taskify-SaaS, however, if, due for any reason, we are unable to provide to you the service or product you purchased from us, please contact us immediately and we will correct the situation, provide a refund or offer credit that can be used for future Taskify-SaaS orders.<\\/p>\\r\\n<p><strong>You shall be entitled to a refund which shall be subject to the following situations:<\\/strong><br>The Refund shall be only considered in the event there is a clear, visible deficiency with the service or product purchased from Taskify-SaaS. No refund shall be issued if Taskify-SaaS processed the registration\\/application as per the government guidelines and registration is pending on part of a government department or officials. If any government fee, duty, challan, or any other sum is paid in the course of processing your registration application. We will refund the full payment less the government fee paid. (Don&rsquo;t worry no government fee shall be deducted until Government challan or any other payment proof is provided to you)<\\/p>\\r\\n<p>In the event a customer has paid for a service and then requests for a refund only because there was a change of mind, the refund shall not be considered as there is no fault, defect, or onus on Taskify-SaaS. Refund requests shall not be entertained after the work has been shared with you in the event of a change of mind. However, we shall give you the option of using the amount paid for by you, for an alternative service in Taskify-SaaS amounting to the same value and the said amount could be applied in part or whole towards the said new service; and If the request for a refund has been raised 30 (thirty) days after the purchase of a service or product has been completed and the same has been intimated and indicated via email or through any form of communication stating that the work has been completed, then, such refund request shall be deemed invalid and shall not be considered.<\\/p>\\r\\n<p>If the request for the refund has been approved by Taskify-SaaS, the same shall be processed and intimated to you via email. This refund process could take a minimum of 15 (fifteen) business days to process and shall be credited to your bank account accordingly. We shall handle the refund process with care and ensure that the money spent by you is returned to you at the earliest.<\\/p>\\r\\n<p><strong>Fees for Services<\\/strong><br>When the payment of fee is made to Taskify-SaaS, the fees paid in advance is retained by Taskify-SaaS in a client account. Taskify-SaaS will earn the fees upon working on a client&rsquo;s matter. During an engagement, Taskify-SaaS earns fee at different rates and different times depending on the completion of various milestones (e.g. providing client portal access, assigning relationship manager, obtaining DIN, Filing of forms, etc.,). Refund cannot be provided for the earned fee because resources and man-hours spent on delivering the service are non-returnable in nature. Further, we can&rsquo;t refund or credit any money paid to government entities, such as filing fees or taxes, or to other third parties with a role in processing your order. Under any circumstance, Taskify-SaaS shall be liable to refund only up to the fee paid by the client.<\\/p>\\r\\n<p><strong>Change of Service<\\/strong><br>If you want to change the service you ordered for a different one, you must request this change of service within 30 days of purchase. The purchase price of the original service, less any earned fee and money paid to government entities, such as filing fees or taxes, or to other third parties with a role in processing your order, will be credited to your Taskify-SaaS account. You can use the balance credit for any other Taskify-SaaS service.<\\/p>\\r\\n<p><strong>Standard Pricing<\\/strong><br>Taskify-SaaS has a standard pricing policy wherein no additional service fee is requested under any circumstance. However, the standard pricing policy is not applicable for an increase in the total fee paid by the client to Taskify-SaaS due to an increase in the government fee or fee incurred by the client for completion of legal documentation or re-filing of forms with the government due to rejection or resubmission. Taskify-SaaS is not responsible or liable for any other cost incurred by the client related to the completion of the service.<\\/p>\\r\\n<p><strong>Factors outside our Control<\\/strong><br>We cannot guarantee the results or outcome of your particular procedure. For instance, the government may reject a trademark application for legal reasons beyond the scope of Taskify-SaaS service. In some cases, a government backlog or problems with the government platforms (e.g. MCA website, Income Tax website, FSSAI website) can lead to long delays before your process is complete. Similarly, Taskify-SaaS does not guarantee the results or outcomes of the services rendered by our Associates on a Nearest Expert platform, who are not employed by Taskify-SaaS. Problems like these are beyond our control and are not covered by this guarantee or eligible for a refund. Hence, the delay in processing your file by the Government cannot be a reason for the refund.<\\/p>\\r\\n<p><strong>Force Majeure<\\/strong><br>Taskify-SaaS shall not be considered in breach of its Satisfaction Guarantee policy or default under any terms of service, and shall not be liable to the Client for any cessation, interruption, or delay in the performance of its obligations by reason of earthquake, flood, fire, storm, lightning, drought, landslide, hurricane, cyclone, typhoon, tornado, natural disaster, act of God or the public enemy, epidemic, famine or plague, action of a court or public authority, change in law, explosion, war, terrorism, armed conflict, labor strike, lockout, boycott or similar event beyond our reasonable control, whether foreseen or unforeseen (each a &ldquo;Force Majeure Event&rdquo;).<\\/p>\\r\\n<p><strong>Cancellation Fee<\\/strong><br>Since we&rsquo;re incurring costs and dedicating time, manpower, technology resources, and effort to your service or document preparation, our guarantee only covers satisfaction issues caused by Taskify-SaaS &ndash; not changes to your situation or your state of mind. In case you require us to hold the processing of service, we will hold the fee paid on your account until you are ready to commence the service.<\\/p>\\r\\n<p>Before processing any refund, we reserve the right to make the best effort to complete the service. In case, you are not satisfied with the service, a cancellation fee of 20% + earned fee + fee paid to the government would be applicable. In case of a change of service, the cancellation fee would not be applicable.<\\/p>\"}', '2024-03-21 09:17:38', '2024-03-21 10:20:19'),
(21, 'privacy_policy', '{\"privacy_policy\":\"<p>At <strong>Taskify-SaaS<\\/strong> , accessible from http:\\/\\/taskify-saas.taskhub.company\\/, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by Taskify-SaaS and how we use it. If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us.<\\/p>\\r\\n<p>This Privacy Policy applies only to our online activities and is valid for visitors to our website with regards to the information that they shared and\\/or collect in Taskify-SaaS . This policy is not applicable to any information collected offline or via channels other than this website.<\\/p>\\r\\n<p><strong>Consent<\\/strong><br>By using our website, you hereby consent to our Privacy Policy and agree to its terms.<\\/p>\\r\\n<p><strong>Information we collect<\\/strong><br>The personal information that you are asked to provide, and the reasons why you are asked to provide it, will be made clear to you at the point we ask you to provide your personal information. If you contact us directly, we may receive additional information about you such as your name, email address, phone number, the contents of the message and\\/or attachments you may send us, and any other information you may choose to provide. When you register for an Account, we may ask for your contact information, including items such as name, company name, address, email address, and telephone number.<\\/p>\\r\\n<p><strong>How we use your information<\\/strong><br>We use the information we collect in various ways, including to:<\\/p>\\r\\n<p>1. Provide, operate, and maintain our website<\\/p>\\r\\n<p>2. Improve, personalize, and expand our website<\\/p>\\r\\n<p>3. Understand and analyze how you use our website<\\/p>\\r\\n<p>4. Develop new products, services, features, and functionality<\\/p>\\r\\n<p>5. Communicate with you, either directly or through one of our partners, including for customer service, to provide you with updates and other information relating to the website, and for marketing and promotional purposes<\\/p>\\r\\n<p>6. Send you emails<\\/p>\\r\\n<p>7. Find and prevent fraud<\\/p>\\r\\n<p><strong>Log Files<\\/strong><br>Taskify-SaaS follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services\' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring\\/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users\' movement on the website, and gathering demographic information.<\\/p>\\r\\n<p><strong>Advertising Partners Privacy Policies<\\/strong><br>You may consult this list to find the Privacy Policy for each of the advertising partners of Taskify-SaaS .<\\/p>\\r\\n<p>Third-party ad servers or ad networks uses technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on Taskify-SaaS , which are sent directly to users\' browser. They automatically receive your IP address when this occurs. These technologies are used to measure the effectiveness of their advertising campaigns and\\/or to personalize the advertising content that you see on websites that you visit.<\\/p>\\r\\n<p><strong>Note that Taskify-SaaS has no access to or control over these cookies that are used by third-party advertisers.<\\/strong><\\/p>\\r\\n<p><strong>Third Party Privacy Policies<\\/strong><br>Taskify-SaaS \'s Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options. You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers\' respective websites.<\\/p>\\r\\n<p>Request that a business that collects a consumer\'s personal data disclose the categories and specific pieces of personal data that a business has collected about consumers. Request that a business delete any personal data about the consumer that a business has collected. Request that a business that sells a consumer\'s personal data, not sell the consumer\'s personal data.<\\/p>\\r\\n<p><strong>GDPR Data Protection Rights -&nbsp;<\\/strong><br>We would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:<\\/p>\\r\\n<p>1. The right to access &ndash; You have the right to request copies of your personal data. We may charge you a small fee for this service.<\\/p>\\r\\n<p>2. The right to rectification &ndash; You have the right to request that we correct any information you believe is inaccurate. You also have the right to request that we complete the information you believe is incomplete.<\\/p>\\r\\n<p>3. The right to erasure &ndash; You have the right to request that we erase your personal data, under certain conditions.<\\/p>\\r\\n<p>4. The right to restrict processing &ndash; You have the right to request that we restrict the processing of your personal data, under certain conditions.<\\/p>\\r\\n<p>5. The right to object to processing &ndash; You have the right to object to our processing of your personal data, under certain conditions.<\\/p>\\r\\n<p>6. The right to data portability &ndash; You have the right to request that we transfer the data that we have collected to another organization, or directly to you, under certain conditions.<\\/p>\\r\\n<p>7. If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us.<\\/p>\"}', '2024-03-21 09:19:36', '2024-03-21 10:16:04'),
(22, 'terms_and_conditions', '{\"terms_and_conditions\":\"<p><strong>Welcome to Taskify-SaaS!<\\/strong><\\/p>\\r\\n<p>These terms and conditions outline the rules and regulations for the use of Taskify-SaaS\'s Website, located at https:\\/\\/taskify-saas.taskhub.company\\/.<\\/p>\\r\\n<p>By accessing this website we assume you accept these terms and conditions. Do not continue to use Taskify-SaaS if you do not agree to take all of the terms and conditions stated on this page.<\\/p>\\r\\n<p>The following terminology applies to these Terms and Conditions, Privacy Statement and Disclaimer Notice and all Agreements: \\\"Client\\\", \\\"You\\\" and \\\"Your\\\" refers to you, the person log on this website and compliant to the Company&rsquo;s terms and conditions. \\\"The Company\\\", \\\"Ourselves\\\", \\\"We\\\", \\\"Our\\\" and \\\"Us\\\", refers to our Company. \\\"Party\\\", \\\"Parties\\\", or \\\"Us\\\", refers to both the Client and ourselves. All terms refer to the offer, acceptance and consideration of payment necessary to undertake the process of our assistance to the Client in the most appropriate manner for the express purpose of meeting the Client&rsquo;s needs in respect of provision of the Company&rsquo;s stated services, in accordance with and subject to, prevailing law of India. Any use of the above terminology or other words in the singular, plural, capitalization and\\/or he\\/she or they, are taken as interchangeable and therefore as referring to same.<\\/p>\\r\\n<p><strong>Cookies<\\/strong><br>We employ the use of cookies. By accessing Taskify-SaaS , you agreed to use cookies in agreement with the Taskify-SaaS\'s Privacy Policy.<\\/p>\\r\\n<p>Most interactive websites use cookies to let us retrieve the user&rsquo;s details for each visit. Cookies are used by our website to enable the functionality of certain areas to make it easier for people visiting our website. Some of our affiliate\\/advertising partners may also use cookies.<\\/p>\\r\\n<p><strong>License<\\/strong><br>Unless otherwise stated, Taskify-SaaS and\\/or its licensors own the intellectual property rights for all material on Taskify-SaaS . All intellectual property rights are reserved. You may access this from Taskify-SaaS for your own personal use subjected to restrictions set in these terms and conditions.<\\/p>\\r\\n<p><strong>You must not:<\\/strong><br>Republish material from Taskify-SaaS<\\/p>\\r\\n<p>Sell, rent or sub-license material from Taskify-SaaS<\\/p>\\r\\n<p>Reproduce, duplicate or copy material from Taskify-SaaS<\\/p>\\r\\n<p>Redistribute content from Taskify-SaaS<\\/p>\\r\\n<p>This Agreement shall begin on the date hereof.<\\/p>\\r\\n<p>Parts of this website offer an opportunity for users to post and exchange opinions and information in certain areas of the website. Taskify-SaaS does not filter, edit, publish or review Comments prior to their presence on the website. Comments do not reflect the views and opinions of Taskify-SaaS,its agents and\\/or affiliates. Comments reflect the views and opinions of the person who post their views and opinions. To the extent permitted by applicable laws, Taskify-SaaS shall not be liable for the Comments or for any liability, damages or expenses caused and\\/or suffered as a result of any use of and\\/or posting of and\\/or appearance of the Comments on this website.<\\/p>\\r\\n<p>Taskify-SaaS reserves the right to monitor all Comments and to remove any Comments which can be considered inappropriate, offensive or causes breach of these Terms and Conditions.<\\/p>\\r\\n<p><strong>You warrant and represent that:<\\/strong><br>You are entitled to post the Comments on our website and have all necessary licenses and consents to do so;<\\/p>\\r\\n<p>The Comments do not invade any intellectual property right, including without limitation copyright, patent or trademark of any third party;<\\/p>\\r\\n<p>The Comments do not contain any defamatory, libelous, offensive, indecent or otherwise unlawful material which is an invasion of privacy<\\/p>\\r\\n<p>The Comments will not be used to solicit or promote business or custom or present commercial activities or unlawful activity.<\\/p>\\r\\n<p>You hereby grant Taskify-SaaS a non-exclusive license to use, reproduce, edit and authorize others to use, reproduce and edit any of your Comments in any and all forms, formats or media.<\\/p>\\r\\n<p><strong>Hyperlinking to our Content<\\/strong><br>The following organizations may link to our Website without prior written approval:<\\/p>\\r\\n<p>Government agencies;<\\/p>\\r\\n<p>Search engines;<\\/p>\\r\\n<p>News organizations;<\\/p>\\r\\n<p>Online directory distributors may link to our Website in the same manner as they hyperlink to the Websites of other listed businesses; and<\\/p>\\r\\n<p>System wide Accredited Businesses except soliciting non-profit organizations, charity shopping malls, and charity fundraising groups which may not hyperlink to our Web site.<\\/p>\\r\\n<p>These organizations may link to our home page, to publications or to other Website information so long as the link: (a) is not in any way deceptive; (b) does not falsely imply sponsorship, endorsement or approval of the linking party and its products and\\/or services; and (c) fits within the context of the linking party&rsquo;s site.<\\/p>\\r\\n<p>We may consider and approve other link requests from the following types of organizations:<br>commonly-known consumer and\\/or business information sources;<\\/p>\\r\\n<p>dot.com community sites;<\\/p>\\r\\n<p>associations or other groups representing charities;<\\/p>\\r\\n<p>online directory distributors;<\\/p>\\r\\n<p>internet portals;<\\/p>\\r\\n<p>accounting, law and consulting firms; and<\\/p>\\r\\n<p>educational institutions and trade associations.<\\/p>\\r\\n<p>We will approve link requests from these organizations if we decide that: (a) the link would not make us look unfavorably to ourselves or to our accredited businesses; (b) the organization does not have any negative records with us; (c) the benefit to us from the visibility of the hyperlink compensates the absence of Taskify-SaaS; and (d) the link is in the context of general resource information.<\\/p>\\r\\n<p>These organizations may link to our home page so long as the link: (a) is not in any way deceptive; (b) does not falsely imply sponsorship, endorsement or approval of the linking party and its products or services; and (c) fits within the context of the linking party&rsquo;s site.<\\/p>\\r\\n<p>If you are one of the organizations listed in paragraph 2 above and are interested in linking to our website, you must inform us by sending an e-mail to Taskify-SaaS.<\\/p>\\r\\n<p>Approved organizations may hyperlink to our Website as follows:<\\/p>\\r\\n<p>By use of our corporate name; or<\\/p>\\r\\n<p>By use of the uniform resource locator being linked to; or<\\/p>\\r\\n<p>By use of any other description of our Website being linked to that makes sense within the context and format of content on the linking party&rsquo;s site.<\\/p>\\r\\n<p>No use of Taskify-SaaS\'s logo or other artwork will be allowed for linking absent a trademark license agreement.<\\/p>\\r\\n<p><strong>iFrames<\\/strong><br>Without prior approval and written permission, you may not create frames around our Webpages that alter in any way the visual presentation or appearance of our Website.<\\/p>\\r\\n<p><strong>Content Liability<\\/strong><br>We shall not be hold responsible for any content that appears on your Website. You agree to protect and defend us against all claims that is rising on your Website. No link(s) should appear on any Website that may be interpreted as libelous, obscene or criminal, or which infringes, otherwise violates, or advocates the infringement or other violation of, any third party rights.<\\/p>\\r\\n<p><strong>Your Privacy<\\/strong><br>Please read Privacy Policy<\\/p>\\r\\n<p><strong>Reservation of Rights<\\/strong><br>We reserve the right to request that you remove all links or any particular link to our Website. You approve to immediately remove all links to our Website upon request. We also reserve the right to amen these terms and conditions and it&rsquo;s linking policy at any time. By continuously linking to our Website, you agree to be bound to and follow these linking terms and conditions.<\\/p>\\r\\n<p><strong>Removal of links from our website<\\/strong><br>If you find any link on our Website that is offensive for any reason, you are free to contact and inform us any moment. We will consider requests to remove links but we are not obligated to or so or to respond to you directly.<\\/p>\\r\\n<p>We do not ensure that the information on this website is correct, we do not warrant its completeness or accuracy; nor do we promise to ensure that the website remains available or that the material on the website is kept up to date.<\\/p>\\r\\n<p><strong>Disclaimer<\\/strong><br>To the maximum extent permitted by applicable law, we exclude all representations, warranties and conditions relating to our website and the use of this website. Nothing in this disclaimer will:<\\/p>\\r\\n<p>limit or exclude our or your liability for death or personal injury;<\\/p>\\r\\n<p>limit or exclude our or your liability for fraud or fraudulent misrepresentation;<\\/p>\\r\\n<p>limit any of our or your liabilities in any way that is not permitted under applicable law; or<\\/p>\\r\\n<p>exclude any of our or your liabilities that may not be excluded under applicable law.<\\/p>\\r\\n<p>The limitations and prohibitions of liability set in this Section and elsewhere in this disclaimer: (a) are subject to the preceding paragraph; and (b) govern all liabilities arising under the disclaimer, including liabilities arising in contract, in tort and for breach of statutory duty.<\\/p>\\r\\n<p>As long as the website and the information and services on the website are provided free of charge, we will not be liable for any loss or damage of any nature.<\\/p>\"}', '2024-03-21 09:22:08', '2024-03-21 10:18:36'),
(23, 'sms_gateway_settings', '{\"base_url\":\"\",\"sms_gateway_method\":\"POST\",\"header_data\":{\"Authorization\":\"\"},\"body_formdata\":{\"From\":\"\",\"To\":\"{country_code}{only_mobile_number}\",\"Body\":\"{message}\"},\"params_data\":[],\"text_format_data\":null}', NULL, NULL),
(24, 'whatsapp_settings', '', '2024-07-11 05:47:08', '2024-07-26 23:58:49');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `payment_method` varchar(255) DEFAULT NULL,
  `tenure` varchar(256) NOT NULL,
  `charging_price` decimal(8,2) DEFAULT NULL,
  `charging_currency` varchar(255) DEFAULT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` bigint(20) NOT NULL DEFAULT 1,
  `priority_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `due_date` date NOT NULL,
  `note` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_user`
--

CREATE TABLE `task_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(512) NOT NULL,
  `percentage` double DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `type`, `name`, `subject`, `content`, `status`, `created_at`, `updated_at`) VALUES
(1, 'email', 'account_creation', 'Account Created In {COMPANY_TITLE}', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; margin: 0px; padding: 0px; width: 100%; height: 659.844px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr style=\"height: 77.0938px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; padding: 25px 0px; height: 77.0938px; text-align: center;\"><span style=\"font-size: 18px;\">{COMPANY_LOGO}</span></td>\r\n</tr>\r\n<tr style=\"height: 486.25px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; background-color: rgb(237, 242, 247); margin: 0px; padding: 0px; width: 100%; border: hidden !important; height: 486.25px;\" width=\"100%\">\r\n<table class=\"m_2051327272198114809inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Your account has been successfully created.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\"><strong>Username:</strong> {USER_NAME}<br><strong>Password:</strong> {PASSWORD}</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">If you did not create an account, no further action is required.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">To access your account, simply click on the link below or copy and paste it into your browser:</p>\r\n{SITE_URL}<br><br>Regards,</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;\">&nbsp;</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 96.5px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; height: 96.5px;\">\r\n<table class=\"m_2051327272198114809footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:00:51', '2024-06-24 11:00:51'),
(2, 'email', 'verify_email', 'This is to verify your email address', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; margin: 0px; padding: 0px; width: 100%; height: 659.844px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr style=\"height: 77.0938px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; padding: 25px 0px; height: 77.0938px; text-align: center;\"><span style=\"font-size: 18px;\">{COMPANY_LOGO}</span></td>\r\n</tr>\r\n<tr style=\"height: 486.25px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; background-color: rgb(237, 242, 247); margin: 0px; padding: 0px; width: 100%; border: hidden !important; height: 486.25px;\" width=\"100%\">\r\n<table class=\"m_2051327272198114809inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Please click the button below to verify your email address.</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 30px auto; padding: 0; text-align: center; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\"><a class=\"m_2051327272198114809button\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;\" href=\"{VERIFY_EMAIL_URL}\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=http://127.0.0.1:8000/email/verify/129/9e323bcc5df5fdff3fe6ff960fd6576204a510ca?expires%3D1710157052%26signature%3D2df5df03c59d0dcd960500989168d0bb4b064035d572bc4195ca5a912b0302e8&amp;source=gmail&amp;ust=1710333202290000&amp;usg=AOvVaw2RJnO33Iiu4v5j5tJBiQWH\">Verify Email Address</a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">If you did not create an account, no further action is required.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Regards,</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;\">If you\'re having trouble clicking the \"Verify Email Address\" button, copy and paste the URL below into your web browser: {VERIFY_EMAIL_URL}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;\">&nbsp;</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 96.5px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; height: 96.5px;\">\r\n<table class=\"m_2051327272198114809footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:08:59', '2024-06-24 11:08:59'),
(3, 'email', 'forgot_password', 'Hello {FIRST_NAME} {LAST_NAME} , we have received your reset password request. Please reset your password from below.', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; padding: 25px 0; text-align: center;\">&nbsp;{COMPANY_LOGO}</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; border-bottom: 1px solid #edf2f7; border-top: 1px solid #edf2f7; margin: 0; padding: 0; width: 100%; border: hidden!important;\" width=\"100%\">\r\n<table class=\"m_-1573326913322544649inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">You are receiving this email because we received a password reset request for your account.</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 30px auto; padding: 0; text-align: center; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\"><a class=\"m_-1573326913322544649button\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;\" href=\"{RESET_PASSWORD_URL}\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=http://127.0.0.1:8000/reset-password/a949de0b656f969649a7f702f263b5a6700085e97958ec84a3aa600a52f40c49?email%3Dgirishthacker1995%2540gmail.com&amp;source=gmail&amp;ust=1710507596576000&amp;usg=AOvVaw1NMV_rWlcnaraU7NfjEcNQ\">Reset Password</a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">This password reset link will expire in 60 minutes.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">If you did not request a password reset, no further action is required.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Regards,</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;\">If you\'re having trouble clicking the \"Reset Password\" button, copy and paste the URL below into your web browser: {RESET_PASSWORD_URL}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<table class=\"m_-1573326913322544649footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:10:16', '2024-06-24 11:10:16'),
(4, 'email', 'project_assignment', 'New Project Assigned to you.', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; margin: 0px; padding: 0px; width: 100%; height: 659.844px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr style=\"height: 77.0938px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; padding: 25px 0px; height: 77.0938px; text-align: center;\"><span style=\"font-size: 18px;\">{COMPANY_LOGO}</span></td>\r\n</tr>\r\n<tr style=\"height: 486.25px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; background-color: rgb(237, 242, 247); margin: 0px; padding: 0px; width: 100%; border: hidden !important; height: 486.25px;\" width=\"100%\">\r\n<table class=\"m_2051327272198114809inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">You have been assigned a new project <strong>{PROJECT_TITLE}, </strong>ID:<strong>#{PROJECT_ID}</strong>.<br><br>Please click the button below to access detailed project information.</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 30px auto; padding: 0; text-align: center; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\"><a class=\"m_2051327272198114809button\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;\" href=\"{PROJECT_URL}\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=http://127.0.0.1:8000/email/verify/129/9e323bcc5df5fdff3fe6ff960fd6576204a510ca?expires%3D1710157052%26signature%3D2df5df03c59d0dcd960500989168d0bb4b064035d572bc4195ca5a912b0302e8&amp;source=gmail&amp;ust=1710333202290000&amp;usg=AOvVaw2RJnO33Iiu4v5j5tJBiQWH\">View Project Details</a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Regards,</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;\">If you\'re having trouble clicking the \"View Project Details\" button, copy and paste the URL below into your web browser: {PROJECT_URL}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;\">&nbsp;</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 96.5px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; height: 96.5px;\">\r\n<table class=\"m_2051327272198114809footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:10:34', '2024-06-24 11:10:34'),
(5, 'email', 'task_assignment', 'New Task assigned to you.', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; margin: 0px; padding: 0px; width: 100%; height: 659.844px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr style=\"height: 77.0938px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; padding: 25px 0px; height: 77.0938px; text-align: center;\"><span style=\"font-size: 18px;\">{COMPANY_LOGO}</span></td>\r\n</tr>\r\n<tr style=\"height: 486.25px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; background-color: rgb(237, 242, 247); margin: 0px; padding: 0px; width: 100%; border: hidden !important; height: 486.25px;\" width=\"100%\">\r\n<table class=\"m_2051327272198114809inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">You have been assigned a new task <strong>{TASK_TITLE}, </strong>ID:<strong>#{TASK_ID}</strong>.<br><br>Please click the button below to access detailed task information.</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 30px auto; padding: 0; text-align: center; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\"><a class=\"m_2051327272198114809button\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;\" href=\"{TASK_URL}\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=http://127.0.0.1:8000/email/verify/129/9e323bcc5df5fdff3fe6ff960fd6576204a510ca?expires%3D1710157052%26signature%3D2df5df03c59d0dcd960500989168d0bb4b064035d572bc4195ca5a912b0302e8&amp;source=gmail&amp;ust=1710333202290000&amp;usg=AOvVaw2RJnO33Iiu4v5j5tJBiQWH\">View Task Details</a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Regards,</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;\">If you\'re having trouble clicking the \"View Task Details\" button, copy and paste the URL below into your web browser: {TASK_URL}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;\">&nbsp;</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 96.5px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; height: 96.5px;\">\r\n<table class=\"m_2051327272198114809footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:10:56', '2024-06-24 11:10:56');
INSERT INTO `templates` (`id`, `type`, `name`, `subject`, `content`, `status`, `created_at`, `updated_at`) VALUES
(6, 'email', 'workspace_assignment', 'New Workspace Assigned to you.', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; margin: 0px; padding: 0px; width: 100%; height: 659.844px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr style=\"height: 77.0938px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; padding: 25px 0px; height: 77.0938px; text-align: center;\"><span style=\"font-size: 18px;\">{COMPANY_LOGO}</span></td>\r\n</tr>\r\n<tr style=\"height: 486.25px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; background-color: rgb(237, 242, 247); margin: 0px; padding: 0px; width: 100%; border: hidden !important; height: 486.25px;\" width=\"100%\">\r\n<table class=\"m_2051327272198114809inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">You have been added in a new workspace <strong>{WORKSPACE_TITLE}, </strong>ID:<strong>#{WORKSPACE_ID}</strong>.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Please click the button below to access workspace.</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 30px auto; padding: 0; text-align: center; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\"><a class=\"m_2051327272198114809button\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;\" href=\"{WORKSPACE_URL}\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=http://127.0.0.1:8000/email/verify/129/9e323bcc5df5fdff3fe6ff960fd6576204a510ca?expires%3D1710157052%26signature%3D2df5df03c59d0dcd960500989168d0bb4b064035d572bc4195ca5a912b0302e8&amp;source=gmail&amp;ust=1710333202290000&amp;usg=AOvVaw2RJnO33Iiu4v5j5tJBiQWH\">Access Workspace</a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Regards,</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;\">If you\'re having trouble clicking the \"Access Workspace\" button, copy and paste the URL below into your web browser: {WORKSPACE_URL}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;\">&nbsp;</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 96.5px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; height: 96.5px;\">\r\n<table class=\"m_2051327272198114809footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:11:27', '2024-06-24 11:11:27'),
(7, 'email', 'meeting_assignment', 'New Meeting Assigned to you.', '<div style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100%!important;\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; margin: 0px; padding: 0px; width: 100%; height: 659.844px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr style=\"height: 77.0938px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; padding: 25px 0px; height: 77.0938px; text-align: center;\"><span style=\"font-size: 18px;\">{COMPANY_LOGO}</span></td>\r\n</tr>\r\n<tr style=\"height: 486.25px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; background-color: rgb(237, 242, 247); margin: 0px; padding: 0px; width: 100%; border: hidden !important; height: 486.25px;\" width=\"100%\">\r\n<table class=\"m_2051327272198114809inner-body\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; margin: 0 auto; padding: 0; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: rgb(61, 72, 82); font-size: 18px; font-weight: bold; margin-top: 0px; text-align: left;\">Hello, {FIRST_NAME} {LAST_NAME}</h1>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">You have been added in a new meeting <strong>{MEETING_TITLE}, </strong>ID:<strong>#{MEETING_ID}</strong>.</p>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Please click the button below to access meeting.</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 30px auto; padding: 0; text-align: center; width: 100%;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" align=\"center\">\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\" role=\"presentation\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\"><a class=\"m_2051327272198114809button\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;\" href=\"{MEETING_URL}\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=http://127.0.0.1:8000/email/verify/129/9e323bcc5df5fdff3fe6ff960fd6576204a510ca?expires%3D1710157052%26signature%3D2df5df03c59d0dcd960500989168d0bb4b064035d572bc4195ca5a912b0302e8&amp;source=gmail&amp;ust=1710333202290000&amp;usg=AOvVaw2RJnO33Iiu4v5j5tJBiQWH\">Access Meeting</a></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;\">Regards,</p>\r\n<table style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;\" role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;\">If you\'re having trouble clicking the \"Access Meeting\" button, copy and paste the URL below into your web browser: {MEETING_URL}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\">\r\n<h1 style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;\">&nbsp;</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 96.5px;\">\r\n<td style=\"box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; height: 96.5px;\">\r\n<table class=\"m_2051327272198114809footer\" style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; margin: 0 auto; padding: 0; text-align: center; width: 570px;\" role=\"presentation\" width=\"570\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; max-width: 100vw; padding: 32px;\" align=\"center\">\r\n<p style=\"box-sizing: border-box; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\'; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;\">&copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div class=\"yj6qo\">&nbsp;</div>\r\n<div class=\"adL\">&nbsp;</div>\r\n</div>', 1, '2024-06-24 11:11:49', '2024-06-24 11:11:49'),
(8, 'sms', 'project_assignment', NULL, 'Hello, {FIRST_NAME} {LAST_NAME} You have been assigned a new project {PROJECT_TITLE}, ID:#{PROJECT_ID}.', 1, '2024-06-24 11:28:19', '2024-06-24 11:28:19'),
(9, 'sms', 'workspace_assignment', NULL, 'Hello, {FIRST_NAME} {LAST_NAME} You have been added in a new workspace {WORKSPACE_TITLE}, ID:#{WORKSPACE_ID}.', 1, '2024-06-24 11:58:35', '2024-06-24 11:58:35'),
(10, 'sms', 'meeting_assignment', NULL, 'Hello, {FIRST_NAME} {LAST_NAME} You have been added in a new meeting {MEETING_TITLE}, ID:#{MEETING_ID}.', 1, '2024-06-24 11:58:41', '2024-06-24 11:58:41'),
(11, 'sms', 'task_assignment', NULL, 'Hello, {FIRST_NAME} {LAST_NAME} You have been assigned a new task {TASK_TITLE}, ID:#{TASK_ID}.', 1, '2024-06-25 22:33:22', '2024-06-25 22:33:22'),
(12, 'whatsapp', 'leave_request_creation', NULL, 'New Leave Request ID:#{ID} Has Been Created By {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME}.', 1, '2024-07-22 04:27:32', '2024-07-26 21:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `time_trackers`
--

CREATE TABLE `time_trackers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` varchar(255) NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `creator_id` bigint(20) UNSIGNED NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) DEFAULT 'pending',
  `currency` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

CREATE TABLE `updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `updates`
--

INSERT INTO `updates` (`id`, `version`, `created_at`, `updated_at`) VALUES
(1, '1.0.0', '2023-10-03 06:26:04', '2023-10-03 06:26:04'),
(5, '1.0.1', '2024-07-01 04:11:13', '2024-07-01 04:11:18'),
(6, '1.0.2', '2024-07-02 04:11:21', '2024-07-02 04:11:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone` varchar(56) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `zip` int(10) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `doj` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT 'avatar.png',
  `active_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'For chatify messenger',
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `messenger_color` varchar(255) DEFAULT NULL,
  `lang` varchar(28) NOT NULL DEFAULT 'en',
  `remember_token` text DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `country_code` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_client_preferences`
--

CREATE TABLE `user_client_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` varchar(56) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `visible_columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`visible_columns`)),
  `default_view` varchar(255) DEFAULT NULL,
  `enabled_notifications` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_workspace`
--

CREATE TABLE `user_workspace` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workspace_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workspaces`
--

CREATE TABLE `workspaces` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_primary` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_workspaces_id_foreign` (`workspace_id`),
  ADD KEY `activity_logs_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admins_user_id_foreign` (`user_id`);

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `allowances_workspace_id_foreign` (`workspace_id`),
  ADD KEY `allowances_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `allowance_payslip`
--
ALTER TABLE `allowance_payslip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `allowance_payslip_allowance_id_foreign` (`allowance_id`),
  ADD KEY `allowance_payslip_payslip_id_foreign` (`payslip_id`),
  ADD KEY `allowance_payslip_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `ch_favorites`
--
ALTER TABLE `ch_favorites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ch_messages`
--
ALTER TABLE `ch_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_email_unique` (`email`),
  ADD KEY `clients_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `client_meeting`
--
ALTER TABLE `client_meeting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_meeting_meeting_id_client_id_unique` (`meeting_id`,`client_id`),
  ADD KEY `client_meeting_client_id_foreign` (`client_id`),
  ADD KEY `client_meeting_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `client_notifications`
--
ALTER TABLE `client_notifications`
  ADD PRIMARY KEY (`client_id`,`notification_id`),
  ADD KEY `client_notifications_notification_id_foreign` (`notification_id`);

--
-- Indexes for table `client_project`
--
ALTER TABLE `client_project`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_client_project_id_client_id_unique` (`project_id`,`client_id`),
  ADD KEY `project_client_client_id_foreign` (`client_id`),
  ADD KEY `client_project_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `client_workspace`
--
ALTER TABLE `client_workspace`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_workspace_workspace_id_client_id_unique` (`workspace_id`,`client_id`),
  ADD KEY `client_workspace_client_id_foreign` (`client_id`),
  ADD KEY `client_workspace_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contracts_workspace_id_foreign` (`workspace_id`),
  ADD KEY `contracts_project_id_foreign` (`project_id`),
  ADD KEY `contracts_client_id_foreign` (`client_id`),
  ADD KEY `contracts_contract_type_id_foreign` (`contract_type_id`),
  ADD KEY `contracts_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `contract_types`
--
ALTER TABLE `contract_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_types_workspace_id_foreign` (`workspace_id`),
  ADD KEY `contract_types_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deductions_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `deduction_payslip`
--
ALTER TABLE `deduction_payslip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deduction_payslip_deduction_id_foreign` (`deduction_id`),
  ADD KEY `deduction_payslip_payslip_id_foreign` (`payslip_id`),
  ADD KEY `deduction_payslip_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `estimates_invoices`
--
ALTER TABLE `estimates_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimates_invoices_client_id_foreign` (`client_id`),
  ADD KEY `estimates_invoices_workspace_id_foreign` (`workspace_id`);

--
-- Indexes for table `estimates_invoice_item`
--
ALTER TABLE `estimates_invoice_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimates_invoice_item_item_id_foreign` (`item_id`),
  ADD KEY `estimates_invoice_item_estimates_invoice_id_foreign` (`estimates_invoice_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_workspace_id_foreign` (`workspace_id`),
  ADD KEY `expenses_user_id_foreign` (`user_id`),
  ADD KEY `expenses_expense_type_id_foreign` (`expense_type_id`);

--
-- Indexes for table `expense_types`
--
ALTER TABLE `expense_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_types_workspace_id_foreign` (`workspace_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_editors`
--
ALTER TABLE `leave_editors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_editors_user_id_foreign` (`user_id`),
  ADD KEY `leave_editors_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_requests_user_id_foreign` (`user_id`),
  ADD KEY `leave_requests_workspace_id_foreign` (`workspace_id`),
  ADD KEY `leave_requests_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `leave_request_visibility`
--
ALTER TABLE `leave_request_visibility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_request_visibility_leave_request_id_foreign` (`leave_request_id`),
  ADD KEY `leave_request_visibility_user_id_foreign` (`user_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meetings_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `meeting_user`
--
ALTER TABLE `meeting_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meeting_user_meeting_id_user_id_unique` (`meeting_id`,`user_id`),
  ADD KEY `meeting_user_user_id_foreign` (`user_id`),
  ADD KEY `meeting_user_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `milestones`
--
ALTER TABLE `milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestones_project_id_foreign` (`project_id`),
  ADD KEY `milestones_workspace_id_foreign` (`workspace_id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notes_workspace_id_foreign` (`workspace_id`),
  ADD KEY `notes_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_workspace_id_foreign` (`workspace_id`);

--
-- Indexes for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD PRIMARY KEY (`user_id`,`notification_id`),
  ADD KEY `notification_user_notification_id_foreign` (`notification_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_methods_workspace_id_foreign` (`workspace_id`),
  ADD KEY `payment_methods_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslips_user_id_foreign` (`user_id`),
  ADD KEY `payslips_workspace_id_foreign` (`workspace_id`),
  ADD KEY `payslips_payment_method_id_foreign` (`payment_method_id`),
  ADD KEY `payslips_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `priorities`
--
ALTER TABLE `priorities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `priorities_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_status_id_foreign` (`status_id`),
  ADD KEY `projects_admin_id_foreign` (`admin_id`),
  ADD KEY `projects_priority_id_foreign` (`priority_id`);

--
-- Indexes for table `project_tag`
--
ALTER TABLE `project_tag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_tag_project_id_foreign` (`project_id`),
  ADD KEY `project_tag_tag_id_foreign` (`tag_id`),
  ADD KEY `project_tag_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `project_user`
--
ALTER TABLE `project_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_user_project_id_user_id_unique` (`project_id`,`user_id`),
  ADD KEY `project_user_user_id_foreign` (`user_id`),
  ADD KEY `project_user_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `role_status`
--
ALTER TABLE `role_status`
  ADD PRIMARY KEY (`role_id`,`status_id`),
  ADD KEY `role_status_status_id_foreign` (`status_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `statuses_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_user_id_foreign` (`user_id`),
  ADD KEY `subscriptions_plan_id_foreign` (`plan_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tags_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_id_foreign` (`project_id`),
  ADD KEY `tasks_admin_id_foreign` (`admin_id`),
  ADD KEY `tasks_priority_id_foreign` (`priority_id`);

--
-- Indexes for table `task_user`
--
ALTER TABLE `task_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_user_task_id_user_id_unique` (`task_id`,`user_id`),
  ADD KEY `task_user_user_id_foreign` (`user_id`),
  ADD KEY `task_user_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_members_admin_id_user_id_unique` (`admin_id`,`user_id`),
  ADD KEY `team_members_user_id_foreign` (`user_id`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `templates_type_name_unique` (`type`,`name`);

--
-- Indexes for table `time_trackers`
--
ALTER TABLE `time_trackers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_trackers_workspace_id_foreign` (`workspace_id`),
  ADD KEY `time_trackers_user_id_foreign` (`user_id`),
  ADD KEY `time_trackers_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `todos_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_subscription_id_foreign` (`subscription_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_client_preferences`
--
ALTER TABLE `user_client_preferences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_workspace`
--
ALTER TABLE `user_workspace`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_workspace_workspace_id_user_id_unique` (`workspace_id`,`user_id`),
  ADD KEY `user_workspace_user_id_foreign` (`user_id`),
  ADD KEY `user_workspace_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `workspaces`
--
ALTER TABLE `workspaces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workspaces_admin_id_foreign` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `allowance_payslip`
--
ALTER TABLE `allowance_payslip`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_meeting`
--
ALTER TABLE `client_meeting`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_project`
--
ALTER TABLE `client_project`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_workspace`
--
ALTER TABLE `client_workspace`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contract_types`
--
ALTER TABLE `contract_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deduction_payslip`
--
ALTER TABLE `deduction_payslip`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimates_invoices`
--
ALTER TABLE `estimates_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimates_invoice_item`
--
ALTER TABLE `estimates_invoice_item`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_types`
--
ALTER TABLE `expense_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_editors`
--
ALTER TABLE `leave_editors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_request_visibility`
--
ALTER TABLE `leave_request_visibility`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meeting_user`
--
ALTER TABLE `meeting_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priorities`
--
ALTER TABLE `priorities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_tag`
--
ALTER TABLE `project_tag`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_user`
--
ALTER TABLE `project_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_user`
--
ALTER TABLE `task_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `time_trackers`
--
ALTER TABLE `time_trackers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `todos`
--
ALTER TABLE `todos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_client_preferences`
--
ALTER TABLE `user_client_preferences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_workspace`
--
ALTER TABLE `user_workspace`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workspaces`
--
ALTER TABLE `workspaces`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_workspaces_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `allowances`
--
ALTER TABLE `allowances`
  ADD CONSTRAINT `allowances_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `allowances_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `allowance_payslip`
--
ALTER TABLE `allowance_payslip`
  ADD CONSTRAINT `allowance_payslip_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `allowance_payslip_allowance_id_foreign` FOREIGN KEY (`allowance_id`) REFERENCES `allowances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allowance_payslip_payslip_id_foreign` FOREIGN KEY (`payslip_id`) REFERENCES `payslips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `client_meeting`
--
ALTER TABLE `client_meeting`
  ADD CONSTRAINT `client_meeting_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `client_meeting_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_meeting_meeting_id_foreign` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_notifications`
--
ALTER TABLE `client_notifications`
  ADD CONSTRAINT `client_notifications_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_notifications_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_project`
--
ALTER TABLE `client_project`
  ADD CONSTRAINT `client_project_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_client_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_client_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_workspace`
--
ALTER TABLE `client_workspace`
  ADD CONSTRAINT `client_workspace_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `client_workspace_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_workspace_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contracts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_contract_type_id_foreign` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_types` (`id`),
  ADD CONSTRAINT `contracts_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contract_types`
--
ALTER TABLE `contract_types`
  ADD CONSTRAINT `contract_types_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contract_types_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deductions`
--
ALTER TABLE `deductions`
  ADD CONSTRAINT `deductions_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deduction_payslip`
--
ALTER TABLE `deduction_payslip`
  ADD CONSTRAINT `deduction_payslip_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deduction_payslip_deduction_id_foreign` FOREIGN KEY (`deduction_id`) REFERENCES `deductions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deduction_payslip_payslip_id_foreign` FOREIGN KEY (`payslip_id`) REFERENCES `payslips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimates_invoices`
--
ALTER TABLE `estimates_invoices`
  ADD CONSTRAINT `estimates_invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimates_invoices_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimates_invoice_item`
--
ALTER TABLE `estimates_invoice_item`
  ADD CONSTRAINT `estimates_invoice_item_estimates_invoice_id_foreign` FOREIGN KEY (`estimates_invoice_id`) REFERENCES `estimates_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimates_invoice_item_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_expense_type_id_foreign` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`),
  ADD CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expense_types`
--
ALTER TABLE `expense_types`
  ADD CONSTRAINT `expense_types_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_editors`
--
ALTER TABLE `leave_editors`
  ADD CONSTRAINT `leave_editors_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_editors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_request_visibility`
--
ALTER TABLE `leave_request_visibility`
  ADD CONSTRAINT `leave_request_visibility_leave_request_id_foreign` FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_request_visibility_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `meetings_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `meeting_user`
--
ALTER TABLE `meeting_user`
  ADD CONSTRAINT `meeting_user_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `meeting_user_meeting_id_foreign` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meeting_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `milestones`
--
ALTER TABLE `milestones`
  ADD CONSTRAINT `milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `milestones_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notes_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD CONSTRAINT `notification_user_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_methods_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payslips_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `payslips_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslips_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `priorities`
--
ALTER TABLE `priorities`
  ADD CONSTRAINT `priorities_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_tag`
--
ALTER TABLE `project_tag`
  ADD CONSTRAINT `project_tag_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_tag_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_user`
--
ALTER TABLE `project_user`
  ADD CONSTRAINT `project_user_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_status`
--
ALTER TABLE `role_status`
  ADD CONSTRAINT `role_status_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_status_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `statuses`
--
ALTER TABLE `statuses`
  ADD CONSTRAINT `statuses_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_user`
--
ALTER TABLE `task_user`
  ADD CONSTRAINT `task_user_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `task_user_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_trackers`
--
ALTER TABLE `time_trackers`
  ADD CONSTRAINT `time_trackers_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_trackers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `time_trackers_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `todos_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_workspace`
--
ALTER TABLE `user_workspace`
  ADD CONSTRAINT `user_workspace_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_workspace_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_workspace_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workspaces`
--
ALTER TABLE `workspaces`
  ADD CONSTRAINT `workspaces_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
