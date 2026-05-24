-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 23, 2026 at 08:09 PM
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
-- Database: `fleet_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_by` varchar(120) DEFAULT NULL,
  `recipient_type` enum('drivers','officers','both') DEFAULT 'officers',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communication_history`
--

CREATE TABLE `communication_history` (
  `id` int(11) NOT NULL,
  `communication_id` int(11) DEFAULT NULL,
  `recipient_name` varchar(120) DEFAULT NULL,
  `recipient_email` varchar(150) NOT NULL,
  `recipient_type` enum('driver','officer') DEFAULT 'officer',
  `delivery_status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estates_projects`
--

CREATE TABLE `estates_projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(180) NOT NULL,
  `project_code` varchar(50) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `contractor` varchar(150) DEFAULT NULL,
  `engineer` varchar(120) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('planned','in_progress','completed','on_hold','overdue') DEFAULT 'planned',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `budget` decimal(15,2) DEFAULT 0.00,
  `spent` decimal(15,2) DEFAULT 0.00,
  `progress` int(11) DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_provider_id` int(11) DEFAULT NULL,
  `maintenance_date` date NOT NULL,
  `type` enum('service','repair') DEFAULT 'service',
  `description` text DEFAULT NULL,
  `cost` decimal(12,2) DEFAULT 0.00,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_inspections`
--

CREATE TABLE `post_inspections` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `inspector_name` varchar(120) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `post_invoice_no` varchar(100) DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `overall_status` varchar(50) DEFAULT NULL,
  `repair_cost` decimal(12,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_inspections`
--

CREATE TABLE `pre_inspections` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `inspector_name` varchar(120) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `overall_status` varchar(50) DEFAULT NULL,
  `defects` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

CREATE TABLE `service_providers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `town` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','transport_officer','driver','maintenance_officer','estates_officer') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `make` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `mileage` int(11) DEFAULT 0,
  `insurance_expiry` date DEFAULT NULL,
  `status` enum('active','maintenance','grounded') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_logs`
--

CREATE TABLE `vehicle_logs` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `trip_date` date NOT NULL,
  `from_location` varchar(150) DEFAULT NULL,
  `to_location` varchar(150) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `odometer_start` int(11) DEFAULT NULL,
  `odometer_end` int(11) DEFAULT NULL,
  `distance_km` int(11) DEFAULT NULL,
  `fuel_liters` decimal(10,2) DEFAULT NULL,
  `fuel_cost` decimal(12,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `communication_history`
--
ALTER TABLE `communication_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `communication_id` (`communication_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estates_projects`
--
ALTER TABLE `estates_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `service_provider_id` (`service_provider_id`);

--
-- Indexes for table `post_inspections`
--
ALTER TABLE `post_inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `pre_inspections`
--
ALTER TABLE `pre_inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`);

--
-- Indexes for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `communication_history`
--
ALTER TABLE `communication_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estates_projects`
--
ALTER TABLE `estates_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_inspections`
--
ALTER TABLE `post_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pre_inspections`
--
ALTER TABLE `pre_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_providers`
--
ALTER TABLE `service_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `communication_history`
--
ALTER TABLE `communication_history`
  ADD CONSTRAINT `communication_history_ibfk_1` FOREIGN KEY (`communication_id`) REFERENCES `communications` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD CONSTRAINT `maintenance_records_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_records_ibfk_2` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `post_inspections`
--
ALTER TABLE `post_inspections`
  ADD CONSTRAINT `post_inspections_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pre_inspections`
--
ALTER TABLE `pre_inspections`
  ADD CONSTRAINT `pre_inspections_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  ADD CONSTRAINT `vehicle_logs_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_logs_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
