<?php
/**
 * Central Authentication & Session Guard
 * StudentHub Portal - CHARUSAT
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$timeout_duration = 900; // 15 minutes for main portal

// Check remember-me cookie if session not set
if (!isset($_SESSION["user_id"]) && isset($_COOKIE["studenthub_remember"])) {
    $raw_token = $_COOKIE["studenthub_remember"];
    $token_hash = hash('sha256', $raw_token);
    
    $stmt = @mysqli_prepare($conn, "SELECT id, username, name, email, role, status FROM users WHERE remember_token = ? AND status = 'active'");
    if ($stmt) {
        @mysqli_stmt_bind_param($stmt, "s", $token_hash);
        @mysqli_stmt_execute($stmt);
        $res = @mysqli_stmt_get_result($stmt);
        if ($user = @mysqli_fetch_assoc($res)) {
            session_regenerate_id(true);
            $_SESSION["user_id"]       = $user["id"];
            $_SESSION["username"]      = $user["username"];
            $_SESSION["name"]          = $user["name"];
            $_SESSION["email"]         = $user["email"];
            $_SESSION["role"]          = $user["role"];
            $_SESSION["last_activity"] = time();
        }
        @mysqli_stmt_close($stmt);
    }
}

// Inactivity timeout check
if (isset($_SESSION["last_activity"])) {
    if (time() - $_SESSION["last_activity"] > $timeout_duration) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
        header("Location: login.php?error=" . urlencode("Your session expired due to inactivity. Please log in again."));
        exit;
    }
}

if (isset($_SESSION["user_id"])) {
    $_SESSION["last_activity"] = time();
}

function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php?error=" . urlencode("Please log in to access this academic module."));
        exit;
    }
}

function require_role($allowed_roles = ['admin']) {
    require_login();
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    $role = $_SESSION["role"] ?? 'student';
    if (!in_array($role, $allowed_roles, true)) {
        http_response_code(403);
        die("<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;background:#fff;border-radius:8px;box-shadow:0 4px 15px rgba(0,0,0,0.1);text-align:center;'>
            <h2 style='color:#C05A6F;'>403 - Access Forbidden</h2>
            <p style='color:#555;'>You do not have the required permissions (" . implode(', ', $allowed_roles) . ") to view this page.</p>
            <p>Your current role is: <strong>" . htmlspecialchars($role) . "</strong></p>
            <a href='dashboard.php' style='color:#245B73;font-weight:bold;'>&larr; Return to Dashboard</a>
        </div>");
    }
}

function require_admin() {
    require_role(['admin']);
}

function require_editor() {
    require_role(['admin', 'editor']);
}

function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

function is_admin() {
    return (($_SESSION["role"] ?? '') === 'admin');
}

function current_user_id() {
    return (int)($_SESSION["user_id"] ?? 0);
}

function current_user_role() {
    return $_SESSION["role"] ?? 'guest';
}
?>
