<?php
/**
 * AJAX Reorder Field - LogicERP
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field_id = $_POST['field_id'] ?? '';
    $dir = $_POST['dir'] ?? ''; // 'up' or 'down'

    if (empty($field_id) || empty($dir)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']); exit;
    }

    $current = fetch_one("SELECT field_id, field_order, form_id FROM form_fields WHERE field_id = ?", [$field_id]);
    if (!$current) {
        echo json_encode(['status' => 'error', 'message' => 'Field not found.']); exit;
    }

    $op = ($dir === 'up') ? '<' : '>';
    $sort = ($dir === 'up') ? 'DESC' : 'ASC';

    // Find the neighbor to swap with
    $target = fetch_one("
        SELECT field_id, field_order 
        FROM form_fields 
        WHERE form_id = ? AND field_order $op ? 
        ORDER BY field_order $sort LIMIT 1
    ", [$current['form_id'], $current['field_order']]);

    if ($target) {
        // Swap orders
        query("UPDATE form_fields SET field_order = ? WHERE field_id = ?", [$target['field_order'], $current['field_id']]);
        query("UPDATE form_fields SET field_order = ? WHERE field_id = ?", [$current['field_order'], $target['field_id']]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Already at the edge.']);
    }
}
?>
