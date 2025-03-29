<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

// Create database directory if not exists
if (!file_exists(DB_DIR)) {
    if (!mkdir(DB_DIR, 0755, true)) {
        die("Failed to create database directory");
    }
}

// Check if already installed
if (file_exists(DB_FILE)) {
    header('Location: ../admin/login.php');
    exit;
}

// Handle installation form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create database
        $db = new SQLite3(DB_FILE);
        if (!$db) {
            throw new Exception("Failed to create database");
        }

        // Create tables
        $schema = file_get_contents(DB_SCHEMA);
        if ($db->exec($schema) === false) {
            throw new Exception("Failed to create database tables");
        }

        // Create admin user
        $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_md5, role, created_at) 
                            VALUES (?, ?, ?, 'admin', datetime('now'))");
        $stmt->bindValue(1, $_POST['admin_username'], SQLITE3_TEXT);
        $stmt->bindValue(2, $_POST['admin_email'], SQLITE3_TEXT);
        $stmt->bindValue(3, $admin_password, SQLITE3_TEXT);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create admin user");
        }

        // Add sample click data
        $sampleClick = [
            'ip' => '127.0.0.1',
            'country_code' => 'VN',
            'region_name' => 'Ho Chi Minh',
            'city' => 'HCMC',
            'isp' => 'Viettel',
            'asn' => 'AS7552',
            'gad_url' => 'http://example.com',
            'useragent' => 'Mozilla/5.0 (Sample)',
            'referrer_url' => 'http://google.com'
        ];
        
        $stmt = $db->prepare("INSERT INTO clicks (ip, country_code, region_name, city, isp, asn, gad_url, useragent, referrer_url, created_at) 
                            VALUES (:ip, :country_code, :region_name, :city, :isp, :asn, :gad_url, :useragent, :referrer_url, datetime('now'))");
        
        foreach ($sampleClick as $key => $value) {
            $stmt->bindValue(':'.$key, $value, SQLITE3_TEXT);
        }
        $stmt->execute();

        // Create installed flag
        file_put_contents(DB_DIR.'/installed.lock', '1');
        
        header('Location: ../admin/login.php?first_install=1');
        exit;
        
    } catch (Exception $e) {
        // Clean up on failure
        if (isset($db) && file_exists(DB_FILE)) {
            unlink(DB_FILE);
        }
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="cpanel-login">
        <div class="login-brand">
            <h1>Google Ads Tracker</h1>
            <h2>Initial Setup</h2>
        </div>
        
        <div class="login-box">
            <div class="login-header">
                <h2><i class="fas fa-cog"></i> System Installation</h2>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label>Admin Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="admin_username" value="admin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Admin Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="admin_email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Admin Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="admin_password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-play"></i> Begin Installation
                </button>
            </form>
        </div>
    </div>
</body>
</html>