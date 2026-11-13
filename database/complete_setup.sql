-- ============================================================
-- CaseCraft - CID Investigation Case Docket System
-- PRODUCTION DATABASE SETUP (Clean Slate - No Demo Cases)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `casecraft_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `casecraft_db`;

-- Drop existing tables in reverse dependency order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `victims`;
DROP TABLE IF EXISTS `suspects`;
DROP TABLE IF EXISTS `case_activity_logs`;
DROP TABLE IF EXISTS `investigation_notes`;
DROP TABLE IF EXISTS `witness_statements`;
DROP TABLE IF EXISTS `evidence`;
DROP TABLE IF EXISTS `case_assignments`;
DROP TABLE IF EXISTS `cases`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE (Officers & Investigators)
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `badge_id` VARCHAR(50) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `has_set_passcode` TINYINT(1) NOT NULL DEFAULT 0,
    `role` ENUM('admin', 'investigator', 'reviewer') NOT NULL DEFAULT 'investigator',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CASES TABLE (Investigation Dockets)
CREATE TABLE `cases` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_number` VARCHAR(50) NOT NULL UNIQUE,
    `fir_number` VARCHAR(100) NULL,
    `incident_location` VARCHAR(255) NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
    `status` ENUM('Open', 'Under Investigation', 'Pending Review', 'Closed', 'Archived') NOT NULL DEFAULT 'Open',
    `opened_at` DATE NOT NULL,
    `closed_at` DATE NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cases_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_cases_case_number` (`case_number`),
    INDEX `idx_cases_status` (`status`),
    INDEX `idx_cases_category` (`category`),
    INDEX `idx_cases_priority` (`priority`),
    INDEX `idx_cases_opened_at` (`opened_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CASE ASSIGNMENTS TABLE
CREATE TABLE `case_assignments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `assigned_by` INT UNSIGNED NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_assignments_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_assignments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY `uk_case_user_assignment` (`case_id`, `user_id`),
    INDEX `idx_assignments_case_id` (`case_id`),
    INDEX `idx_assignments_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. EVIDENCE & FORENSICS TABLE
CREATE TABLE `evidence` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `evidence_type` ENUM('Weapon / Firearm', 'Autopsy / Forensic', 'Digital / Cyber', 'Physical Sample', 'Documentary', 'Photographic / CCTV') NOT NULL DEFAULT 'Physical Sample',
    `description` TEXT NOT NULL,
    `collected_at` DATE NOT NULL,
    `seized_location` VARCHAR(255) NULL,
    `chain_of_custody` TEXT NULL,
    `file_path` VARCHAR(255) NULL,
    `original_file_name` VARCHAR(255) NULL,
    `file_size` INT UNSIGNED NULL,
    `mime_type` VARCHAR(100) NULL,
    `uploaded_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_evidence_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_evidence_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_evidence_case_id` (`case_id`),
    INDEX `idx_evidence_type` (`evidence_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. WITNESS STATEMENTS TABLE
CREATE TABLE `witness_statements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `witness_reference` VARCHAR(150) NOT NULL,
    `statement_text` TEXT NOT NULL,
    `recorded_at` DATE NOT NULL,
    `recorded_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_statements_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_statements_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_statements_case_id` (`case_id`),
    INDEX `idx_statements_witness_ref` (`witness_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. INVESTIGATION NOTES TABLE (Case Diary)
CREATE TABLE `investigation_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `note_text` TEXT NOT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notes_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_notes_case_id` (`case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CASE ACTIVITY LOGS TABLE (Audit Trail)
CREATE TABLE `case_activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `actor_user_id` INT UNSIGNED NOT NULL,
    `action_type` VARCHAR(64) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_activity_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_activity_actor_id` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_activity_case_id` (`case_id`),
    INDEX `idx_activity_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. SUSPECTS & ACCUSED TABLE (CID Profiling)
CREATE TABLE IF NOT EXISTS `suspects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `alias_name` VARCHAR(100) NULL,
    `nid_passport` VARCHAR(50) NULL,
    `age` INT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `suspect_status` ENUM('At Large', 'Under Surveillance', 'Detained', 'In Custody', 'Charges Filed', 'Acquitted') NOT NULL DEFAULT 'At Large',
    `physical_description` TEXT NULL,
    `identifying_marks` TEXT NULL,
    `posture_mannerisms` TEXT NULL,
    `last_known_location` TEXT NULL,
    `photo_path` VARCHAR(255) NULL,
    `prior_records` TEXT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_suspects_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_suspects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_suspects_case` (`case_id`),
    INDEX `idx_suspects_status` (`suspect_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. VICTIMS TABLE
CREATE TABLE IF NOT EXISTS `victims` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT UNSIGNED NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `nid_passport` VARCHAR(50) NULL,
    `age` INT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `contact_number` VARCHAR(50) NULL,
    `address` TEXT NULL,
    `victim_condition` ENUM('Deceased', 'Hospitalized', 'Safe / Protected', 'Uninjured', 'Missing') NOT NULL DEFAULT 'Safe / Protected',
    `injuries_loss_description` TEXT NULL,
    `protection_status` VARCHAR(150) NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_victims_case_id` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_victims_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_victims_case` (`case_id`),
    INDEX `idx_victims_condition` (`victim_condition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INITIAL OFFICER SEED (Zero Dummy Cases - Pure Clean Slate)
-- Default initial admin officer setup (Password/Passcode: officer can set on first login)
-- ============================================================

INSERT INTO `users` (`id`, `full_name`, `email`, `badge_id`, `password_hash`, `has_set_passcode`, `role`, `is_active`, `created_at`) VALUES
(1, 'Lead Investigating Officer', 'officer@cid.gov.bd', 'CID-HQ-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'admin', 1, NOW()),
(2, 'Duty Investigator', 'investigator@cid.gov.bd', 'CID-INV-02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'investigator', 1, NOW());

-- NO CASES INSERTED: The system starts completely clean with 0 cases.
