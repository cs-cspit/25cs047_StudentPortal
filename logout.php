<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION["user_id"])) {
    $uid = (int)$_SESSION["user_id"];
    
    // Clear remember token in DB
    $clear_token = @mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE id = ?");
    if ($clear_token) {
        @mysqli_stmt_bind_param($clear_token, "i", $uid);
        @mysqli_stmt_execute($clear_token);
        @mysqli_stmt_close($clear_token);
    }

    log_audit_trail($conn, $uid, "User Logged Out", "Logged out from main StudentHub portal");
}

// Clear cookie
if (isset($_COOKIE["studenthub_remember"])) {
    setcookie("studenthub_remember", "", time() - 3600, "/");
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

header("Location: login.php?message=" . urlencode("You have been safely logged out."));
exit;
?>
