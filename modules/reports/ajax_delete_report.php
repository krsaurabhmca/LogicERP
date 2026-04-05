<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

$id_enc = $_POST['id'] ?? '';
$id = decrypt_id($id_enc);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request.']);
    exit;
}

$success = execute("DELETE FROM reports WHERE report_id = ?", [$id]);

if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
