<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__.'/../includes/config.php';

try {
    // Initialize database connection
    $db = new SQLite3(DB_FILE);
    $db->enableExceptions(true);

    // Create tables in proper sequence
    $tables = [
        'users' => "CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_md5 VARCHAR(32) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'report_viewer',
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        
        'clicks' => "CREATE TABLE clicks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip VARCHAR(45) NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            region VARCHAR(100),
            useragent TEXT,
            gad_url TEXT,
            referrer_url TEXT,
            is_duplicate BOOLEAN DEFAULT 0,
            is_suspicious BOOLEAN DEFAULT 0,
            country_code VARCHAR(2),
            region_name VARCHAR(100),
            city VARCHAR(100),
            isp VARCHAR(100),
            asn VARCHAR(50)
        )",
        
        'settings' => "CREATE TABLE settings (
            mail_host VARCHAR(100),
            mail_port INT DEFAULT 587,
            mail_encryption VARCHAR(10) DEFAULT 'tls',
            mail_username VARCHAR(100),
            mail_password VARCHAR(100),
            site_title VARCHAR(100),
            admin_email VARCHAR(100),
            alert_threshold INT DEFAULT 5
        )",
        
        'report_permissions' => "CREATE TABLE report_permissions (
            user_id INTEGER PRIMARY KEY,
            can_view_daily BOOLEAN DEFAULT 1,
            can_view_3day BOOLEAN DEFAULT 1,
            can_export BOOLEAN DEFAULT 1,
            FOREIGN KEY(user_id) REFERENCES users(id)
        )"
    ];

    // Create tables if they don't exist
    foreach ($tables as $table => $sql) {
        if (!$db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")) {
            if (!$db->exec($sql)) {
                throw new Exception("Failed to create $table table: " . $db->lastErrorMsg());
            }
            echo "Created $table table successfully\n";
        }
    }

    // Create admin user if doesn't exist
    $adminExists = $db->querySingle("SELECT 1 FROM users WHERE username='admin'");
    if (!$adminExists) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_md5, role) VALUES (?, ?, ?, 'admin')");
        $stmt->bindValue(1, 'admin', SQLITE3_TEXT);
        $stmt->bindValue(2, 'admin@example.com', SQLITE3_TEXT);
        $stmt->bindValue(3, $password, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            throw new Exception("Failed to create admin user: " . $db->lastErrorMsg());
        }
        echo "Created admin user (admin/admin123)\n";
    }

    // Create installed flag
    file_put_contents(DB_DIR.'/installed.lock', '1');
    echo "Database preparation completed successfully\n";

} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}
?>
