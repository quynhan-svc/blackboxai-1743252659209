<?php
// Load configuration with absolute path
require_once __DIR__.'/../includes/config.php';

if (!defined('DB_FILE') || !defined('DB_SCHEMA')) {
    die('Database configuration constants are not defined. Please check config.php');
}

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