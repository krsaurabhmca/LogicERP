<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf($token)) {
        echo json_encode(['status' => 'error', 'message' => 'Security token mismatch.']);
        exit;
    }

    $form_id = $_POST['form_id'] ?? '';
    $submission_id = $_POST['submission_id'] ?? '';
    $submission_id_is_new = empty($submission_id); // FLAG for serials
    
    if (!$form_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Form.']);
        exit;
    }

    $fields = fetch_all("SELECT * FROM form_fields WHERE form_id = ?", [$form_id]);

    if (empty($submission_id)) {
        // CREATE NEW
        $stmt_head = query("INSERT INTO form_submissions (form_id, user_id) VALUES (?, ?)", [$form_id, $_SESSION['user_id'] ?? null]);
        global $conn;
        $submission_id = $conn->insert_id;
    } else {
        // UPDATE (Clear existing field data first if needed, or just REPLACE/UPDATE)
        // For simplicity and to avoid complex logic, we update each field value separately
    }

    foreach ($fields as $field) {
        $val = $_POST[$field['field_name']] ?? '';
        
        // Handle Auto-Serial
        if ($field['field_type'] == 'serial' && $submission_id_is_new) {
             $max = fetch_one("SELECT MAX(CAST(field_value AS UNSIGNED)) as m FROM form_data WHERE field_id = ?", [$field['field_id']]);
             $val = ($max['m'] ?? 0) + 1;
        }

        // Handle Multi-Select / Checkbox Groups
        if (is_array($val)) {
            $val = json_encode($val);
        }

        // Handle File Uploads
        if ($field['field_type'] == 'file' || $field['field_type'] == 'camera') {
            if (!empty($_FILES[$field['field_name']]['name'])) {
                $dir = __DIR__ . '/../../uploads/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                
                $ext = pathinfo($_FILES[$field['field_name']]['name'], PATHINFO_EXTENSION);
                $fname = $field['field_name'] . '_' . time() . '_' . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($_FILES[$field['field_name']]['tmp_name'], $dir . $fname)) {
                    $val = $fname;
                }
            } else {
                // Keep existing file if updating and no new file uploaded
                if (!empty($submission_id)) {
                    $old = fetch_one("SELECT field_value FROM form_data WHERE submission_id = ? AND field_id = ?", [$submission_id, $field['field_id']]);
                    if ($old) $val = $old['field_value'];
                }
            }
        }
        
        // Save to Database
        $existing = fetch_one("SELECT data_id FROM form_data WHERE submission_id = ? AND field_id = ?", [$submission_id, $field['field_id']]);
        if ($existing) {
            query("UPDATE form_data SET field_value = ? WHERE data_id = ?", [$val, $existing['data_id']]);
        } else {
            query("INSERT INTO form_data (submission_id, field_id, field_value) VALUES (?, ?, ?)", [$submission_id, $field['field_id'], $val]);
        }
    }

    echo json_encode(['status' => 'success']);
}
?>
