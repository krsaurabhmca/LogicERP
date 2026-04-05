<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_name = xss_clean($_POST['report_name'] ?? '');
    $form_id = $_POST['form_id'] ?? '';
    $config_json = $_POST['config'] ?? '';

    if (!$report_name || !$form_id || !$config_json) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Report Configuration.']);
        exit;
    }

    $stmt = query("INSERT INTO reports (report_name, form_id, config, created_by) VALUES (?, ?, ?, ?)", [$report_name, $form_id, $config_json, $_SESSION['user_id']]);
    
    if ($stmt) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save report.']);
    }
}
?>
