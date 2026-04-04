<?php
/**
 * AJAX Handler: Save Module Global Settings (RBAC, Dashboard)
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$form_id = $_POST['form_id'] ?? 0;
if (!$form_id) {
    echo json_encode(['status' => 'error', 'message' => 'Module ID is required.']);
    exit;
}

$allowed_roles = $_POST['allowed_roles'] ?? [];
$is_module = isset($_POST['is_module']) ? 1 : 0;
$show_on_dashboard = isset($_POST['show_on_dashboard']) ? 1 : 0;
$module_icon = xss_clean($_POST['module_icon'] ?? 'bi-collection');
$module_category = xss_clean($_POST['module_category'] ?? 'General');

$roles_json = json_encode($allowed_roles);

$sql = "UPDATE forms SET 
        is_module = ?, 
        allowed_roles = ?, 
        show_on_dashboard = ?, 
        module_icon = ?, 
        module_category = ? 
        WHERE form_id = ?";

$stmt = query($sql, [$is_module, $roles_json, $show_on_dashboard, $module_icon, $module_category, $form_id]);

if ($stmt) {
    echo json_encode(['status' => 'success', 'message' => 'Module settings updated.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error while saving.']);
}
?>
