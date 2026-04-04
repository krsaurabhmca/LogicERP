<?php
/**
 * AJAX Delete Field Handler
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field_id = $_POST['field_id'] ?? '';

    if (empty($field_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing field ID.']);
        exit;
    }

    // Success! Delete field
    $stmt = query("DELETE FROM form_fields WHERE field_id = ?", [$field_id]);
    
    if ($stmt) {
        echo json_encode(['status' => 'success', 'message' => 'Field deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete field.']);
    }
}
?>
