<?php
/**
 * Dynamic Document Renderer (Invoice/Receipt/Profile)
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

$tid_enc = $_GET['tid'] ?? '';
$sid_enc = $_GET['sid'] ?? '';

$template_id = decrypt_id($tid_enc);
$submission_id = decrypt_id($sid_enc);

if (!$template_id || !$submission_id) {
    die("Error: Invalid Request Parameters.");
}

// 1. Fetch Template
$template = fetch_one("SELECT * FROM report_templates WHERE template_id = ?", [$template_id]);
if (!$template) die("Error: Template not found.");

// 2. Fetch Submission Data & Table Alias
$sub_head = fetch_one("SELECT * FROM form_submissions WHERE submission_id = ?", [$submission_id]);
$form_meta = fetch_one("SELECT form_name FROM forms WHERE form_id = ?", [$template['form_id']]);

// Normalize Names (student_profile -> student)
$table_alias = strtolower(str_replace(' ', '_', $form_meta['form_name'] ?? 'record'));

$raw_data = fetch_all("SELECT f.field_name, f.field_type, d.field_value FROM form_data d JOIN form_fields f ON d.field_id = f.field_id WHERE d.submission_id = ?", [$submission_id]);

$data_map = [
    'global.submission_id' => $submission_id,
    'global.created_at' => date('d M Y, H:i', strtotime($sub_head['created_at'] ?? '')),
    'global.sys_date' => date('d-m-Y')
];

// Dual-Binding with NORMALIZATION & ASSET RESOLUTION
foreach ($raw_data as $row) {
    $norm_field = strtolower(str_replace(' ', '_', $row['field_name']));
    $val = $row['field_value'];

    // Auto-resolve image paths
    if (($row['field_type'] === 'file' || $row['field_type'] === 'image' || strpos($norm_field, 'photo') !== false) && !empty($val)) {
        $val = BASE_URL . 'uploads/' . $val;
    }

    $data_map[$norm_field] = $val; // Flat
    $data_map[$table_alias . '.' . $norm_field] = $val; // Namespaced
}

// 4. Multi-Table DATA LOOPS (High Performance Processing)
$connections = json_decode($template['data_connections'] ?? '[]', true);
if (!empty($connections)) {
    foreach ($connections as $cn) {
        $tag = $cn['mname'];
        $loop_regex = "/\{\{#{$tag}\}\}(.*?)\{\{\/{$tag}\}\}/s";
        
        if (preg_match_all($loop_regex, $html, $matches)) {
            foreach ($matches[1] as $index => $sub_template) {
                // Fetch linked records
                $child_id_field = $cn['cfield'] ?? 'submission_id'; // Normally submission_id
                
                // Fetch all Linked Submissions
                $child_subs = fetch_all("SELECT d.submission_id FROM form_data d WHERE d.field_id = ? AND d.field_value = ?", [$cn['cfield'], $submission_id]);
                
                $loop_html = "";
                foreach ($child_subs as $cs) {
                    $cs_id = $cs['submission_id'];
                    $cs_data = fetch_all("SELECT f.field_name, d.field_value FROM form_data d JOIN form_fields f ON d.field_id = f.field_id WHERE d.submission_id = ?", [$cs_id]);
                    
                    $temp_sub = $sub_template;
                    foreach ($cs_data as $row) {
                        $norm_field = strtolower(str_replace(' ', '_', $row['field_name']));
                        $token = "{{{$tag}.{$norm_field}}}";
                        $temp_sub = str_replace($token, $row['field_value'], $temp_sub);
                    }
                    $loop_html .= $temp_sub;
                }
                
                $html = str_replace($matches[0][$index], $loop_html, $html);
            }
        }
    }
}

// 5. Final Replacement for Root Fields
foreach ($data_map as $key => $val) {
    if(!is_array($val)) $html = str_replace('{{' . $key . '}}', $val, $html);
}

// 6. Output with Print Logic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Document | LogicERP</title>
    <style>
        body { margin: 0; padding: 0; background: #f5f6fa; font-family: 'Inter', sans-serif; }
        .document-canvas { background: #fff; width: 210mm; min-height: 297mm; margin: 20px auto; padding: 15mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; position: relative; }
        @media print {
            body { background: #fff; }
            .document-canvas { margin: 0; box-shadow: none; width: 100%; border: none; }
            .print-btn { display: none; }
        }
        .print-btn { position: fixed; bottom: 30px; right: 30px; background: #4f46e5; color: #fff; border: none; padding: 15px 30px; border-radius: 50px; font-weight: bold; cursor: pointer; box-shadow: 0 10px 15px rgba(0,0,0,0.2); transition: 0.2s; z-index: 1000; }
        .print-btn:hover { transform: translateY(-5px); background: #4338ca; }
        
        /* User Specific CSS */
        <?php echo $template['css_content']; ?>
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn">
        Print This Document
    </button>

    <div class="document-canvas">
        <?php echo $html; ?>
    </div>
</body>
</html>
