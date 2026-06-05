-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2026 at 04:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fleet_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_user_id` int(10) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('manual','inspection','maintenance','system') NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communication_recipients`
--

CREATE TABLE `communication_recipients` (
  `id` int(10) UNSIGNED NOT NULL,
  `communication_id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_name` varchar(120) DEFAULT NULL,
  `recipient_email` varchar(150) NOT NULL,
  `recipient_type` enum('driver','officer','external') NOT NULL DEFAULT 'officer',
  `delivery_status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractors`
--

CREATE TABLE `contractors` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(120) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `town` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Unassigned', 'UNASSIGNED', '2026-05-28 19:14:05', NULL),
(2, 'Transport', 'TRANSPORT', '2026-05-28 19:14:05', NULL),
(3, 'Estates', 'ESTATES', '2026-05-28 19:14:05', NULL),
(4, 'University Secretary', 'US', '2026-05-28 19:14:05', NULL),
(5, 'Vice Chancellor', 'VC', '2026-05-28 19:14:05', NULL),
(6, 'DVD fa', 'DVDFA', '2026-05-28 19:14:05', NULL),
(7, 'Library', 'L-CDD901', '2026-05-28 19:47:45', NULL),
(8, 'ffdf', 'DEPT-5CF05C', '2026-05-28 21:14:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `employee_id` varchar(60) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `national_id_number` varchar(60) DEFAULT NULL,
  `license_number` varchar(100) NOT NULL,
  `license_classes` varchar(80) DEFAULT NULL,
  `license_issue_date` date DEFAULT NULL,
  `license_issuing_authority` varchar(150) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `driver_photo` varchar(255) DEFAULT NULL,
  `national_id_photo` varchar(255) DEFAULT NULL,
  `driving_license_scan` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `user_id`, `department_id`, `full_name`, `employee_id`, `phone`, `email`, `gender`, `national_id_number`, `license_number`, `license_classes`, `license_issue_date`, `license_issuing_authority`, `license_expiry`, `driver_photo`, `national_id_photo`, `driving_license_scan`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 2, 'Simali Habert', NULL, '+256 772 123 456', 'simalihabert@gmail.com', NULL, NULL, 'CM 78452', 'B', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-05-28 19:14:05', NULL),
(2, NULL, 2, 'Moses Okello', NULL, '+256 701 450 220', 'moses.okello@busitema.ac.ug', NULL, NULL, 'CM 21984', 'B', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-05-28 19:14:05', NULL),
(3, NULL, 2, 'Grace Namuli', NULL, '+256 758 802 114', 'grace.namuli@busitema.ac.ug', NULL, NULL, 'CM 66310', 'B', NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-05-28 19:14:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `estate_projects`
--

CREATE TABLE `estate_projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `contractor_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `project_name` varchar(180) NOT NULL,
  `project_code` varchar(50) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `funding_source` varchar(150) DEFAULT NULL,
  `status` enum('planned','approved','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'planned',
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `budget` decimal(16,2) NOT NULL DEFAULT 0.00,
  `spent` decimal(16,2) NOT NULL DEFAULT 0.00,
  `progress_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `estate_project_updates`
--

CREATE TABLE `estate_project_updates` (
  `id` int(10) UNSIGNED NOT NULL,
  `estate_project_id` int(10) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `update_date` date NOT NULL,
  `progress_percent` tinyint(3) UNSIGNED DEFAULT NULL,
  `amount_spent` decimal(16,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED DEFAULT NULL,
  `inspector_user_id` int(10) UNSIGNED DEFAULT NULL,
  `service_provider_id` int(10) UNSIGNED DEFAULT NULL,
  `inspection_type` enum('pre','post') NOT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `post_invoice_number` varchar(100) DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `inspector_name` varchar(120) NOT NULL,
  `inspector_title` varchar(120) DEFAULT NULL,
  `mileage` int(10) UNSIGNED DEFAULT NULL,
  `overall_status` enum('good','fair','faulty','needs_repair','completed') DEFAULT NULL,
  `defects` text DEFAULT NULL,
  `works_done` text DEFAULT NULL,
  `repair_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `memo_to` varchar(150) DEFAULT NULL,
  `memo_thru_one` varchar(150) DEFAULT NULL,
  `memo_thru_two` varchar(150) DEFAULT NULL,
  `memo_from` varchar(150) DEFAULT NULL,
  `vehicle_description` varchar(255) DEFAULT NULL,
  `closing_note` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_items`
--

CREATE TABLE `inspection_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `inspection_id` int(10) UNSIGNED NOT NULL,
  `inspection_point` varchar(150) NOT NULL,
  `findings` text DEFAULT NULL,
  `action_point` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_parts`
--

CREATE TABLE `maintenance_parts` (
  `id` int(10) UNSIGNED NOT NULL,
  `maintenance_record_id` int(10) UNSIGNED NOT NULL,
  `part_name` varchar(150) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `service_provider_id` int(10) UNSIGNED DEFAULT NULL,
  `reported_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `maintenance_type` enum('repair','routine_service','inspection','brake_service','other') NOT NULL DEFAULT 'repair',
  `date_reported` date NOT NULL,
  `date_completed` date DEFAULT NULL,
  `description` text NOT NULL,
  `parts_replaced` text DEFAULT NULL,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `mileage_at_service` int(10) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `status` enum('reported','in_progress','completed','cancelled') NOT NULL DEFAULT 'reported',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_records`
--

INSERT INTO `maintenance_records` (`id`, `vehicle_id`, `service_provider_id`, `reported_by`, `approved_by`, `maintenance_type`, `date_reported`, `date_completed`, `description`, `parts_replaced`, `total_cost`, `mileage_at_service`, `invoice_number`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 4, NULL, NULL, NULL, 'repair', '2026-05-18', '2026-05-18', 'Engine overhaul, brakes, windscreen and related repairs', NULL, 4200000.00, NULL, NULL, 'completed', NULL, '2026-05-28 19:14:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_inspection_system_checks`
--

CREATE TABLE `post_inspection_system_checks` (
  `id` int(10) UNSIGNED NOT NULL,
  `inspection_id` int(10) UNSIGNED NOT NULL,
  `system_name` varchar(120) NOT NULL,
  `condition_status` enum('good','fair','faulty') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

CREATE TABLE `service_providers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `town` varchar(100) DEFAULT NULL,
  `contact_person` varchar(120) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `specialty` varchar(150) DEFAULT NULL,
  `status` enum('active','pending','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_providers`
--

INSERT INTO `service_providers` (`id`, `name`, `town`, `contact_person`, `phone`, `email`, `specialty`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tororo Auto Garage', 'Tororo', NULL, '+256 701 220 110', 'service@tororoauto.ug', 'General repairs', 'active', '2026-05-28 19:14:05', NULL),
(2, 'Toyota Uganda Service Centre', 'Kampala', NULL, '+256 414 339 000', 'fleetservice@toyota.co.ug', 'Toyota service', 'active', '2026-05-28 19:14:05', NULL),
(3, 'Mbale Fleet Mechanics', 'Mbale', NULL, '+256 772 431 980', 'info@mbalefleet.ug', 'Brakes and suspension', 'pending', '2026-05-28 19:14:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','transport_officer','driver','maintenance_officer','estates_officer') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `registration_no` varchar(50) NOT NULL,
  `make` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacture_year` year(4) DEFAULT NULL,
  `vehicle_type` enum('sedan','suv','pickup','truck','van','bus','motorcycle','other') NOT NULL DEFAULT 'other',
  `fuel_type` enum('petrol','diesel','hybrid','electric','other') NOT NULL DEFAULT 'diesel',
  `current_mileage` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `insurance_expiry` date DEFAULT NULL,
  `status` enum('active','maintenance','grounded','disposed') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `department_id`, `registration_no`, `make`, `model`, `manufacture_year`, `vehicle_type`, `fuel_type`, `current_mileage`, `insurance_expiry`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'UBD 456G', 'Toyota', 'Prado', '2022', 'suv', 'diesel', 0, NULL, 'maintenance', NULL, '2026-05-28 19:14:05', NULL),
(2, 6, 'UBR 123C', 'TOYOTA', 'Land cruiser', '2024', 'suv', 'diesel', 852436, NULL, 'active', NULL, '2026-05-28 19:14:05', NULL),
(3, 4, 'UBR 402Q', 'TOYOTA', 'HILLUX PICKUP', '2024', 'pickup', 'diesel', 65231, NULL, 'active', NULL, '2026-05-28 19:14:05', NULL),
(4, 3, 'UAJ 433X', 'Ford', 'Ford ranger', '2009', 'pickup', 'diesel', 196002, NULL, 'active', NULL, '2026-05-28 19:14:05', NULL),
(5, 5, 'UBP 401F', 'TOYOTA', 'LAND CRUISER', '2022', 'suv', 'diesel', 200808, NULL, 'maintenance', NULL, '2026-05-28 19:14:05', NULL),
(6, 7, 'UAX 2342', 'Toyata', 'Land Cruiser2', '2012', 'sedan', 'diesel', 123, NULL, 'maintenance', NULL, '2026-05-28 19:47:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_assignments`
--

CREATE TABLE `vehicle_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED NOT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` date NOT NULL,
  `released_at` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_assignments`
--

INSERT INTO `vehicle_assignments` (`id`, `vehicle_id`, `driver_id`, `assigned_by`, `assigned_at`, `released_at`, `notes`, `created_at`) VALUES
(1, 4, 1, NULL, '2026-05-01', NULL, NULL, '2026-05-28 19:14:05'),
(2, 2, 2, NULL, '2026-05-01', NULL, NULL, '2026-05-28 19:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_logs`
--

CREATE TABLE `vehicle_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `trip_date` date NOT NULL,
  `departure_location` varchar(150) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `odometer_start` int(10) UNSIGNED DEFAULT NULL,
  `odometer_end` int(10) UNSIGNED DEFAULT NULL,
  `distance_km` int(10) UNSIGNED GENERATED ALWAYS AS (case when `odometer_start` is not null and `odometer_end` is not null and `odometer_end` >= `odometer_start` then `odometer_end` - `odometer_start` else NULL end) STORED,
  `fuel_litres` decimal(10,2) DEFAULT NULL,
  `fuel_cost` decimal(14,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_logs`
--

INSERT INTO `vehicle_logs` (`id`, `vehicle_id`, `driver_id`, `created_by`, `trip_date`, `departure_location`, `destination`, `purpose`, `odometer_start`, `odometer_end`, `fuel_litres`, `fuel_cost`, `remarks`, `created_at`, `updated_at`) VALUES
(5, 4, 1, NULL, '2026-05-05', 'Busitema Main Campus', 'Tororo Town', 'Administrative follow-up meeting', 195120, 195168, 8.50, 51000.00, 'Assigned driver routine movement.', '2026-05-05 08:10:00', NULL),
(6, 4, 2, NULL, '2026-05-12', 'Tororo Town', 'Jinja', 'Collection of workshop supplies', 195168, 195322, 21.40, 128000.00, 'Vehicle used by alternate driver for procurement run.', '2026-05-12 07:45:00', NULL),
(7, 4, 1, NULL, '2026-05-26', 'Busitema Main Campus', 'Kampala', 'Senate documentation delivery', 195322, 195560, 30.00, 180000.00, 'Round trip with scheduled stopover.', '2026-05-26 06:30:00', NULL),
(8, 4, 3, NULL, '2026-06-02', 'Busitema Main Campus', 'Mbale', 'Driver induction support visit', 195560, 195702, 18.60, 111600.00, 'Historical entry for reporting by another driver.', '2026-06-02 09:05:00', NULL),
(9, 4, 1, NULL, '2026-06-04', 'Busitema Main Campus', 'Soroti', 'Field supervision and return', 195702, 196002, 36.00, 216000.00, 'Latest mileage reading for UAJ 433X.', '2026-06-04 05:55:00', NULL),
(10, 2, 2, NULL, '2026-05-08', 'Kampala', 'Mbale', 'Vice Chancellor coordination visit', 851820, 851996, 24.00, 156000.00, 'Highway travel completed in one day.', '2026-05-08 06:40:00', NULL),
(11, 2, 2, NULL, '2026-05-19', 'Mbale', 'Busia', 'Regional outreach transport', 851996, 852140, 19.50, 126750.00, 'Vehicle returned same evening.', '2026-05-19 07:25:00', NULL),
(12, 2, 1, NULL, '2026-06-01', 'Busia', 'Kampala', 'Delegation transfer support', 852140, 852436, 38.00, 247000.00, 'Shared vehicle use across drivers.', '2026-06-01 06:15:00', NULL),
(13, 3, 1, NULL, '2026-05-14', 'Busitema Main Campus', 'Pallisa', 'Stores pickup for estates unit', 64810, 64942, 16.00, 99200.00, 'Pickup truck used for materials collection.', '2026-05-14 10:15:00', NULL),
(14, 3, 2, NULL, '2026-05-29', 'Pallisa', 'Iganga', 'Workshop transfer of tools', 64942, 65110, 22.00, 136400.00, 'Another driver used the same pickup.', '2026-05-29 08:35:00', NULL),
(15, 3, 1, NULL, '2026-06-03', 'Iganga', 'Tororo', 'Maintenance follow-up and return', 65110, 65231, 14.80, 91760.00, 'Latest mileage reading for UBR 402Q.', '2026-06-03 09:00:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_communications_sender_user_id` (`sender_user_id`);

--
-- Indexes for table `communication_recipients`
--
ALTER TABLE `communication_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comm_recipients_communication_id` (`communication_id`),
  ADD KEY `idx_comm_recipients_driver_id` (`driver_id`),
  ADD KEY `idx_comm_recipients_user_id` (`user_id`);

--
-- Indexes for table `contractors`
--
ALTER TABLE `contractors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contractors_name` (`name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_departments_name` (`name`),
  ADD UNIQUE KEY `uq_departments_code` (`code`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_drivers_license_number` (`license_number`),
  ADD UNIQUE KEY `uq_drivers_employee_id` (`employee_id`),
  ADD KEY `idx_drivers_user_id` (`user_id`),
  ADD KEY `idx_drivers_department_id` (`department_id`);

--
-- Indexes for table `estate_projects`
--
ALTER TABLE `estate_projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_estate_projects_project_code` (`project_code`),
  ADD KEY `idx_estate_projects_contractor_id` (`contractor_id`),
  ADD KEY `idx_estate_projects_status` (`status`),
  ADD KEY `idx_estate_projects_category` (`category`),
  ADD KEY `fk_estate_projects_created_by` (`created_by`);

--
-- Indexes for table `estate_project_updates`
--
ALTER TABLE `estate_project_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estate_project_updates_project_id` (`estate_project_id`),
  ADD KEY `idx_estate_project_updates_update_date` (`update_date`),
  ADD KEY `fk_estate_project_updates_created_by` (`created_by`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspections_vehicle_id` (`vehicle_id`),
  ADD KEY `idx_inspections_driver_id` (`driver_id`),
  ADD KEY `idx_inspections_type_date` (`inspection_type`,`inspection_date`),
  ADD KEY `idx_inspections_service_provider_id` (`service_provider_id`),
  ADD KEY `fk_inspections_inspector_user` (`inspector_user_id`);

--
-- Indexes for table `inspection_items`
--
ALTER TABLE `inspection_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_items_inspection_id` (`inspection_id`);

--
-- Indexes for table `maintenance_parts`
--
ALTER TABLE `maintenance_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance_parts_record_id` (`maintenance_record_id`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance_vehicle_id` (`vehicle_id`),
  ADD KEY `idx_maintenance_service_provider_id` (`service_provider_id`),
  ADD KEY `idx_maintenance_date_reported` (`date_reported`),
  ADD KEY `idx_maintenance_status` (`status`),
  ADD KEY `fk_maintenance_reported_by` (`reported_by`),
  ADD KEY `fk_maintenance_approved_by` (`approved_by`);

--
-- Indexes for table `post_inspection_system_checks`
--
ALTER TABLE `post_inspection_system_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_system_checks_inspection_id` (`inspection_id`);

--
-- Indexes for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_service_providers_name_town` (`name`,`town`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_department_id` (`department_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vehicles_registration_no` (`registration_no`),
  ADD KEY `idx_vehicles_department_id` (`department_id`),
  ADD KEY `idx_vehicles_status` (`status`);

--
-- Indexes for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicle_assignments_vehicle_id` (`vehicle_id`),
  ADD KEY `idx_vehicle_assignments_driver_id` (`driver_id`),
  ADD KEY `idx_vehicle_assignments_assigned_by` (`assigned_by`);

--
-- Indexes for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicle_logs_vehicle_id` (`vehicle_id`),
  ADD KEY `idx_vehicle_logs_driver_id` (`driver_id`),
  ADD KEY `idx_vehicle_logs_trip_date` (`trip_date`),
  ADD KEY `fk_vehicle_logs_created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `communication_recipients`
--
ALTER TABLE `communication_recipients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `estate_projects`
--
ALTER TABLE `estate_projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estate_project_updates`
--
ALTER TABLE `estate_project_updates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_items`
--
ALTER TABLE `inspection_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_parts`
--
ALTER TABLE `maintenance_parts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `post_inspection_system_checks`
--
ALTER TABLE `post_inspection_system_checks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_providers`
--
ALTER TABLE `service_providers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `communications`
--
ALTER TABLE `communications`
  ADD CONSTRAINT `fk_communications_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `communication_recipients`
--
ALTER TABLE `communication_recipients`
  ADD CONSTRAINT `fk_comm_recipients_communication` FOREIGN KEY (`communication_id`) REFERENCES `communications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comm_recipients_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comm_recipients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `fk_drivers_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_drivers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `estate_projects`
--
ALTER TABLE `estate_projects`
  ADD CONSTRAINT `fk_estate_projects_contractor` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estate_projects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `estate_project_updates`
--
ALTER TABLE `estate_project_updates`
  ADD CONSTRAINT `fk_estate_project_updates_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estate_project_updates_project` FOREIGN KEY (`estate_project_id`) REFERENCES `estate_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inspections`
--
ALTER TABLE `inspections`
  ADD CONSTRAINT `fk_inspections_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inspections_inspector_user` FOREIGN KEY (`inspector_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inspections_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inspections_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inspection_items`
--
ALTER TABLE `inspection_items`
  ADD CONSTRAINT `fk_inspection_items_inspection` FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `maintenance_parts`
--
ALTER TABLE `maintenance_parts`
  ADD CONSTRAINT `fk_maintenance_parts_record` FOREIGN KEY (`maintenance_record_id`) REFERENCES `maintenance_records` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD CONSTRAINT `fk_maintenance_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_service_provider` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `post_inspection_system_checks`
--
ALTER TABLE `post_inspection_system_checks`
  ADD CONSTRAINT `fk_post_system_checks_inspection` FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  ADD CONSTRAINT `fk_vehicle_assignments_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle_assignments_user` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle_assignments_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  ADD CONSTRAINT `fk_vehicle_logs_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle_logs_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle_logs_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
