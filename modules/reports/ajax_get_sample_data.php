<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

$form_id = $_GET['form_id'] ?? '';
if (!$form_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Form ID.']);
    exit;
}

// 1. Fetch Latest Submission
$sub = fetch_one("SELECT submission_id, created_at FROM form_submissions WHERE form_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1", [$form_id]);

if (!$sub) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

// 2. Fetch Values
// Fetch Form Name for Prefix
$form = fetch_one("SELECT form_name FROM forms WHERE form_id = ?", [$form_id]);
$prefix = strtolower(str_replace(' ', '_', $form['form_name'] ?? 'record'));

$data = fetch_all("SELECT f.field_name, f.field_type, d.field_value FROM form_data d JOIN form_fields f ON d.field_id = f.field_id WHERE d.submission_id = ?", [$sub['submission_id']]);

$mapped_data = [
    'global.submission_id' => $sub['submission_id'],
    'global.created_at' => date('d M Y', strtotime($sub['created_at'])),
    'global.sys_date' => date('d-m-Y')
];

foreach ($data as $row) {
    $norm_field = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim($row['field_name'])));
    $val = $row['field_value'];

    // Professional Designer Preview (Placeholder)
    if(($row['field_type'] === 'file' || $row['field_type'] === 'image' || strpos($norm_field, 'photo') !== false)) {
        $val = "https://ui-avatars.com/api/?name=".urlencode($norm_field)."&background=random&size=200&rounded=true";
    }

    $mapped_data[$norm_field] = $val; 
    $mapped_data[$prefix . '.' . $norm_field] = $val; 
}

echo json_encode(['status' => 'success', 'data' => $mapped_data]);
?>
