<?php
// Database Configuration
define('DB_PATH', __DIR__.'/../database/ads_tracker.db');
define('DB_SCHEMA', __DIR__.'/../database/schema.sql');

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