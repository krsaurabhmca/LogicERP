<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

$sub_id = $_GET['submission_id'] ?? '';
if (!$sub_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request.']);
    exit;
}

$raw_data = fetch_all("SELECT field_id, field_value FROM form_data WHERE submission_id = ?", [$sub_id]);
$data = [];
foreach ($raw_data as $row) {
    $data[$row['field_id']] = $row['field_value'];
}

echo json_encode(['status' => 'success', 'data' => $data]);
?>
