<?php
/**
 * AJAX Bulk Delete Handler
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids_enc = $_POST['ids'] ?? [];
    
    if (empty($ids_enc) || !is_array($ids_enc)) {
        echo json_encode(['status' => 'error', 'message' => 'No records selected for deletion.']);
        exit;
    }

    $success_count = 0;
    foreach ($ids_enc as $enc_id) {
        $sub_id = decrypt_id($enc_id);
        if ($sub_id) {
            $stmt = query("UPDATE form_submissions SET deleted_at = NOW() WHERE submission_id = ?", [$sub_id]);
            if ($stmt) $success_count++;
        }
    }

    if ($success_count > 0) {
        echo json_encode(['status' => 'success', 'message' => "$success_count records deleted successfully."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete selected records or invalid IDs provided.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
