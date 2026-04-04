<?php
/**
 * AJAX Save Field Handler - Enhanced
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = $_POST['form_id'] ?? '';
    $field_id = $_POST['field_id'] ?? ''; // For Updates
    $label = xss_clean($_POST['field_label'] ?? '');
    $name = xss_clean($_POST['field_name'] ?? '');
    $type = xss_clean($_POST['field_type'] ?? 'text');
    $is_req = $_POST['is_required'] ?? 0;
    $is_visible = $_POST['is_visible'] ?? 1;
    $show_in_table = $_POST['show_in_table'] ?? 0;
    $field_options = $_POST['field_options'] ?? '';
    $dynamic_query = $_POST['dynamic_query'] ?? '';
    $allowed_roles = json_encode($_POST['allowed_roles'] ?? []);
    $default_value = $_POST['default_value'] ?? '';
    $min_date = $_POST['min_date'] ?: null;
    $max_date = $_POST['max_date'] ?: null;
    $allow_future = $_POST['allow_future'] ?? 0;
    $allow_past = $_POST['allow_past'] ?? 0;

    if (empty($form_id) || empty($label) || empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }

    $success = false;
    if (!empty($field_id)) {
        // UPDATE Existing Field
        $stmt = query("UPDATE form_fields SET field_label = ?, field_name = ?, field_type = ?, is_required = ?, is_visible = ?, field_options = ?, dynamic_query = ?, show_in_table = ?, allowed_roles = ?, default_value = ?, min_date = ?, max_date = ?, allow_future = ?, allow_past = ? WHERE field_id = ?", 
                      [$label, $name, $type, $is_req, $is_visible, $field_options, $dynamic_query, $show_in_table, $allowed_roles, $default_value, $min_date, $max_date, $allow_future, $allow_past, $field_id]);
        $success = $stmt ? true : false;
    } else {
        // INSERT New Field
        $stmt = query("INSERT INTO form_fields (form_id, field_label, field_name, field_type, is_required, is_visible, field_options, dynamic_query, show_in_table, allowed_roles, default_value, min_date, max_date, allow_future, allow_past) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                      [$form_id, $label, $name, $type, $is_req, $is_visible, $field_options, $dynamic_query, $show_in_table, $allowed_roles, $default_value, $min_date, $max_date, $allow_future, $allow_past]);
        $success = $stmt ? true : false;
    }
    
    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database operation failed.']);
    }
}
?>
