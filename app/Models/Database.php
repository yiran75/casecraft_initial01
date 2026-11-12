<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Database Connection Manager (PDO Singleton)
 * 
 * Provides robust PDO connection handling for MySQL and SQLite.
 * Uses prepared statements and exception handling everywhere.
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Get or initialize the PDO instance
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Establish the connection based on configuration
     */
    private static function connect(): void
    {
        if (empty(self::$config)) {
            self::$config = require dirname(__DIR__, 2) . '/config/database.php';
        }

        $driver = self::$config['default'] ?? 'mysql';
        $dbConfig = self::$config['connections'][$driver] ?? null;

        if (!$dbConfig) {
            throw new PDOException("Database driver '{$driver}' is not configured.");
        }

        try {
            if ($driver === 'sqlite') {
                $dbPath = $dbConfig['database'];
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $dsn = "sqlite:{$dbPath}";
                self::$instance = new PDO($dsn, null, null, $dbConfig['options'] ?? []);
                // Enable foreign keys in SQLite
                self::$instance->exec('PRAGMA foreign_keys = ON;');
            } else {
                // MySQL Connection
                $host = $dbConfig['host'] ?? '127.0.0.1';
                $port = $dbConfig['port'] ?? 3306;
                $dbname = $dbConfig['database'] ?? 'casecraft_db';
                $charset = $dbConfig['charset'] ?? 'utf8mb4';

                // First attempt to connect to specified database
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                try {
                    self::$instance = new PDO(
                        $dsn,
                        $dbConfig['username'] ?? 'root',
                        $dbConfig['password'] ?? '',
                        $dbConfig['options'] ?? []
                    );
                } catch (PDOException $e) {
                    // If database does not exist, connect to server and create it
                    if (str_contains($e->getMessage(), 'Unknown database') || $e->getCode() == 1049) {
                        $serverDsn = "mysql:host={$host};port={$port};charset={$charset}";
                        $tempPdo = new PDO(
                            $serverDsn,
                            $dbConfig['username'] ?? 'root',
                            $dbConfig['password'] ?? '',
                            $dbConfig['options'] ?? []
                        );
                        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET {$charset} COLLATE {$dbConfig['collation']};");
                        // Now connect to the new DB
                        self::$instance = new PDO(
                            $dsn,
                            $dbConfig['username'] ?? 'root',
                            $dbConfig['password'] ?? '',
                            $dbConfig['options'] ?? []
                        );
                    } else {
                        throw $e;
                    }
                }
            }

            // If users table does not exist, automatically bootstrap complete schema
            try {
                $check = self::$instance->query("SHOW TABLES LIKE 'users'")->fetch();
                if (!$check) {
                    $setupFile = dirname(__DIR__, 2) . '/database/complete_setup.sql';
                    if (file_exists($setupFile)) {
                        $sql = file_get_contents($setupFile);
                        self::$instance->exec($sql);
                    }
                }
            } catch (\Throwable $ex) {
                error_log("Auto-setup check: " . $ex->getMessage());
            }

            // Ensure CID Tables (Suspects & Victims) are auto-migrated
            self::ensureCidTables();
            // Ensure upgraded columns (FIR, Location, Badge, Passcode Flag)
            self::ensureSchemaColumns();
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new PDOException("Could not connect to the database. Please check configuration. " . $e->getMessage());
        }
    }

    /**
     * Auto-migrate CID Suspects & Victims tables
     */
    private static function ensureCidTables(): void
    {
        try {
            $pdo = self::$instance;
            if (!$pdo) return;

            $pdo->exec("
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
                    INDEX `idx_suspects_case` (`case_id`),
                    INDEX `idx_suspects_status` (`suspect_status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
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
                    INDEX `idx_victims_case` (`case_id`),
                    INDEX `idx_victims_condition` (`victim_condition`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            error_log('CID Tables auto-migration notice: ' . $e->getMessage());
        }
    }

    /**
     * Set explicit PDO instance (useful for testing)
     */
    public static function setConnection(?PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /**
     * Helper to run an automated migration & seed from SQL files
     */
    public static function runMigrationAndSeed(): array
    {
        $pdo = self::getConnection();
        $schemaPath = dirname(__DIR__, 2) . '/database/schema.sql';
        $seedPath = dirname(__DIR__, 2) . '/database/seed.sql';

        $results = ['schema' => false, 'seed' => false, 'messages' => []];

        if (file_exists($schemaPath)) {
            $schemaSql = file_get_contents($schemaPath);
            $pdo->exec($schemaSql);
            $results['schema'] = true;
            $results['messages'][] = 'Schema executed successfully.';
        }

        if (file_exists($seedPath)) {
            $seedSql = file_get_contents($seedPath);
            $pdo->exec($seedSql);
            $results['seed'] = true;
            $results['messages'][] = 'Seed data inserted successfully.';
        }

        return $results;
    }

    /**
     * Ensure upgraded columns exist across tables
     */
    private static function ensureSchemaColumns(): void
    {
        try {
            $pdo = self::$instance;
            if (!$pdo) return;

            // Check cases table
            $caseCheck = $pdo->query("SHOW TABLES LIKE 'cases'")->fetch();
            if ($caseCheck) {
                $caseCols = $pdo->query("SHOW COLUMNS FROM `cases`")->fetchAll(\PDO::FETCH_COLUMN);
                if (!in_array('fir_number', $caseCols)) {
                    $pdo->exec("ALTER TABLE `cases` ADD COLUMN `fir_number` VARCHAR(100) NULL AFTER `case_number`");
                }
                if (!in_array('incident_location', $caseCols)) {
                    $pdo->exec("ALTER TABLE `cases` ADD COLUMN `incident_location` VARCHAR(255) NULL AFTER `fir_number`");
                }
            }

            // Check users table
            $userCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            if ($userCheck) {
                $userCols = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(\PDO::FETCH_COLUMN);
                if (!in_array('badge_id', $userCols)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `badge_id` VARCHAR(50) NULL AFTER `email`");
                }
                if (!in_array('has_set_passcode', $userCols)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `has_set_passcode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`");
                }
            }
        } catch (\Throwable $e) {
            error_log("Schema columns ensure notice: " . $e->getMessage());
        }
    }
}
