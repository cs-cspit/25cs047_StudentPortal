<?php
/**
 * StudentHub Central Database Configuration
 * Charotar University of Science and Technology (CHARUSAT)
 * Faculty of Technology and Engineering (FTE) - ITUE203
 * Developer: Dhruvi Mokani (25CS047)
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'studenthub_db');

// Fallback databases list in case studenthub_db is not yet imported
$fallback_dbs = ['studenthub_db', 'campusconnect_db', 'event_db', 'student_db', 'user_registration', 'studenthub'];

$conn = null;
$pdo = null;

// Try primary database, then fallback if needed
foreach ($fallback_dbs as $db_candidate) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $test_conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, $db_candidate);
    if ($test_conn) {
        $conn = $test_conn;
        mysqli_set_charset($conn, 'utf8mb4');
        break;
    }
}

if (!$conn) {
    // If none exist, attempt to connect to MySQL server and auto-create studenthub_db
    $root_conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if ($root_conn) {
        @mysqli_query($root_conn, "CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, 'studenthub_db');
        if ($conn) {
            mysqli_set_charset($conn, 'utf8mb4');
        }
    }
}

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;background:#fde8e8;color:#9b1c1c;border:1px solid #f8b4b4;border-radius:8px;max-width:600px;margin:30px auto;'>
        <h3 style='margin-top:0;'>Database Connection Error</h3>
        <p>Could not connect to MySQL server. Please make sure MySQL is running in your XAMPP Control Panel.</p>
        <p><strong>Error:</strong> " . htmlspecialchars(mysqli_connect_error()) . "</p>
        <p><em>Database target: studenthub_db</em></p>
    </div>");
}

// Initialize PDO connection
try {
    $active_db = mysqli_fetch_row(mysqli_query($conn, "SELECT DATABASE()"))[0] ?? DB_NAME;
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . $active_db . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Fallback if PDO fails
    $pdo = null;
}

// Audit logging helper (Practical 14 & 15)
function log_audit_trail($conn, $userId, $action, $details = null) {
    if (!$conn) return false;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    // Check if audit_logs table exists
    $table_check = @mysqli_query($conn, "SHOW TABLES LIKE 'audit_logs'");
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        $stmt = @mysqli_prepare($conn, "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            @mysqli_stmt_bind_param($stmt, "isss", $userId, $action, $details, $ip);
            $res = @mysqli_stmt_execute($stmt);
            @mysqli_stmt_close($stmt);
            return $res;
        }
    }
    return false;
}
