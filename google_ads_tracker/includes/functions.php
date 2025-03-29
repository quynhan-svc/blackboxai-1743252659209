<?php
require_once 'config.php';

// Database initialization
function initDatabase() {
    if (!file_exists(DB_FILE)) {
        $db = new SQLite3(DB_FILE);
        $schema = file_get_contents(DB_SCHEMA);
        if (!$db->exec($schema)) {
            throw new Exception("Failed to initialize database");
        }
    }
    $db = new SQLite3(DB_FILE);
    $db->busyTimeout(5000);
    return $db;
}

// Installation check
function checkInstallation() {
    if (!defined('INSTALLED') || !INSTALLED) {
        if (basename($_SERVER['PHP_SELF']) != 'install.php') {
            header('Location: install/install.php');
            exit;
        }
        return false;
    }
    return true;
}

// Authentication functions
function checkAuth() {
    session_start();
    checkInstallation();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Improved user creation with password hashing
function createUser($username, $email, $password, $role = 'report_viewer') {
    $db = initDatabase();
    $stmt = $db->prepare("INSERT INTO users (username, email, password_md5, role, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
    $stmt->bindValue(1, sanitizeInput($username), SQLITE3_TEXT);
    $stmt->bindValue(2, sanitizeInput($email), SQLITE3_TEXT);
    $stmt->bindValue(3, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
    $stmt->bindValue(4, $role, SQLITE3_TEXT);
    return $stmt->execute();
}

// Input sanitization
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// IP handling
function getClientIP() {
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'];
}

// GeoIP functions
function getVietnamRegion($ip) {
    // This should be replaced with proper GeoIP integration
    $ip_last = explode('.', $ip)[3] ?? 0;
    if ($ip_last < 85) return 'Miền Bắc';
    if ($ip_last < 170) return 'Miền Trung';
    return 'Miền Nam';
}