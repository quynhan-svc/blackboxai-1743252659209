<?php
// Define base directory
define('BASE_DIR', dirname(__DIR__));

// Check if installation is required
if (!file_exists(BASE_DIR.'/database/ads_tracker.db') && 
    basename($_SERVER['PHP_SELF']) != 'install.php') {
    header('Location: install/install.php');
    exit;
}

try {
    // Database Configuration
    define('DB_DIR', BASE_DIR.'/database');
    define('DB_FILE', DB_DIR.'/ads_tracker.db');
    define('DB_SCHEMA', BASE_DIR.'/database/schema.sql');
    define('INSTALLED', file_exists(DB_DIR.'/installed.lock'));

    // Security Settings
    define('SESSION_TIMEOUT', 1800);
    define('LOGIN_ATTEMPTS', 5);

    // Initialize Database
    function initDatabase() {
        if (!file_exists(DB_FILE)) {
            throw new Exception('Database not found. Please run the installer.');
        }
        
        $db = new SQLite3(DB_FILE);
        if (!$db) {
            throw new Exception('Failed to connect to database');
        }
        $db->busyTimeout(5000);
        return $db;
    }

    $db = initDatabase();
} catch (Exception $e) {
    if (basename($_SERVER['PHP_SELF']) != 'install.php') {
        header('Location: install/install.php?error='.urlencode($e->getMessage()));
        exit;
    }
    // Allow installer to run even with errors
}
?>
