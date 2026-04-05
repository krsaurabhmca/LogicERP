<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = $_POST['form_id'] ?? '';
    $selected_field_ids = $_POST['fields'] ?? [];

    if (!$form_id || empty($selected_field_ids)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid configuration.']);
        exit;
    }

    // 1. Fetch Field Metadata for headers
    $placeholders = implode(',', array_fill(0, count($selected_field_ids), '?'));
    $field_meta = fetch_all("SELECT field_id, field_label FROM form_fields WHERE field_id IN ($placeholders) AND form_id = ?", array_merge($selected_field_ids, [$form_id]));
    
    $headers = [];
    $field_id_to_label = [];
    foreach ($field_meta as $fm) {
        $headers[] = $fm['field_label'];
        $field_id_to_label[$fm['field_id']] = $fm['field_label'];
    }

    // 2. Fetch Submission Data (Limit to 5 for preview)
    $submissions = fetch_all("SELECT submission_id FROM form_submissions WHERE form_id = ? ORDER BY created_at DESC LIMIT 5", [$form_id]);
    
    $preview_data = [];
    foreach ($submissions as $sub) {
        $sub_id = $sub['submission_id'];
        $row = [];
        
        // Fetch values for this submission
        $data = fetch_all("SELECT field_id, field_value FROM form_data WHERE submission_id = ? AND field_id IN ($placeholders)", array_merge([$sub_id], $selected_field_ids));
        
        // Initialize Row
        foreach ($headers as $h) $row[$h] = '-';
        
        foreach ($data as $d) {
            $label = $field_id_to_label[$d['field_id']] ?? null;
            if ($label) $row[$label] = $d['field_value'];
        }
        $preview_data[] = $row;
    }

    echo json_encode([
        'status' => 'success', 
        'headers' => $headers,
        'data' => $preview_data
    ]);
}
?>
