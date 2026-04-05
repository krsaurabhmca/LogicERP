<?php
/**
 * Logout Handler
 * LogicERP Modular Framework
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array(); 
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect using absolute path
require_once __DIR__ . '/config/db.php';
header("Location: " . BASE_URL . "login.php?msg=" . urlencode("Logged out successfully"));
exit();
?>
