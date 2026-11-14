-- ============================================================
-- CaseCraft - Seed Data (No Dummy Cases - Clean Slate)
-- ============================================================

USE `casecraft_db`;

-- 1. OFFICIAL USERS
INSERT INTO `users` (`id`, `full_name`, `email`, `badge_id`, `password_hash`, `has_set_passcode`, `role`, `is_active`, `created_at`) VALUES
(1, 'Superintendent Marcus Vance', 'officer@cid.gov.bd', 'CID-HQ-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'admin', 1, NOW()),
(2, 'Inspector Elena Rostova', 'investigator@cid.gov.bd', 'CID-INV-02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'investigator', 1, NOW());

-- NO CASES: The system starts clean with 0 cases.
