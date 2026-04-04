<?php
/**
 * REST API: Dynamic Forms Explorer
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

// Simple API Key check
$api_key = $_GET['api_key'] ?? '';
if (empty($api_key)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// Fetch all active forms
$forms = fetch_all("SELECT form_id, form_name, form_description, created_at FROM forms WHERE is_active = 1");

echo json_encode([
    'status' => 'success',
    'count' => count($forms),
    'forms' => $forms
]);
?>
