<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

$form_id = $_GET['form_id'] ?? '';
if (!$form_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Form ID.']);
    exit;
}

$fields = fetch_all("SELECT field_id, field_label, field_name, field_type FROM form_fields WHERE form_id = ? ORDER BY field_order ASC", [$form_id]);

echo json_encode(['status' => 'success', 'data' => $fields]);
?>
