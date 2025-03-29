<?php
require_once 'config.php';

// Basic security functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// User authentication functions
function createUser($username, $email, $password, $role = 'report_viewer') {
    global $db;
    $stmt = $db->prepare("INSERT INTO users (username, email, password_md5, role) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, sanitizeInput($username), SQLITE3_TEXT);
    $stmt->bindValue(2, sanitizeInput($email), SQLITE3_TEXT);
    $stmt->bindValue(3, md5($password), SQLITE3_TEXT);
    $stmt->bindValue(4, $role, SQLITE3_TEXT);
    return $stmt->execute();
}

// Report permission functions
function setReportPermissions($user_id, $daily = true, $three_day = true, $export = true) {
    global $db;
    $stmt = $db->prepare("INSERT OR REPLACE INTO report_permissions VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $daily ? 1 : 0, SQLITE3_INTEGER);
    $stmt->bindValue(3, $three_day ? 1 : 0, SQLITE3_INTEGER);
    $stmt->bindValue(4, $export ? 1 : 0, SQLITE3_INTEGER);
    return $stmt->execute();
}

// Get real client IP (works with Cloudflare)
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

// IP to region mapping for Vietnam
function getVietnamRegion($ip) {
    // This is a simplified version - in production you would use GeoIP database
    $ip_last = explode('.', $ip)[3] ?? 0;
    if ($ip_last < 85) return 'Miền Bắc';
    if ($ip_last < 170) return 'Miền Trung';
    return 'Miền Nam';
}