<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_id_enc = $_POST['submission_id'] ?? '';
    $sub_id = decrypt_id($sub_id_enc);

    if (empty($sub_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid Submission ID.']);
        exit;
    }

    // SOFT DELETE: Update deleted_at instead of full DELETE
    $stmt = query("UPDATE form_submissions SET deleted_at = NOW() WHERE submission_id = ?", [$sub_id]);
    
    if ($stmt) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database deletion failed.']);
    }
}
?>
