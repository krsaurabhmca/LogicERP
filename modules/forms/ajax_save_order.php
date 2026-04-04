<?php
/**
 * AJAX Bulk Save Order - LogicERP
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = $_POST['order'] ?? [];

    if (empty($order) || !is_array($order)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid order data.']);
        exit;
    }

    $success = true;
    foreach ($order as $index => $field_id) {
        $sort_order = ($index + 1) * 10; // Use 10-20-30 spacing
        $stmt = query("UPDATE form_fields SET field_order = ? WHERE field_id = ?", [$sort_order, $field_id]);
        if (!$stmt) $success = false;
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save order.']);
    }
}
?>
