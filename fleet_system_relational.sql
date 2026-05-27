-- Improved relational schema for the Fleet System backend.
-- MariaDB/MySQL compatible. Import this into a fresh database when possible.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS communication_recipients;
DROP TABLE IF EXISTS communications;
DROP TABLE IF EXISTS post_inspection_system_checks;
DROP TABLE IF EXISTS inspection_items;
DROP TABLE IF EXISTS inspections;
DROP TABLE IF EXISTS maintenance_parts;
DROP TABLE IF EXISTS maintenance_records;
DROP TABLE IF EXISTS vehicle_logs;
DROP TABLE IF EXISTS vehicle_assignments;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS service_providers;
DROP TABLE IF EXISTS estate_project_updates;
DROP TABLE IF EXISTS estate_projects;
DROP TABLE IF EXISTS contractors;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(40) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_departments_name (name),
  UNIQUE KEY uq_departments_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','transport_officer','driver','maintenance_officer','estates_officer') NOT NULL DEFAULT 'admin',
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_department_id (department_id),
  CONSTRAINT fk_users_department
    FOREIGN KEY (department_id) REFERENCES departments(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id INT UNSIGNED DEFAULT NULL,
  registration_no VARCHAR(50) NOT NULL,
  make VARCHAR(100) NOT NULL,
  model VARCHAR(100) NOT NULL,
  manufacture_year YEAR DEFAULT NULL,
  vehicle_type ENUM('sedan','suv','pickup','truck','van','bus','motorcycle','other') NOT NULL DEFAULT 'other',
  fuel_type ENUM('petrol','diesel','hybrid','electric','other') NOT NULL DEFAULT 'diesel',
  current_mileage INT UNSIGNED NOT NULL DEFAULT 0,
  insurance_expiry DATE DEFAULT NULL,
  status ENUM('active','maintenance','grounded','disposed') NOT NULL DEFAULT 'active',
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_vehicles_registration_no (registration_no),
  KEY idx_vehicles_department_id (department_id),
  KEY idx_vehicles_status (status),
  CONSTRAINT fk_vehicles_department
    FOREIGN KEY (department_id) REFERENCES departments(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE drivers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  department_id INT UNSIGNED DEFAULT NULL,
  full_name VARCHAR(120) NOT NULL,
  employee_id VARCHAR(60) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  license_number VARCHAR(100) NOT NULL,
  license_classes VARCHAR(80) DEFAULT NULL,
  license_expiry DATE DEFAULT NULL,
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_drivers_employee_id (employee_id),
  UNIQUE KEY uq_drivers_license_number (license_number),
  KEY idx_drivers_user_id (user_id),
  KEY idx_drivers_department_id (department_id),
  CONSTRAINT fk_drivers_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_drivers_department
    FOREIGN KEY (department_id) REFERENCES departments(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicle_assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  driver_id INT UNSIGNED NOT NULL,
  assigned_by INT UNSIGNED DEFAULT NULL,
  assigned_at DATE NOT NULL,
  released_at DATE DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_vehicle_assignments_vehicle_id (vehicle_id),
  KEY idx_vehicle_assignments_driver_id (driver_id),
  KEY idx_vehicle_assignments_assigned_by (assigned_by),
  CONSTRAINT fk_vehicle_assignments_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_vehicle_assignments_driver
    FOREIGN KEY (driver_id) REFERENCES drivers(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_vehicle_assignments_user
    FOREIGN KEY (assigned_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicle_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  driver_id INT UNSIGNED DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  trip_date DATE NOT NULL,
  departure_location VARCHAR(150) NOT NULL,
  destination VARCHAR(150) NOT NULL,
  purpose VARCHAR(255) NOT NULL,
  odometer_start INT UNSIGNED DEFAULT NULL,
  odometer_end INT UNSIGNED DEFAULT NULL,
  distance_km INT UNSIGNED GENERATED ALWAYS AS (
    CASE
      WHEN odometer_start IS NOT NULL AND odometer_end IS NOT NULL AND odometer_end >= odometer_start
      THEN odometer_end - odometer_start
      ELSE NULL
    END
  ) STORED,
  fuel_litres DECIMAL(10,2) DEFAULT NULL,
  fuel_cost DECIMAL(14,2) DEFAULT NULL,
  remarks TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_vehicle_logs_vehicle_id (vehicle_id),
  KEY idx_vehicle_logs_driver_id (driver_id),
  KEY idx_vehicle_logs_trip_date (trip_date),
  CONSTRAINT fk_vehicle_logs_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_vehicle_logs_driver
    FOREIGN KEY (driver_id) REFERENCES drivers(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_vehicle_logs_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_providers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  town VARCHAR(100) DEFAULT NULL,
  contact_person VARCHAR(120) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  specialty VARCHAR(150) DEFAULT NULL,
  status ENUM('active','pending','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_service_providers_name_town (name, town)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenance_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  service_provider_id INT UNSIGNED DEFAULT NULL,
  reported_by INT UNSIGNED DEFAULT NULL,
  approved_by INT UNSIGNED DEFAULT NULL,
  maintenance_type ENUM('repair','routine_service','inspection','brake_service','other') NOT NULL DEFAULT 'repair',
  date_reported DATE NOT NULL,
  date_completed DATE DEFAULT NULL,
  description TEXT NOT NULL,
  parts_replaced TEXT DEFAULT NULL,
  total_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  mileage_at_service INT UNSIGNED DEFAULT NULL,
  invoice_number VARCHAR(100) DEFAULT NULL,
  status ENUM('reported','in_progress','completed','cancelled') NOT NULL DEFAULT 'reported',
  remarks TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_maintenance_vehicle_id (vehicle_id),
  KEY idx_maintenance_service_provider_id (service_provider_id),
  KEY idx_maintenance_date_reported (date_reported),
  KEY idx_maintenance_status (status),
  CONSTRAINT fk_maintenance_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_maintenance_service_provider
    FOREIGN KEY (service_provider_id) REFERENCES service_providers(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_maintenance_reported_by
    FOREIGN KEY (reported_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_maintenance_approved_by
    FOREIGN KEY (approved_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenance_parts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  maintenance_record_id INT UNSIGNED NOT NULL,
  part_name VARCHAR(150) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  unit_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_maintenance_parts_record_id (maintenance_record_id),
  CONSTRAINT fk_maintenance_parts_record
    FOREIGN KEY (maintenance_record_id) REFERENCES maintenance_records(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inspections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  driver_id INT UNSIGNED DEFAULT NULL,
  inspector_user_id INT UNSIGNED DEFAULT NULL,
  service_provider_id INT UNSIGNED DEFAULT NULL,
  inspection_type ENUM('pre','post') NOT NULL,
  invoice_number VARCHAR(100) DEFAULT NULL,
  post_invoice_number VARCHAR(100) DEFAULT NULL,
  inspection_date DATE NOT NULL,
  inspector_name VARCHAR(120) NOT NULL,
  inspector_title VARCHAR(120) DEFAULT NULL,
  mileage INT UNSIGNED DEFAULT NULL,
  overall_status ENUM('good','fair','faulty','needs_repair','completed') DEFAULT NULL,
  defects TEXT DEFAULT NULL,
  works_done TEXT DEFAULT NULL,
  repair_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  memo_to VARCHAR(150) DEFAULT NULL,
  memo_thru_one VARCHAR(150) DEFAULT NULL,
  memo_thru_two VARCHAR(150) DEFAULT NULL,
  memo_from VARCHAR(150) DEFAULT NULL,
  vehicle_description VARCHAR(255) DEFAULT NULL,
  closing_note TEXT DEFAULT NULL,
  recommendation TEXT DEFAULT NULL,
  cc VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_inspections_vehicle_id (vehicle_id),
  KEY idx_inspections_driver_id (driver_id),
  KEY idx_inspections_type_date (inspection_type, inspection_date),
  KEY idx_inspections_service_provider_id (service_provider_id),
  CONSTRAINT fk_inspections_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_inspections_driver
    FOREIGN KEY (driver_id) REFERENCES drivers(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_inspections_inspector_user
    FOREIGN KEY (inspector_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_inspections_service_provider
    FOREIGN KEY (service_provider_id) REFERENCES service_providers(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inspection_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspection_id INT UNSIGNED NOT NULL,
  inspection_point VARCHAR(150) NOT NULL,
  findings TEXT DEFAULT NULL,
  action_point TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_inspection_items_inspection_id (inspection_id),
  CONSTRAINT fk_inspection_items_inspection
    FOREIGN KEY (inspection_id) REFERENCES inspections(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE post_inspection_system_checks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspection_id INT UNSIGNED NOT NULL,
  system_name VARCHAR(120) NOT NULL,
  condition_status ENUM('good','fair','faulty') NOT NULL,
  remarks TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_post_system_checks_inspection_id (inspection_id),
  CONSTRAINT fk_post_system_checks_inspection
    FOREIGN KEY (inspection_id) REFERENCES inspections(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE communications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_user_id INT UNSIGNED DEFAULT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  message_type ENUM('manual','inspection','maintenance','system') NOT NULL DEFAULT 'manual',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_communications_sender_user_id (sender_user_id),
  CONSTRAINT fk_communications_sender
    FOREIGN KEY (sender_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE communication_recipients (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  communication_id INT UNSIGNED NOT NULL,
  driver_id INT UNSIGNED DEFAULT NULL,
  user_id INT UNSIGNED DEFAULT NULL,
  recipient_name VARCHAR(120) DEFAULT NULL,
  recipient_email VARCHAR(150) NOT NULL,
  recipient_type ENUM('driver','officer','external') NOT NULL DEFAULT 'officer',
  delivery_status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  sent_at DATETIME DEFAULT NULL,
  failure_reason TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_comm_recipients_communication_id (communication_id),
  KEY idx_comm_recipients_driver_id (driver_id),
  KEY idx_comm_recipients_user_id (user_id),
  CONSTRAINT fk_comm_recipients_communication
    FOREIGN KEY (communication_id) REFERENCES communications(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comm_recipients_driver
    FOREIGN KEY (driver_id) REFERENCES drivers(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_comm_recipients_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contractors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  contact_person VARCHAR(120) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  town VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_contractors_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estate_projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contractor_id INT UNSIGNED DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  project_name VARCHAR(180) NOT NULL,
  project_code VARCHAR(50) NOT NULL,
  location VARCHAR(150) DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  funding_source VARCHAR(150) DEFAULT NULL,
  status ENUM('planned','approved','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'planned',
  priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  start_date DATE DEFAULT NULL,
  deadline DATE DEFAULT NULL,
  budget DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  spent DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_estate_projects_project_code (project_code),
  KEY idx_estate_projects_contractor_id (contractor_id),
  KEY idx_estate_projects_status (status),
  KEY idx_estate_projects_category (category),
  CONSTRAINT fk_estate_projects_contractor
    FOREIGN KEY (contractor_id) REFERENCES contractors(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_estate_projects_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_estate_projects_progress CHECK (progress_percent <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estate_project_updates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estate_project_id INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  update_date DATE NOT NULL,
  progress_percent TINYINT UNSIGNED DEFAULT NULL,
  amount_spent DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  note TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_estate_project_updates_project_id (estate_project_id),
  KEY idx_estate_project_updates_update_date (update_date),
  CONSTRAINT fk_estate_project_updates_project
    FOREIGN KEY (estate_project_id) REFERENCES estate_projects(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_estate_project_updates_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_estate_project_updates_progress CHECK (progress_percent IS NULL OR progress_percent <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO departments (name, code) VALUES
('Unassigned', 'UNASSIGNED'),
('Transport', 'TRANSPORT'),
('Estates', 'ESTATES'),
('University Secretary', 'US'),
('Vice Chancellor', 'VC'),
('DVD fa', 'DVDFA');

INSERT INTO service_providers (name, town, phone, email, specialty, status) VALUES
('Tororo Auto Garage', 'Tororo', '+256 701 220 110', 'service@tororoauto.ug', 'General repairs', 'active'),
('Toyota Uganda Service Centre', 'Kampala', '+256 414 339 000', 'fleetservice@toyota.co.ug', 'Toyota service', 'active'),
('Mbale Fleet Mechanics', 'Mbale', '+256 772 431 980', 'info@mbalefleet.ug', 'Brakes and suspension', 'pending');

INSERT INTO vehicles (department_id, registration_no, make, model, manufacture_year, vehicle_type, fuel_type, current_mileage, status) VALUES
((SELECT id FROM departments WHERE name = 'Unassigned'), 'UBD 456G', 'Toyota', 'Prado', 2022, 'suv', 'diesel', 0, 'maintenance'),
((SELECT id FROM departments WHERE name = 'DVD fa'), 'UBR 123C', 'TOYOTA', 'Land cruiser', 2024, 'suv', 'diesel', 852436, 'active'),
((SELECT id FROM departments WHERE name = 'University Secretary'), 'UBR 402Q', 'TOYOTA', 'HILLUX PICKUP', 2024, 'pickup', 'diesel', 65231, 'active'),
((SELECT id FROM departments WHERE name = 'Estates'), 'UAJ 433X', 'Ford', 'Ford ranger', 2009, 'pickup', 'diesel', 196002, 'active'),
((SELECT id FROM departments WHERE name = 'Vice Chancellor'), 'UBP 401F', 'TOYOTA', 'LAND CRUISER', 2022, 'suv', 'diesel', 200808, 'maintenance');

INSERT INTO drivers (department_id, full_name, phone, email, license_number, license_classes, status) VALUES
((SELECT id FROM departments WHERE name = 'Transport'), 'Simali Habert', '+256 772 123 456', 'simalihabert@gmail.com', 'CM 78452', 'B', 'active'),
((SELECT id FROM departments WHERE name = 'Transport'), 'Moses Okello', '+256 701 450 220', 'moses.okello@busitema.ac.ug', 'CM 21984', 'B', 'active'),
((SELECT id FROM departments WHERE name = 'Transport'), 'Grace Namuli', '+256 758 802 114', 'grace.namuli@busitema.ac.ug', 'CM 66310', 'B', 'inactive');

INSERT INTO vehicle_assignments (vehicle_id, driver_id, assigned_at) VALUES
((SELECT id FROM vehicles WHERE registration_no = 'UAJ 433X'), (SELECT id FROM drivers WHERE license_number = 'CM 78452'), '2026-05-01'),
((SELECT id FROM vehicles WHERE registration_no = 'UBR 123C'), (SELECT id FROM drivers WHERE license_number = 'CM 21984'), '2026-05-01');

INSERT INTO vehicle_logs (vehicle_id, driver_id, trip_date, departure_location, destination, purpose, odometer_start, odometer_end, fuel_litres, fuel_cost, remarks) VALUES
((SELECT id FROM vehicles WHERE registration_no = 'UAJ 433X'), (SELECT id FROM drivers WHERE license_number = 'CM 78452'), '2026-05-17', 'Kampala', 'Mbale', 'Elgon', 196002, 196298, 22.00, 132000.00, 'Ok'),
((SELECT id FROM vehicles WHERE registration_no = 'UAJ 433X'), (SELECT id FROM drivers WHERE license_number = 'CM 78452'), '2026-05-17', 'Busitema', 'Kampala', 'National council', 196002, 196312, 20.00, 125600.00, 'Ok');

INSERT INTO maintenance_records (vehicle_id, maintenance_type, date_reported, date_completed, description, total_cost, status) VALUES
((SELECT id FROM vehicles WHERE registration_no = 'UAJ 433X'), 'repair', '2026-05-18', '2026-05-18', 'Engine overhaul, brakes, windscreen and related repairs', 4200000.00, 'completed');

