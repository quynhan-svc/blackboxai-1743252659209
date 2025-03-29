<?php
// Database Configuration
define('DB_DIR', __DIR__.'/../database');
define('DB_FILE', DB_DIR.'/ads_tracker.db'); 
define('DB_SCHEMA', __DIR__.'/../database/schema.sql');
define('INSTALLED', file_exists(DB_DIR.'/installed.lock'));

// Ensure database directory exists
if (!file_exists(DB_DIR)) {
    if (!mkdir(DB_DIR, 0755, true)) {
        die('Failed to create database directory');
    }
}

// Initialize SQLite Database
function initDatabase() {
    if (!file_exists(DB_PATH)) {
        $db = new SQLite3(DB_PATH);
        $schema = file_get_contents(DB_SCHEMA);
        $db->exec($schema);
    }
    return new SQLite3(DB_PATH);
}

$db = initDatabase();

// Security Settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('LOGIN_ATTEMPTS', 5);
?>