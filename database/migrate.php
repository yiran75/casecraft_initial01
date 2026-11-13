<?php
/**
 * CaseCraft - Database Migration & Seeder Script
 * Run from CLI: php database/migrate.php
 */

require_once __DIR__ . '/../app/Models/Database.php';

use App\Models\Database;

echo "========================================\n";
echo " CaseCraft Database Setup & Migration \n";
echo "========================================\n\n";

try {
    echo "Connecting to MySQL server...\n";
    $pdo = Database::getConnection();
    echo "Database connected successfully!\n\n";

    echo "Running complete_setup.sql...\n";
    $setupFile = __DIR__ . '/complete_setup.sql';
    if (!file_exists($setupFile)) {
        die("Error: complete_setup.sql not found at {$setupFile}\n");
    }

    $setupSql = file_get_contents($setupFile);
    $pdo->exec($setupSql);
    echo "Complete database schema and initial records set up successfully!\n\n";

    echo "----------------------------------------\n";
    echo "Default Demo Accounts (Password: 'password'):\n";
    echo " - Admin:         admin@casecraft.local\n";
    echo " - Investigator:  investigator@casecraft.local\n";
    echo " - Reviewer:      reviewer@casecraft.local\n";
    echo "----------------------------------------\n";
    echo "Setup Complete!\n";

} catch (Exception $e) {
    echo "\n[ERROR] Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
