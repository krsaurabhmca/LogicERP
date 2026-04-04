<?php
/**
 * Dynamic Module Export - Excel (CSV) & PDF (Print)
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

$id_enc = $_GET['id'] ?? '';
$form_id = decrypt_id($id_enc);
$type = $_GET['type'] ?? 'csv'; // csv or print

if (!$form_id) die('Invalid Module ID.');

$form = fetch_one("SELECT * FROM forms WHERE form_id = ?", [$form_id]);
if (!$form) die('Module not found.');

// Fetch Fields for headers
$fields = fetch_all("SELECT * FROM form_fields WHERE form_id = ? AND show_in_table = 1 ORDER BY field_order ASC", [$form_id]);

// Fetch Submissions
$submissions = fetch_all("
    SELECT s.*, u.full_name as submitted_by 
    FROM form_submissions s
    LEFT JOIN users u ON s.user_id = u.user_id
    WHERE s.form_id = ? AND s.deleted_at IS NULL
    ORDER BY s.created_at DESC
", [$form_id]);

$data_map = [];
if (!empty($submissions)) {
    $sub_ids = array_column($submissions, 'submission_id');
    $placeholders = implode(',', array_fill(0, count($sub_ids), '?'));
    $raw_data = fetch_all("SELECT submission_id, field_id, field_value FROM form_data WHERE submission_id IN ($placeholders)", $sub_ids);
    foreach ($raw_data as $row) { 
        $data_map[$row['submission_id']][$row['field_id']] = $row['field_value']; 
    }
}

// --- CSV EXPORT ---
if ($type === 'csv') {
    $filename = str_replace(' ', '_', $form['form_name']) . "_" . date('Y-m-d_H-i') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');

    // Header Row
    $header = ['ID'];
    foreach ($fields as $f) $header[] = strtoupper($f['field_label']);
    $header[] = 'SUBMITTED BY';
    $header[] = 'DATE';
    fputcsv($output, $header);

    // Data Rows
    foreach ($submissions as $sub) {
        $row = [$sub['submission_id']];
        foreach ($fields as $f) {
            $val = $data_map[$sub['submission_id']][$f['field_id']] ?? '';
            if (is_array($val) || (strpos($val ?? '', '[') === 0 && strpos($val ?? '', ']') !== false)) {
                $arr = is_array($val) ? $val : json_decode($val, true);
                $val = is_array($arr) ? implode(', ', $arr) : $val;
            }
            $row[] = $val;
        }
        $row[] = $sub['submitted_by'];
        $row[] = $sub['created_at'];
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// --- PRINT / PDF VIEW ---
if ($type === 'print') {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Export - <?php echo $form['form_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { font-size: 10pt; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
        }
    </style>
</head>
<body class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0"><?php echo $form['form_name']; ?> - Data Export</h4>
        <button onclick="window.print()" class="btn btn-primary">Print to PDF</button>
    </div>

    <div class="text-center mb-4">
        <h2 class="fw-bold mb-1"><?php echo strtoupper($form['form_name']); ?></h2>
        <p class="text-muted">Generated on <?php echo date('F j, Y, g:i a'); ?></p>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <?php foreach ($fields as $f): ?>
                    <th><?php echo strtoupper($f['field_label']); ?></th>
                <?php endforeach; ?>
                <th>SUBMITTED BY</th>
                <th>DATE</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $sub): ?>
            <tr>
                <td>#<?php echo $sub['submission_id']; ?></td>
                <?php foreach ($fields as $f): 
                    $val = $data_map[$sub['submission_id']][$f['field_id']] ?? '-';
                ?>
                <td>
                    <?php 
                        if (is_array($val) || (strpos($val ?? '', '[') === 0 && strpos($val ?? '', ']') !== false)) {
                            $arr = is_array($val) ? $val : json_decode($val, true);
                            echo htmlspecialchars(is_array($arr) ? implode(', ', $arr) : $val);
                        } else {
                            echo htmlspecialchars($val); 
                        }
                    ?>
                </td>
                <?php endforeach; ?>
                <td><?php echo $sub['submitted_by']; ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($sub['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
<?php
    exit;
}
?>
