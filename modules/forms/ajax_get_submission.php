<?php
/**
 * AJAX - Fetch Metadata for Submission
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

$sid_raw = $_GET['submission_id'] ?? '';
$sub_id = decrypt_id($sid_raw);

if (!$sub_id) {
    // Fallback to plain if decryption fails (safeguard)
    $sub_id = is_numeric($sid_raw) ? $sid_raw : 0;
}

if (!$sub_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Record Access.']);
    exit;
}

$raw_data = fetch_all("SELECT field_id, field_value FROM form_data WHERE submission_id = ?", [$sub_id]);
$data = [];
foreach ($raw_data as $row) {
    $data[$row['field_id']] = $row['field_value'];
}

echo json_encode([
    'status' => 'success', 
    'data' => $data,
    'plain_id' => "#" . $sub_id // Professional numeric label
]);
