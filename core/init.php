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
        redirect(BASE_URL . 'login.php', 'Please login to continue.', 'danger');
    }
    
    // Developer role has total access everywhere bypass
    if ($user_role === 'dev') return true;

    // Normal role check
    if ($role) {
        // Handle comma-separated list of roles
        $roles = is_array($role) ? $role : array_map('trim', explode(',', $role));
        if (!in_array($user_role, $roles)) {
            redirect(BASE_URL . 'index.php', 'Unauthorized access.', 'warning');
        }
    }
}
?>
