<?php
/**
 * Core Initialization
 * LogicERP Modular Framework
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inlude Config & Helpers
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/security.php';

// Common Global Variables
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? 'guest';

// Auto-redirect if trying to access restricted pages
function check_auth($role = null) {
    global $is_logged_in, $user_role;
    if (!$is_logged_in) {
        redirect('login.php', 'Please login to continue.', 'danger');
    }
    if ($role && $user_role !== 'admin' && $user_role !== $role) {
        redirect('index.php', 'Unauthorized access.', 'warning');
    }
}
?>
