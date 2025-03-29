<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/../includes/init.php';

try {
    // Verify configuration
    if (!defined('DB_FILE') || !defined('DB_SCHEMA') || !defined('DB_DIR')) {
        throw new Exception("Database configuration not properly initialized");
    }

    // Create database connection
    $db = new SQLite3(DB_FILE);
    $db->enableExceptions(true);

    // Check if tables exist
    $tablesExist = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    
    if (!$tablesExist) {
        echo "Creating database tables...\n";
        
        // Read schema file
        $schema = file_get_contents(DB_SCHEMA);
        if ($schema === false) {
            throw new Exception("Failed to read schema file");
        }

        // Execute schema in transaction
        $db->exec('BEGIN TRANSACTION');
        try {
            if (!$db->exec($schema)) {
                throw new Exception("Schema execution failed: " . $db->lastErrorMsg());
            }
            
            // Create admin user
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password_md5, role) VALUES (?, ?, ?, ?)");
            $stmt->bindValue(1, 'admin', SQLITE3_TEXT);
            $stmt->bindValue(2, 'admin@example.com', SQLITE3_TEXT);
            $stmt->bindValue(3, $password, SQLITE3_TEXT);
            $stmt->bindValue(4, 'admin', SQLITE3_TEXT);
            $stmt->execute();
            
            $db->exec('COMMIT');
            echo "Database initialized successfully\n";
        } catch (Exception $e) {
            $db->exec('ROLLBACK');
            throw $e;
        }
    } else {
        echo "Database already initialized\n";
    }
    
    // Create installed flag
    if (!file_exists(DB_DIR)) {
        mkdir(DB_DIR, 0755, true);
    }
    file_put_contents(DB_DIR.'/installed.lock', '1');
    
} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}
?>