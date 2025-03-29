<?php
// Define base directory
define('BASE_DIR', dirname(__DIR__));

// Database Configuration
define('DB_DIR', BASE_DIR.'/database');
define('DB_FILE', DB_DIR.'/ads_tracker.db');
define('DB_SCHEMA', BASE_DIR.'/database/schema.sql');
define('INSTALLED', file_exists(DB_DIR.'/installed.lock'));

// Security Settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('LOGIN_ATTEMPTS', 5);

// Initialize Database
function initDatabase() {
    if (!file_exists(DB_FILE)) {
        $db = new SQLite3(DB_FILE);
        $schema = file_get_contents(DB_SCHEMA);
        if (!$db->exec($schema)) {
            die('Failed to initialize database tables: ' . $db->lastErrorMsg());
        }
    }
    $db = new SQLite3(DB_FILE);
    $db->busyTimeout(5000);
    return $db;
}

$db = initDatabase();
?>