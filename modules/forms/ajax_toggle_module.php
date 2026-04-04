<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = $_POST['form_id'] ?? '';
    $status = $_POST['status'] ?? 0;

    if (empty($form_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Form ID.']);
        exit;
    }

    $stmt = query("UPDATE forms SET is_module = ? WHERE form_id = ?", [$status, $form_id]);
    
    if ($stmt) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
    }
}
?>
