<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

$res = fetch_all("SELECT form_id as id, form_name as val FROM forms WHERE is_module = 1 AND is_active = 1");
echo json_encode($res);
?>
