<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

$form_id = $_GET['table'] ?? ''; // Renamed from table for consistency
if (empty($form_id)) {
    echo json_encode([]);
    exit;
}

$res = fetch_all("SELECT field_id as id, field_label as val FROM form_fields WHERE form_id = ? AND is_visible = 1 AND field_type NOT IN ('section_heading', 'camera', 'file')", [$form_id]);
echo json_encode($res);
?>
