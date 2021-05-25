-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 25, 2021 at 02:14 PM
-- Server version: 8.0.25-0ubuntu0.20.04.1
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legalcase_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_activity`
--

CREATE TABLE `account_activity` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'ref : user->id',
  `related_to` int DEFAULT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref: case_master->id',
  `credit_amount` decimal(11,2) DEFAULT '0.00',
  `debit_amount` decimal(11,2) DEFAULT '0.00',
  `total_amount` decimal(11,2) DEFAULT '0.00',
  `entry_date` date DEFAULT NULL,
  `status` enum('sent','unsent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pay_type` enum('trust','client') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firm_id` int DEFAULT NULL,
  `section` enum('invoice','request','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_pay` enum('trust','normal','none') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `all_history`
--

CREATE TABLE `all_history` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `activity` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `activity_for` int DEFAULT NULL,
  `notes_for_case` int DEFAULT NULL,
  `notes_for_client` int DEFAULT NULL,
  `notes_for_company` int DEFAULT NULL,
  `event_for_case` int DEFAULT NULL,
  `event_for_lead` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `expense_id` int DEFAULT NULL,
  `time_entry_id` int DEFAULT NULL,
  `event_name` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_for_lead` int DEFAULT NULL,
  `task_for_case` int DEFAULT NULL,
  `task_id` int DEFAULT NULL,
  `task_name` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_id` int DEFAULT NULL,
  `deposit_for` int DEFAULT NULL,
  `document_id` int DEFAULT NULL,
  `type` enum('time_entry','expenses','invoices','other','notes','contact','event','task','deposit','document') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` enum('add','update','delete','none','pay','complete','incomplete','share','comment','archive','unarchive','link') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `firm_id` int NOT NULL,
  `client_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calls`
--

CREATE TABLE `calls` (
  `id` int UNSIGNED NOT NULL,
  `call_date` date DEFAULT NULL,
  `call_time` time DEFAULT NULL,
  `caller_name` int DEFAULT NULL,
  `caller_name_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `case_id` int DEFAULT NULL,
  `call_for` int DEFAULT NULL COMMENT 'firm user',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `call_duration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `call_resolved` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'yes',
  `call_type` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0 : Incoming1: Outgoing',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_activity`
--

CREATE TABLE `case_activity` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref: case->id',
  `activity_title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Low 2: Medium 3:High',
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `staff_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_client_selection`
--

CREATE TABLE `case_client_selection` (
  `id` int NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref : case_master->id',
  `user_role` int DEFAULT NULL,
  `selected_user` int DEFAULT NULL,
  `is_billing_contact` enum('yes','no') CHARACTER SET latin1 DEFAULT 'no',
  `billing_method` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `billing_amount` double(11,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_events`
--

CREATE TABLE `case_events` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref: case->id',
  `lead_id` int DEFAULT NULL,
  `parent_evnt_id` int DEFAULT NULL,
  `event_title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_SOL` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `event_type` int DEFAULT NULL COMMENT 'ref: event_type->id',
  `all_day` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date NOT NULL,
  `end_time` time DEFAULT NULL,
  `event_location_id` int DEFAULT NULL COMMENT 'ref= case_event_location->id',
  `event_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_event_private` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `recuring_event` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `event_frequency` enum('DAILY','EVERY_BUSINESS_DAY','CUSTOM','WEEKLY','MONTHLY','YEARLY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_interval_day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daily_weekname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_end_date_checkbox` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_interval_month` int DEFAULT NULL,
  `event_interval_year` int DEFAULT NULL,
  `monthly_frequency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yearly_frequency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_on` date DEFAULT NULL,
  `event_read` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_event_comment`
--

CREATE TABLE `case_event_comment` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `action_type` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0 : Comment 1: History',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_event_linked_staff`
--

CREATE TABLE `case_event_linked_staff` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `attending` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `is_linked` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_event_location`
--

CREATE TABLE `case_event_location` (
  `id` int UNSIGNED NOT NULL,
  `location_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ref: case->id',
  `address1` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` int DEFAULT NULL,
  `location_future_use` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_event_reminder`
--

CREATE TABLE `case_event_reminder` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int DEFAULT NULL,
  `reminder_type` enum('popup','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_user_type` enum('me','attorney','paralegal','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminer_number` int DEFAULT NULL,
  `reminder_frequncy` enum('minute','hour','day','week') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_intake_form`
--

CREATE TABLE `case_intake_form` (
  `id` int UNSIGNED NOT NULL,
  `intake_form_id` int NOT NULL COMMENT 'ref : intake_form->id',
  `lead_id` int DEFAULT NULL COMMENT 'user->id',
  `client_id` int DEFAULT NULL,
  `case_id` int DEFAULT NULL,
  `status` enum('0','1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '0 : Pending 1: Sent 2:Submited 3: Semt via portal',
  `form_unique_id` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submited_at` timestamp NULL DEFAULT NULL,
  `firm_id` int DEFAULT NULL,
  `submited_to` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_filled` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `unique_token` varchar(512) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_intake_form_fields_data`
--

CREATE TABLE `case_intake_form_fields_data` (
  `id` int UNSIGNED NOT NULL,
  `intake_form_id` int NOT NULL COMMENT 'ref : case_intake_form->intake_form_id',
  `form_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `form_type` enum('contact','normal') NOT NULL DEFAULT 'normal',
  `firm_id` int DEFAULT NULL,
  `online_lead_id` int DEFAULT NULL,
  `case_intake_form_token` varchar(512) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_master`
--

CREATE TABLE `case_master` (
  `id` int UNSIGNED NOT NULL,
  `case_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `case_unique_number` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `case_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `case_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `case_status` int DEFAULT '0' COMMENT 'Ref: case_stage->id',
  `case_open_date` datetime DEFAULT NULL,
  `case_close_date` datetime DEFAULT NULL,
  `case_office` int DEFAULT NULL,
  `case_statute_date` datetime DEFAULT NULL,
  `sol_satisfied` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `conflict_check` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: no 1:yes',
  `conflict_check_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `practice_area` int DEFAULT NULL COMMENT 'ref : case_practice_area ->id',
  `is_entry_done` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0 : Incompelete 1: Completed',
  `billing_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_amount` decimal(11,2) DEFAULT '0.00',
  `firm_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_notes`
--

CREATE TABLE `case_notes` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int NOT NULL,
  `lead_id` int DEFAULT NULL COMMENT 'if lead_for is not null then this field shoud be null (note_for=case_id)',
  `notes_for` int DEFAULT NULL,
  `note_date` date DEFAULT NULL,
  `note_activity` int DEFAULT NULL COMMENT 'ref : lead_notes_activity->id',
  `note_subject` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_practice_area`
--

CREATE TABLE `case_practice_area` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_sol_reminder`
--

CREATE TABLE `case_sol_reminder` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `reminder_type` enum('popup','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminer_number` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_staff`
--

CREATE TABLE `case_staff` (
  `id` int NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref : case_master->id',
  `user_id` int NOT NULL COMMENT 'staff user id',
  `lead_attorney` int DEFAULT NULL,
  `originating_attorney` int DEFAULT NULL,
  `rate_type` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0: Default_Rate 1: Case_Rate  ',
  `rate_amount` double(11,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_stage`
--

CREATE TABLE `case_stage` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage_order` int DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_stage_history`
--

CREATE TABLE `case_stage_history` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `stage_id` int DEFAULT NULL COMMENT 'Ref: case_stage->id',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `case_update`
--

CREATE TABLE `case_update` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `update_status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client_activity`
--

CREATE TABLE `client_activity` (
  `id` int UNSIGNED NOT NULL,
  `acrtivity_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `activity_by` int DEFAULT NULL,
  `activity_for` int DEFAULT NULL,
  `type` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1 : Task 2: Case 3: fund',
  `task_id` int DEFAULT NULL,
  `case_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client_company_import`
--

CREATE TABLE `client_company_import` (
  `id` int UNSIGNED NOT NULL,
  `file_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_record` int DEFAULT NULL,
  `total_imported` int DEFAULT NULL,
  `total_warning` int DEFAULT '0',
  `status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Completed 2: Error  3: Undo',
  `flat_fees` decimal(11,2) NOT NULL,
  `firm_id` int DEFAULT NULL,
  `error_code` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_company_import_history`
--

CREATE TABLE `client_company_import_history` (
  `id` int UNSIGNED NOT NULL,
  `client_company_import_id` int DEFAULT NULL COMMENT 'ref : : client_company_import->id',
  `full_name` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outstanding_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warning_list` text COLLATE utf8mb4_unicode_ci,
  `total_record` int DEFAULT NULL,
  `total_imported` int DEFAULT NULL,
  `status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Completed 2: Error  3: Undo',
  `flat_fees` decimal(11,2) NOT NULL,
  `firm_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_group`
--

CREATE TABLE `client_group` (
  `id` int NOT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Inactive 1: Active',
  `firm_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client_notes`
--

CREATE TABLE `client_notes` (
  `id` int UNSIGNED NOT NULL,
  `client_id` int DEFAULT NULL COMMENT 'ref : user->id',
  `case_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `note_date` date DEFAULT NULL,
  `is_draft` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'no' COMMENT 'case',
  `is_publish` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `note_subject` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: Unpublished 1: Published ',
  `original_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `contract_access_permission`
--

CREATE TABLE `contract_access_permission` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL COMMENT 'ref : contract_user->id',
  `clientsPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden',
  `leadsPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden',
  `casesPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden',
  `eventsPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden',
  `documentsPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden',
  `commentingPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden',
  `textMessagingPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '0:yes 1:1: View 2: View 3:Hidden',
  `messagesPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden ',
  `billingPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden ',
  `reportingPermission` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1: View 2: View 3:Hidden ',
  `allMessagesFirmwide` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:Unchecked 1:Checked',
  `restrictBilling` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:Unchecked 1:Checked',
  `financialInsightsPermission` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:Unchecked 1:Checked',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `contract_user_case`
--

CREATE TABLE `contract_user_case` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL COMMENT 'ref : contract_user->id',
  `case_id` int DEFAULT NULL COMMENT 'ref: case->id',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `contract_user_permission`
--

CREATE TABLE `contract_user_permission` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL COMMENT 'ref : contract_user->id',
  `access_case` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:All firm cases  1:  Only linked cases',
  `add_new` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:yes 1:no',
  `edit_permisssion` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:yes 1:no',
  `delete_item` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:yes 1:no',
  `import_export` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:yes 1:no',
  `custome_fields` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:yes 1:no',
  `manage_firm` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:yes 1:no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `abv` char(2) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'ISO 3661-1 alpha-2',
  `abv3` char(3) CHARACTER SET utf8 DEFAULT NULL COMMENT 'ISO 3661-1 alpha-3',
  `abv3_alt` char(3) CHARACTER SET utf8 DEFAULT NULL,
  `code` char(3) CHARACTER SET utf8 DEFAULT NULL COMMENT 'ISO 3661-1 numeric',
  `slug` varchar(100) CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deactivated_user`
--

CREATE TABLE `deactivated_user` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `reason` enum('1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deposit_into_credit_history`
--

CREATE TABLE `deposit_into_credit_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'user->id',
  `deposit_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `deposit_by` int NOT NULL,
  `firm_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deposit_into_trust`
--

CREATE TABLE `deposit_into_trust` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'ref : user->id',
  `invoice_id` int DEFAULT NULL,
  `requested_id` int DEFAULT NULL COMMENT 'ref : requested_fund->id',
  `credit_amount` decimal(11,2) DEFAULT '0.00',
  `debit_amount` decimal(11,2) DEFAULT '0.00',
  `total_amount` decimal(11,2) DEFAULT '0.00',
  `payment_date` date DEFAULT NULL,
  `status` enum('sent','unsent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pay_type` enum('trust','client') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_master`
--

CREATE TABLE `document_master` (
  `id` int NOT NULL,
  `case_id` int DEFAULT NULL,
  `document_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `firm_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_template`
--

CREATE TABLE `email_template` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_template`
--

INSERT INTO `email_template` (`id`, `title`, `subject`, `content`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, 'forgot password', 'Legalcase Password Reset Instructions', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Forgot Password</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Dear {name} </b>, A request to reset your password has been made. </td>\r\n        </tr>\r\n       \r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">If you did not make this request, simply ignore this email. If you did make this request just click the button below:</td>\r\n        </tr>\r\n         <tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 40px;padding-bottom: 40px;\">\r\n                <a href=\"{1}\" style=\"width: 160px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">Reset Password</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n			If you continue to have problems, please feel free to contact us at\r\n<b> <a style=\"color: #242527;\" href=\"{supporthref}\">{support_email}</a></b> for additional support.  </b> </td>\r\n        </tr>\r\n		\r\n\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-05-21 18:30:00', 1, NULL, NULL, NULL, NULL),
(2, 'password changed', 'Legalcase :: Password Changed', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Password Changed</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Dear {name} </b>, your account password has been recently changed.</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">If you made this change, no further action is required.</td>\r\n        </tr>\r\n       <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;font-weight: 600;\"><br></td>\r\n        </tr>\r\n       \r\n         <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">We will always let you know when there is any activity on your account. This helps keep your account safe. </td>\r\n        </tr> \r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n			If you did not make this request, contact us immediately at <b> <a style=\"color: #242527;\" href=\"{supporthref}\">{support_email}</a> </b> </td>\r\n        </tr>\r\n		\r\n\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-05-21 18:30:00', 1, NULL, NULL, NULL, NULL),
(3, 'Temp code for reset password', 'Reset PAssword with OTP', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Dear {0} </b>, you requested a verification code. You can use this code to complete the process. </td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 32px;font-weight: 600;\">Temporary Identification Code:<br>\r\n			{1}\r\n			</td>\r\n        </tr>\r\n		 <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		\r\n         <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">We will always let you know when there is any activity on your account. This helps keep your account safe. </td>\r\n        </tr> \r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n			If you did not make this request, contact us immediately at <b> <a style=\"color: #242527;\" href=\"{supporthref}\">support@legalcase.com</a> </b> </td>\r\n        </tr>\r\n		\r\n\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>Poderjudicialvirtual.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 15px;\">\r\n					\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(4, 'User Welcome', 'Legalcase :: Welcome', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Dear {name} </b>,your Legalcase account is ready to go. Let’s get set up!.</td>\r\n        </tr>\r\n        \r\n       <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;font-weight: 600;\"><br></td>\r\n        </tr>\r\n       \r\n         <tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">We will always let you know when there is any activity on your account. This helps keep your account safe. </td>\r\n        </tr> \r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n			If you did not make this request, contact us immediately at <b> <a style=\"color: #242527;\" href=\"{supporthref}\">{support_email}</a> </b> </td>\r\n        </tr>\r\n		\r\n\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-23 13:00:00', 1, NULL, NULL, NULL, NULL),
(5, 'Activate Account', 'Start Your Free Trial with Legalcase', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Hi {name} </b>, Get started with streamlined case and practice management today! </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: 220px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">START YOUR FREE TRIAL</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">Legalcase is all-in-one legal practice management software for case and matter management, time tracking, billing, and client communication. With your free trial you can: </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               <ul>\r\n				<li>Learn how practice management can help your firm</li>\r\n				<li>Explore the specific features of Legalcase</li>\r\n				<li>See how Legalcase works with real case data</li>\r\n			   </ul>\r\n			   \r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						If the button doesn\'t work, please copy and paste this URL into your browser:<br><a href=\"{token}\">{token}</a></p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(6, 'has Invited You to Join', 'has Invited You to Join', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Hi {name} </b>, {refuser} has created your  account with {site_title}. Get started using {site_title} for streamlined case and practice management today.  </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">Get Started With {site_title}</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">{site_title} is all-in-one legal practice management software for case and matter management. </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						If the button doesn\'t work, please copy and paste this URL into your browser:<br><a href=\"{token}\">{token}</a></p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(7, 'Intake Form', 'Intake Form', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Intake Form</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">{message} </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">View Intake Form</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(8, 'Invoice', 'Invoice', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Invoice</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">{message} </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">View Invoice</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(9, 'Enable Client Portal', 'Please activate your account with\r\n', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Hi {name} </b>, Welcome to the {firm} Client Portal. Activate your account by clicking the button below.  </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">Active Now</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">Our private and secure client portal is available 24 hours a day, 7 days a week for you to:  </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n              <ul>\r\n				  <li>View and upload documents related to your case</li>\r\n				  <li>Send and receive confidential messages</li>\r\n				  <li>Receive notifications about important dates</li>\r\n				  <li>View and print invoices</li>\r\n			  </ul>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">Have questions? Call us {phone_number} </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						If the button doesn\'t work, please copy and paste this URL into your browser:<br><a href=\"{token}\">{token}</a></p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(10, 'Fund Request To Client', 'Fund Request', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Payment Request</b></td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">{message}</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td  style=\"padding: 0px 25px;font-size: 16px;\">\r\n               <span style=\"font-weight:bold\">Deposit Amount: ${amount}</span>\r\n			  \r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td  style=\"padding: 0px 25px;font-size: 16px;\">\r\n              \r\n			   <span style=\"font-size:85%\">Due on: {duedate}</span>\r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications,\r\nPLEASE DO NOT REPLY TO THIS EMAIL.\r\n\r\nThis email was sent to you by {regards} Firm. \r\n						</p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(11, 'Send Message', 'You have a new message on', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">{sender} has sent you a message</td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">Subject: {subject}</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{url}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">Read Message</a>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">For your security, please sign in to your account at:<br> <a href=\"{loginurl}\">{loginurl}</a> to view your message.</td>\r\n        </tr>\r\n		\r\n	<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n\r\n\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications,\r\nPLEASE DO NOT REPLY TO THIS EMAIL.\r\n\r\nThis email was sent to you by {regards} Firm. \r\n						</p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(12, 'Send Invoice Reminder', 'Send Invoice Reminder', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">Payment Reminder:<br>\r\n			You have an invoice waiting for you </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: auto;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;padding: 0 10px 0 10px\">View Invoice</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\"><b>View your invoice online in just a few clicks.</b> </td>\r\n        </tr>\r\n		\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>\r\n						 </p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(13, 'Share Invoice', 'Share Invoice', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">Please review your attached invoice. </td>\r\n        </tr>\r\n		\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>\r\n						 </p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2021-01-27 18:30:00', 1, NULL, NULL, NULL, NULL),
(15, 'Custom Email', 'Custom Email', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">{message}</td>\r\n        </tr>\r\n		\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>\r\n						 </p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2021-01-27 18:30:00', 1, NULL, NULL, NULL, NULL),
(16, 'Send Reminder for updated invoice', 'Send Reminder for updated invoice', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n		    <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 0px;font-size: 16px;\"><b>Hi {name} </b>,</td>\r\n        </tr>\r\n		\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 15px 23px 1px;font-size: 16px;\">\r\n			 Invoice #{invoice} has been updated. \r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: auto;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;padding: 0 10px 0 10px\">View Invoice</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\"> For additional details about this invoice, sign in to your client portal at:<br> {loginurl} </td>\r\n        </tr>\r\n		\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>\r\n						 </p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL);
INSERT INTO `email_template` (`id`, `title`, `subject`, `content`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(17, 'Fund Request Reminder To Client', 'Fund Request Reminder', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Payment Reminder:</b></td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">You have a request due {message}</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Please deposit funds into your account.\r\n			<br>\r\n            </td>\r\n        </tr>\r\n			<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td  style=\"padding: 0px 25px;font-size: 16px;\">\r\n               <span style=\"font-weight:bold\">Deposit Amount: ${amount}</span>\r\n			  \r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td  style=\"padding: 0px 25px;font-size: 16px;\">\r\n              \r\n			   <span style=\"font-size:85%\">Due on: {duedate}</span>\r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications,\r\nPLEASE DO NOT REPLY TO THIS EMAIL.\r\n\r\nThis email was sent to you by {regards} Firm. \r\n						</p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(18, 'Lead form submit response', 'Thank you for contacting us\r\n', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><h2>Thank you for your inquiry.</h2>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\"> <p>\r\n                                        Our team will reply to you very shortly.<br>\r\n                                        We look forward to working with you.\r\n                                    </p></td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thank you,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications,\r\nPLEASE DO NOT REPLY TO THIS EMAIL.\r\n\r\nThis email was sent to you by {regards} Firm. \r\n						</p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(19, 'An Intake Form has been submitted\r\n', 'An Intake Form has been submitted\r\n', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Intake Form</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">Hi {receiver}, <br>\r\n				A new potential client just submitted the Contact Us form.\r\n\r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{url}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">View Potential Client</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n			  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications,\r\nPLEASE DO NOT REPLY TO THIS EMAIL.\r\n\r\n						</p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(20, 'Share Invoice with client', 'Share Invoice with client', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\">Hi {name}<br>\r\n			You have been sent an invoice.\r\n </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: auto;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;padding: 0 10px 0 10px\">View Invoice</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\"><b>View your invoice online in just a few clicks.</b> </td>\r\n        </tr>\r\n		\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n	\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>\r\n						 </p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL),
(21, 'Create client account and received email', 'Please activate your account with \r\n', '\r\n<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Legalcase : Welcome Email</title>\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  \r\n	<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700\" rel=\"stylesheet\">\r\n</head>\r\n<body style=\"font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;\" data-gr-c-s-loaded=\"true\">\r\n    <table style=\"border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;\">\r\n        <tr style=\"background: #DCDCDC;\">\r\n			<td style=\"text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;\">\r\n				<a href=\"{EmailLinkOnLogo}\" style=\"text-decoration: none;\" target=\"_blank\">\r\n					<img src=\"{EmailLogo1}\"  style=\"display: inline-block;vertical-align: middle;width: 100px;height: 100px;\">\r\n				</a>\r\n			</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"color: #1c2224;padding: 45px 25px 25px;font-size: 16px;\"><b>Hi {name} </b>, \r\n			Welcome to the {refuser} Client Portal. Activate your account by clicking the button below.\r\n			</td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"text-align: center;padding-top: 0px;padding-bottom: 0px;\">\r\n               <a href=\"{token}\" style=\"width: 260px;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;\">Activate Now</a>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">Our private and secure client portal is available 24 hours a day, 7 days a week for you to:</td>\r\n        </tr>\r\n			<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n				<ul>\r\n					<li>View and upload documents related to your case</li>\r\n					<li>Send and receive confidential messages</li>\r\n					<li>Receive notifications about important dates</li>\r\n					<li>View and print invoices</li>\r\n				</ul>\r\n			</td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;<br>\r\n            </td>\r\n        </tr>\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               Thanks,\r\n			   <br>{regards}.\r\n            </td>\r\n        </tr>\r\n		\r\n		<tr>\r\n            <td colspan=\"2\" style=\"padding: 0px 25px;font-size: 16px;\">\r\n               &nbsp;\r\n            </td>\r\n        </tr>\r\n		  \r\n		<tr>\r\n			<td style=\"color: #7a7a7a;background: #DCDCDC;\">\r\n				<div style=\"padding: 5px;\">\r\n					<div style=\"max-width: 100%;font-size: 14px;color: #7a7a7a;\">\r\n						<p style=\"margin: 25px;text-align:center;\">\r\n						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>\r\n						 </p>\r\n						<p style=\"margin: 25px;text-align:center;\">© {year} {regards}</p>\r\n					</div>\r\n				</div>\r\n			</td>\r\n		</tr>\r\n        </tbody>\r\n    </table>\r\n</body>\r\n</html>', '2020-06-07 18:30:00', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_type`
--

CREATE TABLE `event_type` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `status_order` int DEFAULT NULL,
  `firm_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `expense_entry`
--

CREATE TABLE `expense_entry` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `time_entry_billable` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `entry_date` date DEFAULT NULL,
  `cost` decimal(11,2) DEFAULT NULL,
  `duration` double(11,2) DEFAULT NULL,
  `status` enum('paid','unpaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `invoice_link` int DEFAULT NULL,
  `remove_from_current_invoice` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `token_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_for_invoice`
--

CREATE TABLE `expense_for_invoice` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `expense_entry_id` int DEFAULT NULL COMMENT 'ref:  expense_entry->id',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `firm`
--

CREATE TABLE `firm` (
  `id` int NOT NULL,
  `firm_name` varchar(512) DEFAULT NULL,
  `parent_user_id` int NOT NULL COMMENT 'user->id',
  `client_portal_access` enum('yes','no') NOT NULL DEFAULT 'no',
  `sol` enum('yes','no') NOT NULL DEFAULT 'no',
  `firm_logo` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `firm_address`
--

CREATE TABLE `firm_address` (
  `id` int UNSIGNED NOT NULL,
  `office_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax_line` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apt_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` int DEFAULT NULL,
  `firm_id` int DEFAULT NULL,
  `is_primary` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `firm_event_reminder`
--

CREATE TABLE `firm_event_reminder` (
  `id` int UNSIGNED NOT NULL,
  `firm_id` int DEFAULT NULL COMMENT 'ref : firm->id',
  `reminder_type` enum('popup','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_user_type` enum('me','attorney','paralegal','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminer_number` int DEFAULT NULL,
  `reminder_frequncy` enum('minute','hour','day','week') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `firm_sol_reminder`
--

CREATE TABLE `firm_sol_reminder` (
  `id` int UNSIGNED NOT NULL,
  `firm_id` int DEFAULT NULL COMMENT 'ref : firm->id',
  `reminder_type` enum('popup','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminer_days` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `flat_fee_entry`
--

CREATE TABLE `flat_fee_entry` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `time_entry_billable` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `entry_date` date DEFAULT NULL,
  `cost` decimal(11,2) DEFAULT NULL,
  `status` enum('paid','unpaid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `invoice_link` int DEFAULT NULL,
  `remove_from_current_invoice` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `token_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flat_fee_entry_for_invoice`
--

CREATE TABLE `flat_fee_entry_for_invoice` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `flat_fee_entry_id` int DEFAULT NULL COMMENT 'ref : flat_fee_entry->id\r\n',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `intake_form`
--

CREATE TABLE `intake_form` (
  `id` int UNSIGNED NOT NULL,
  `form_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: incomplete 1: Complete',
  `form_unique_id` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_font_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_font_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_font` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_font` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firm_name` int DEFAULT NULL,
  `form_type` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0: regular 1:Contact us',
  `domain_url` varchar(512) DEFAULT NULL,
  `authorised_domain` enum('yes','no') NOT NULL DEFAULT 'no',
  `send_confimation_mail` enum('yes','no') NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `intake_form_domain`
--

CREATE TABLE `intake_form_domain` (
  `id` int UNSIGNED NOT NULL,
  `form_id` int DEFAULT NULL,
  `domain_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intake_form_fields`
--

CREATE TABLE `intake_form_fields` (
  `id` int UNSIGNED NOT NULL,
  `intake_form_id` int NOT NULL COMMENT 'ref : intake_form->id',
  `form_category` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `header_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `form_field` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `client_friendly_lable` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_required` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'yes',
  `extra_value` text,
  `sort_order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `case_id` int DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(20,2) DEFAULT '0.00',
  `paid_amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(20,2) DEFAULT '0.00',
  `due_date` date DEFAULT NULL,
  `is_viewed` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `is_sent` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `reminder_sent_counter` int NOT NULL DEFAULT '0',
  `reminder_viewed_on` datetime DEFAULT NULL,
  `last_reminder_sent_on` datetime DEFAULT NULL,
  `status` enum('Sent','Unsent','Partial','Paid','Draft','Forwarded','Overdue') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unsent',
  `payment_term` enum('0','1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '5' COMMENT '0 : Due date 1: Due on receipt 2: Net 15 3: Net 30 4: Net 60 5: Not Set',
  `automated_reminder` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `terms_condition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_plan_enabled` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `invoice_unique_token` text,
  `invoice_token` varchar(512) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_adjustment`
--

CREATE TABLE `invoice_adjustment` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `item` enum('discount','intrest','tax','addition','none') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `applied_to` enum('flat_fees','time_entries','expenses','balance_forward_total','sub_total','none') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `ad_type` enum('percentage','amount','none') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `basis` decimal(11,2) DEFAULT '0.00',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `percentages` float DEFAULT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_batch`
--

CREATE TABLE `invoice_batch` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ref : invoices-> id',
  `batch_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_invoice` int NOT NULL DEFAULT '0',
  `draft_invoice` int NOT NULL DEFAULT '0',
  `unsent_invoice` int NOT NULL DEFAULT '0',
  `sent_invoice` int NOT NULL DEFAULT '0',
  `firm_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_history`
--

CREATE TABLE `invoice_history` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `lead_invoice_id` int DEFAULT NULL,
  `lead_id` int DEFAULT NULL,
  `lead_message` text,
  `acrtivity_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pay_method` varchar(255) DEFAULT NULL,
  `amount` decimal(20,2) DEFAULT NULL,
  `responsible_user` int DEFAULT NULL,
  `deposit_into` varchar(512) DEFAULT NULL,
  `deposit_into_id` int DEFAULT NULL,
  `invoice_payment_id` int DEFAULT NULL COMMENT 'ref: : invoice_payment->id',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('0','1','2','3','4') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0 : None 1: Deposit 2: Full Refund 3.Partial Refund 4: Refund Entry',
  `refund_ref_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_installment`
--

CREATE TABLE `invoice_installment` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `installment_amount` decimal(11,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `firm_id` int NOT NULL,
  `status` enum('paid','unpaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `paid_date` datetime DEFAULT NULL,
  `pay_type` enum('auto','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `adjustment` decimal(11,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payment`
--

CREATE TABLE `invoice_payment` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int NOT NULL COMMENT 'ref : :invoice->id',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(11,2) DEFAULT NULL,
  `amount_refund` decimal(11,2) NOT NULL DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `deposit_into` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_into_id` int DEFAULT NULL,
  `payment_from_id` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_from` enum('trust','client') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0: Payment 1: Refund 2:None',
  `total` decimal(11,2) NOT NULL DEFAULT '0.00',
  `firm_id` int DEFAULT NULL,
  `ip_unique_id` varchar(255) DEFAULT NULL,
  `refund_ref_id` int DEFAULT NULL,
  `entry_type` enum('0','1','2') NOT NULL DEFAULT '2' COMMENT '0: Trust 1: Opersting 2:None',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payment_plan`
--

CREATE TABLE `invoice_payment_plan` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `per_installment_amt` decimal(11,2) NOT NULL,
  `no_of_installment` int NOT NULL,
  `repeat_by` enum('weekly','biweekly','monthly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'weekly',
  `is_set_first_installment` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `first_installment_amount` decimal(11,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_additional_info`
--

CREATE TABLE `lead_additional_info` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `address2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `driver_license` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referal_source` int DEFAULT NULL COMMENT 'ref : referal_resource->id',
  `refered_by` int DEFAULT NULL COMMENT 'Ref : users -> id ',
  `lead_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `potential_case_title` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_added` date NOT NULL,
  `lead_status` int DEFAULT NULL COMMENT 'ref: lead_status->id',
  `practice_area` int DEFAULT NULL COMMENT 'ref: case_practice_area->id',
  `potential_case_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `assigned_to` int DEFAULT NULL COMMENT 'ref : user->id',
  `office` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: primary 0:''''',
  `potential_case_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1 : Active 2: Did No Hire 3:Converted',
  `conflict_check` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `conflict_check_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `firm_id` int DEFAULT NULL,
  `do_not_hire_reason` int DEFAULT NULL,
  `do_not_hire_on` date DEFAULT NULL,
  `conflict_check_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `client_portal_enable` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT ' 0: No 1:Yes ',
  `contact_group_id` int NOT NULL DEFAULT '1',
  `is_converted` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `converted_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lead_case_activity_history`
--

CREATE TABLE `lead_case_activity_history` (
  `id` int UNSIGNED NOT NULL,
  `acrtivity_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `activity_by` int DEFAULT NULL,
  `for_lead` int DEFAULT NULL,
  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1 : Task 2: Case',
  `task_id` int DEFAULT NULL,
  `case_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lead_notes`
--

CREATE TABLE `lead_notes` (
  `id` int UNSIGNED NOT NULL,
  `notes_for` int DEFAULT NULL,
  `note_date` date DEFAULT NULL,
  `note_activity` int DEFAULT NULL COMMENT 'ref : lead_notes_activity->id',
  `note_subject` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lead_notes_activity`
--

CREATE TABLE `lead_notes_activity` (
  `id` int UNSIGNED NOT NULL,
  `acrtivity_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lead_notes_activity_history`
--

CREATE TABLE `lead_notes_activity_history` (
  `id` int UNSIGNED NOT NULL,
  `acrtivity_title` enum('added','edited','deleted','added a lead','edited a lead','deleted a lead') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_by` int DEFAULT NULL,
  `for_lead` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lead_status`
--

CREATE TABLE `lead_status` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `status_order` int DEFAULT NULL,
  `firm_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref : case_master->id',
  `user_id` text,
  `replies_is` enum('private','public') DEFAULT 'public',
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `not_hire_reasons`
--

CREATE TABLE `not_hire_reasons` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `firm_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `online_lead_submit`
--

CREATE TABLE `online_lead_submit` (
  `id` int UNSIGNED NOT NULL,
  `intake_form_id` int NOT NULL COMMENT 'ref : case_intake_form->intake_form_id',
  `email` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firm_id` int DEFAULT NULL,
  `case_intake_form_fields_data_id` int NOT NULL,
  `unique_token` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plan_history`
--

CREATE TABLE `plan_history` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `plan_type` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0:free 1:paid',
  `status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1 : Active 2: Inactive 3:Suspended',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `potential_case_invoice`
--

CREATE TABLE `potential_case_invoice` (
  `id` int UNSIGNED NOT NULL,
  `invoice_unique_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lead_id` int NOT NULL,
  `invoice_number` int DEFAULT NULL,
  `invoice_amount` decimal(20,2) DEFAULT NULL,
  `amount_paid` decimal(20,2) DEFAULT NULL COMMENT 'ref : lead_notes_activity->id',
  `due_date` date NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1: Sent 2: Unsent',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `potential_case_invoice_payment`
--

CREATE TABLE `potential_case_invoice_payment` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ref : : potential_case_invoice->id',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(11,2) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `deposit_into` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `potential_case_payment`
--

CREATE TABLE `potential_case_payment` (
  `id` int UNSIGNED NOT NULL,
  `invoice_unique_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lead_id` int NOT NULL,
  `invoice_number` int DEFAULT NULL,
  `invoice_amount` decimal(11,2) DEFAULT NULL,
  `amount_paid` decimal(11,2) DEFAULT NULL COMMENT '\\',
  `due_date` date NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1: Sent 2: Unsent',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `referal_resource`
--

CREATE TABLE `referal_resource` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `stage_order` int DEFAULT NULL,
  `firm_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `requested_fund`
--

CREATE TABLE `requested_fund` (
  `id` int UNSIGNED NOT NULL,
  `client_id` int DEFAULT NULL COMMENT 'client id',
  `deposit_into` int DEFAULT NULL COMMENT 'trust account id',
  `amount_requested` decimal(11,2) DEFAULT '0.00',
  `amount_due` decimal(11,2) DEFAULT '0.00',
  `amount_paid` decimal(11,2) DEFAULT '0.00',
  `payment_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `email_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('sent','unsent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `is_viewed` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `reminder_sent_counter` int NOT NULL DEFAULT '0',
  `last_reminder_sent_on` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `shared_invoice`
--

CREATE TABLE `shared_invoice` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL COMMENT 'ref:  users->id',
  `is_viewed` enum('yes','no') NOT NULL DEFAULT 'no',
  `last_reminder_sent_on` datetime DEFAULT NULL,
  `reminder_sent_counter` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref: case->id',
  `lead_id` int DEFAULT NULL,
  `no_case_link` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `task_title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_due_on` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `task_priority` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1: Low 2: Medium 3:High',
  `task_assign_to` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `time_tracking_enabled` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `status` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: incomplete 1: Complete',
  `task_completed_by` int DEFAULT NULL,
  `task_completed_date` datetime DEFAULT NULL,
  `task_read` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task-DVDBK`
--

CREATE TABLE `task-DVDBK` (
  `id` int UNSIGNED NOT NULL,
  `case_id` int DEFAULT NULL COMMENT 'ref: case->id',
  `lead_id` int DEFAULT NULL,
  `no_case_link` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `task_title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_due_on` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `task_priority` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1: Low 2: Medium 3:High',
  `task_assign_to` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `time_tracking_enabled` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `status` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: incomplete 1: Complete',
  `task_completed_by` int DEFAULT NULL,
  `task_completed_date` datetime DEFAULT NULL,
  `task_read` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_activity`
--

CREATE TABLE `task_activity` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `firm_id` int DEFAULT NULL,
  `flat_fees` decimal(11,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_checklist`
--

CREATE TABLE `task_checklist` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int DEFAULT NULL,
  `title` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checklist_order` int NOT NULL,
  `status` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0 : Pending 1: Done',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_comment`
--

CREATE TABLE `task_comment` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int NOT NULL,
  `title` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1: Active 2: Inactive ',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int DEFAULT NULL COMMENT 'ref : task->id',
  `task_action` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_linked_staff`
--

CREATE TABLE `task_linked_staff` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `time_estimate_total` int DEFAULT NULL,
  `linked_or_not_with_case` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_reminder`
--

CREATE TABLE `task_reminder` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int DEFAULT NULL COMMENT 'ref : task->id',
  `reminder_type` enum('popup','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_user_type` enum('me','attorney','paralegal','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminer_number` int DEFAULT NULL,
  `reminder_frequncy` enum('minute','hour','day','week') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_time_entry`
--

CREATE TABLE `task_time_entry` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int DEFAULT NULL COMMENT 'ref : task->id',
  `case_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `time_entry_billable` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `entry_date` date DEFAULT NULL,
  `entry_rate` decimal(11,2) DEFAULT NULL,
  `rate_type` enum('hr','flat') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` double(11,2) DEFAULT NULL,
  `status` enum('paid','unpaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `invoice_link` int DEFAULT NULL,
  `remove_from_current_invoice` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `token_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_user_selection`
--

CREATE TABLE `temp_user_selection` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `selected_user` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `time_entry_for_invoice`
--

CREATE TABLE `time_entry_for_invoice` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `time_entry_id` int DEFAULT NULL COMMENT 'ref:  task_time_entry->id',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trust_history`
--

CREATE TABLE `trust_history` (
  `id` int UNSIGNED NOT NULL,
  `client_id` int DEFAULT NULL COMMENT 'client id',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(11,2) DEFAULT '0.00',
  `withdraw_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `withdraw_from_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fund_type` enum('diposit','withdraw','refund_withdraw','refund_deposit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_trust_balance` decimal(11,2) DEFAULT '0.00',
  `refund_ref_id` int DEFAULT NULL,
  `is_refunded` enum('yes','no','maybe') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'maybe',
  `refund_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` enum('1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1:Attorney 2: Paralegal 3:Staff 4: None 5:Lead',
  `user_level` enum('1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '3' COMMENT '1 : Admin 2:Clien 3:User 4: Company 5: Lead',
  `user_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `default_rate` decimal(11,2) DEFAULT NULL,
  `mobile_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `user_status` enum('1','2','3','4') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1 : Active 2: Inactive 3:Suspended, 4:Archive',
  `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: Pending Verification 1:Verified',
  `firm_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apt_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` int DEFAULT NULL,
  `work_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_user_to` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '1 : no case 2: all active case 3: specific case',
  `sharing_setting_1` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: none 1: Add all case events to user''s calendar',
  `sharing_setting_2` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: none 1:Share all open and completed case tasks with this user',
  `sharing_setting_3` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0:none 1: Mark all items as read (only available when a specific case is selected)',
  `case_rate` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0 :  Use lawyer default rate 1: Specify a default rate for this case',
  `rate_amount` decimal(10,2) DEFAULT NULL COMMENT 'This will enabled if only case_rate=1 selected ',
  `default_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#00cdd2',
  `last_login` datetime DEFAULT NULL,
  `is_sent_welcome_email` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0: No 1:Yes',
  `profile_image` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` enum('yes','no') NOT NULL DEFAULT 'no',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_no` int DEFAULT NULL COMMENT ' Number of Firm Employees ',
  `parent_user` int NOT NULL DEFAULT '0' COMMENT '0 : Parent User ',
  `add_task_guide` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: Show 1: hide',
  `add_event_guide2` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0: Show 1: hide ',
  `add_event_guide` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0: Show 1: hide ',
  `set_goal` decimal(11,2) NOT NULL DEFAULT '0.00',
  `goal_frequency` enum('daily','monthly','weekly') DEFAULT NULL,
  `credit_account` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Enabled 1:Disabled',
  `user_role` int NOT NULL DEFAULT '0' COMMENT 'ref=user_role->id',
  `welcome_page_widget_is_display` enum('yes','no') NOT NULL DEFAULT 'yes',
  `last_seen_at` datetime DEFAULT NULL,
  `sessionTime` int NOT NULL DEFAULT '15' COMMENT 'in minutes',
  `started_tips` enum('on','off') DEFAULT NULL,
  `auto_logout` enum('on','off') DEFAULT NULL,
  `dont_logout_while_timer_runnig` enum('on','off') DEFAULT NULL,
  `contact_us_widget` enum('yes','no') NOT NULL DEFAULT 'yes',
  `bulk_id` int DEFAULT NULL,
  `popup_after_first_case` enum('yes','no') NOT NULL DEFAULT 'yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `usersold`
--

CREATE TABLE `usersold` (
  `id` int UNSIGNED NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_no` int NOT NULL DEFAULT '0' COMMENT 'Number of Firm Employees',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT '1 : Admin 2:Clien 3:User',
  `user_timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `user_status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1 : Active 2: Inactive 3:Suspended',
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: Pending Verification 1:Verified',
  `firm_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apt_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` int DEFAULT NULL,
  `work_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_user` int DEFAULT '0' COMMENT '0 : Parent User',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_additional_info`
--

CREATE TABLE `users_additional_info` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'Ref : users -> id',
  `contact_group_id` int DEFAULT NULL COMMENT 'ref : contact_group->id',
  `company_id` int DEFAULT NULL COMMENT 'Ref : users -> id (User type is company)',
  `multiple_compnay_id` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_status` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT '1 : Active 2: Inactive 3:Suspended',
  `client_portal_enable` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0: No 1:Yes',
  `grant_access` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `address2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_license` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax_number` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_trust_balance` decimal(11,2) DEFAULT '0.00',
  `trust_account_balance` decimal(11,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_detail`
--

CREATE TABLE `users_detail` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL COMMENT 'Ref : users -> id',
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apt_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` int NOT NULL,
  `home_phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3',
  `user_timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: Pending Verification 1:Verified',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferance_reminder`
--

CREATE TABLE `user_preferance_reminder` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `reminder_type` enum('popup','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminer_number` int DEFAULT NULL,
  `type` enum('task','event') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_frequncy` enum('minute','hour','day','week') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `id` int NOT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `firm_id` int NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Inactive 1: Active',
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_case_state`
-- (See below for the actual view)
--
CREATE TABLE `view_case_state` (
`case_event_counter` bigint
,`case_expenseentry_counter` bigint
,`case_invoice_counter` bigint
,`case_note_counter` bigint
,`case_task_counter` bigint
,`case_timeentry_counter` bigint
,`id` int unsigned
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_client_linked_state`
-- (See below for the actual view)
--
CREATE TABLE `view_client_linked_state` (
`client_linked_with_case_counter` bigint
,`user_id` int unsigned
);

-- --------------------------------------------------------

--
-- Structure for view `view_case_state`
--
DROP TABLE IF EXISTS `view_case_state`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_case_state`  AS  select `case_master`.`id` AS `id`,(select count(0) from `case_events` where (`case_master`.`id` = `case_events`.`case_id`)) AS `case_event_counter`,(select count(0) from `task` where (`case_master`.`id` = `task`.`case_id`)) AS `case_task_counter`,(select count(0) from `task_time_entry` where (`case_master`.`id` = `task_time_entry`.`case_id`)) AS `case_timeentry_counter`,(select count(0) from `expense_entry` where (`case_master`.`id` = `expense_entry`.`case_id`)) AS `case_expenseentry_counter`,(select count(0) from `client_notes` where (`case_master`.`id` = `client_notes`.`case_id`)) AS `case_note_counter`,(select count(0) from `invoices` where (`case_master`.`id` = `invoices`.`case_id`)) AS `case_invoice_counter` from `case_master` ;

-- --------------------------------------------------------

--
-- Structure for view `view_client_linked_state`
--
DROP TABLE IF EXISTS `view_client_linked_state`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_client_linked_state`  AS  select `users`.`id` AS `user_id`,(select count(0) from `case_client_selection` where (`case_client_selection`.`selected_user` = `users`.`id`)) AS `client_linked_with_case_counter` from `users` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_activity`
--
ALTER TABLE `account_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `all_history`
--
ALTER TABLE `all_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calls`
--
ALTER TABLE `calls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_activity`
--
ALTER TABLE `case_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_client_selection`
--
ALTER TABLE `case_client_selection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_events`
--
ALTER TABLE `case_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_event_comment`
--
ALTER TABLE `case_event_comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_event_linked_staff`
--
ALTER TABLE `case_event_linked_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_event_location`
--
ALTER TABLE `case_event_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_event_reminder`
--
ALTER TABLE `case_event_reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_intake_form`
--
ALTER TABLE `case_intake_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_intake_form_fields_data`
--
ALTER TABLE `case_intake_form_fields_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_master`
--
ALTER TABLE `case_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_notes`
--
ALTER TABLE `case_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_practice_area`
--
ALTER TABLE `case_practice_area`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_sol_reminder`
--
ALTER TABLE `case_sol_reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_staff`
--
ALTER TABLE `case_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_stage`
--
ALTER TABLE `case_stage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_stage_history`
--
ALTER TABLE `case_stage_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_update`
--
ALTER TABLE `case_update`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_activity`
--
ALTER TABLE `client_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_company_import`
--
ALTER TABLE `client_company_import`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_company_import_history`
--
ALTER TABLE `client_company_import_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_group`
--
ALTER TABLE `client_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contract_access_permission`
--
ALTER TABLE `contract_access_permission`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contract_user_case`
--
ALTER TABLE `contract_user_case`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contract_user_permission`
--
ALTER TABLE `contract_user_permission`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deactivated_user`
--
ALTER TABLE `deactivated_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`assigned_to`);

--
-- Indexes for table `deposit_into_credit_history`
--
ALTER TABLE `deposit_into_credit_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposit_into_trust`
--
ALTER TABLE `deposit_into_trust`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_master`
--
ALTER TABLE `document_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_template`
--
ALTER TABLE `email_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_type`
--
ALTER TABLE `event_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_entry`
--
ALTER TABLE `expense_entry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_for_invoice`
--
ALTER TABLE `expense_for_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `firm`
--
ALTER TABLE `firm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `firm_address`
--
ALTER TABLE `firm_address`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `firm_event_reminder`
--
ALTER TABLE `firm_event_reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `firm_sol_reminder`
--
ALTER TABLE `firm_sol_reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flat_fee_entry`
--
ALTER TABLE `flat_fee_entry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flat_fee_entry_for_invoice`
--
ALTER TABLE `flat_fee_entry_for_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `intake_form`
--
ALTER TABLE `intake_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `intake_form_domain`
--
ALTER TABLE `intake_form_domain`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `intake_form_fields`
--
ALTER TABLE `intake_form_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_adjustment`
--
ALTER TABLE `invoice_adjustment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_batch`
--
ALTER TABLE `invoice_batch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_history`
--
ALTER TABLE `invoice_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_installment`
--
ALTER TABLE `invoice_installment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_payment`
--
ALTER TABLE `invoice_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_payment_plan`
--
ALTER TABLE `invoice_payment_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_additional_info`
--
ALTER TABLE `lead_additional_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_case_activity_history`
--
ALTER TABLE `lead_case_activity_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_notes`
--
ALTER TABLE `lead_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_notes_activity`
--
ALTER TABLE `lead_notes_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_notes_activity_history`
--
ALTER TABLE `lead_notes_activity_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_status`
--
ALTER TABLE `lead_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `not_hire_reasons`
--
ALTER TABLE `not_hire_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `online_lead_submit`
--
ALTER TABLE `online_lead_submit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `plan_history`
--
ALTER TABLE `plan_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potential_case_invoice`
--
ALTER TABLE `potential_case_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potential_case_invoice_payment`
--
ALTER TABLE `potential_case_invoice_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potential_case_payment`
--
ALTER TABLE `potential_case_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referal_resource`
--
ALTER TABLE `referal_resource`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requested_fund`
--
ALTER TABLE `requested_fund`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shared_invoice`
--
ALTER TABLE `shared_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task-DVDBK`
--
ALTER TABLE `task-DVDBK`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_activity`
--
ALTER TABLE `task_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_checklist`
--
ALTER TABLE `task_checklist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_comment`
--
ALTER TABLE `task_comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_linked_staff`
--
ALTER TABLE `task_linked_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_reminder`
--
ALTER TABLE `task_reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_time_entry`
--
ALTER TABLE `task_time_entry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_user_selection`
--
ALTER TABLE `temp_user_selection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_entry_for_invoice`
--
ALTER TABLE `time_entry_for_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trust_history`
--
ALTER TABLE `trust_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usersold`
--
ALTER TABLE `usersold`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `users_additional_info`
--
ALTER TABLE `users_additional_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_detail`
--
ALTER TABLE `users_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`city`);

--
-- Indexes for table `user_preferance_reminder`
--
ALTER TABLE `user_preferance_reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_activity`
--
ALTER TABLE `account_activity`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `all_history`
--
ALTER TABLE `all_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calls`
--
ALTER TABLE `calls`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_activity`
--
ALTER TABLE `case_activity`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_client_selection`
--
ALTER TABLE `case_client_selection`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_events`
--
ALTER TABLE `case_events`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_event_comment`
--
ALTER TABLE `case_event_comment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_event_linked_staff`
--
ALTER TABLE `case_event_linked_staff`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_event_location`
--
ALTER TABLE `case_event_location`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_event_reminder`
--
ALTER TABLE `case_event_reminder`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_intake_form`
--
ALTER TABLE `case_intake_form`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_intake_form_fields_data`
--
ALTER TABLE `case_intake_form_fields_data`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_master`
--
ALTER TABLE `case_master`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_notes`
--
ALTER TABLE `case_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_practice_area`
--
ALTER TABLE `case_practice_area`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_sol_reminder`
--
ALTER TABLE `case_sol_reminder`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_staff`
--
ALTER TABLE `case_staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_stage`
--
ALTER TABLE `case_stage`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_stage_history`
--
ALTER TABLE `case_stage_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_update`
--
ALTER TABLE `case_update`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_activity`
--
ALTER TABLE `client_activity`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_company_import`
--
ALTER TABLE `client_company_import`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_company_import_history`
--
ALTER TABLE `client_company_import_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_group`
--
ALTER TABLE `client_group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_notes`
--
ALTER TABLE `client_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_access_permission`
--
ALTER TABLE `contract_access_permission`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_user_case`
--
ALTER TABLE `contract_user_case`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_user_permission`
--
ALTER TABLE `contract_user_permission`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deactivated_user`
--
ALTER TABLE `deactivated_user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposit_into_credit_history`
--
ALTER TABLE `deposit_into_credit_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposit_into_trust`
--
ALTER TABLE `deposit_into_trust`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_master`
--
ALTER TABLE `document_master`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_template`
--
ALTER TABLE `email_template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `event_type`
--
ALTER TABLE `event_type`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_entry`
--
ALTER TABLE `expense_entry`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_for_invoice`
--
ALTER TABLE `expense_for_invoice`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firm`
--
ALTER TABLE `firm`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firm_address`
--
ALTER TABLE `firm_address`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firm_event_reminder`
--
ALTER TABLE `firm_event_reminder`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firm_sol_reminder`
--
ALTER TABLE `firm_sol_reminder`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flat_fee_entry`
--
ALTER TABLE `flat_fee_entry`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flat_fee_entry_for_invoice`
--
ALTER TABLE `flat_fee_entry_for_invoice`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intake_form`
--
ALTER TABLE `intake_form`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intake_form_domain`
--
ALTER TABLE `intake_form_domain`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intake_form_fields`
--
ALTER TABLE `intake_form_fields`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_adjustment`
--
ALTER TABLE `invoice_adjustment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_batch`
--
ALTER TABLE `invoice_batch`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_history`
--
ALTER TABLE `invoice_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_installment`
--
ALTER TABLE `invoice_installment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_payment`
--
ALTER TABLE `invoice_payment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_payment_plan`
--
ALTER TABLE `invoice_payment_plan`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_additional_info`
--
ALTER TABLE `lead_additional_info`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_case_activity_history`
--
ALTER TABLE `lead_case_activity_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_notes`
--
ALTER TABLE `lead_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_notes_activity`
--
ALTER TABLE `lead_notes_activity`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_notes_activity_history`
--
ALTER TABLE `lead_notes_activity_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_status`
--
ALTER TABLE `lead_status`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `not_hire_reasons`
--
ALTER TABLE `not_hire_reasons`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_lead_submit`
--
ALTER TABLE `online_lead_submit`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plan_history`
--
ALTER TABLE `plan_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potential_case_invoice`
--
ALTER TABLE `potential_case_invoice`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potential_case_invoice_payment`
--
ALTER TABLE `potential_case_invoice_payment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potential_case_payment`
--
ALTER TABLE `potential_case_payment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referal_resource`
--
ALTER TABLE `referal_resource`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requested_fund`
--
ALTER TABLE `requested_fund`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shared_invoice`
--
ALTER TABLE `shared_invoice`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task-DVDBK`
--
ALTER TABLE `task-DVDBK`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_activity`
--
ALTER TABLE `task_activity`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_checklist`
--
ALTER TABLE `task_checklist`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_comment`
--
ALTER TABLE `task_comment`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_linked_staff`
--
ALTER TABLE `task_linked_staff`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_reminder`
--
ALTER TABLE `task_reminder`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_time_entry`
--
ALTER TABLE `task_time_entry`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_user_selection`
--
ALTER TABLE `temp_user_selection`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_entry_for_invoice`
--
ALTER TABLE `time_entry_for_invoice`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trust_history`
--
ALTER TABLE `trust_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usersold`
--
ALTER TABLE `usersold`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_additional_info`
--
ALTER TABLE `users_additional_info`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_detail`
--
ALTER TABLE `users_detail`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_preferance_reminder`
--
ALTER TABLE `user_preferance_reminder`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
