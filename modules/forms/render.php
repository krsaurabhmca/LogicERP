<?php
/**
 * Dynamic Form Renderer - Enhanced
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';

$id_enc = $_GET['id'] ?? '';
$form_id = decrypt_id($id_enc);

if (!$form_id) die("Invalid Form ID.");

$form = fetch_one("SELECT * FROM forms WHERE form_id = ?", [$form_id]);
if (!$form) die("Form not found.");

$fields = fetch_all("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC", [$form_id]);

// Submission Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf($token)) die("Security validation failed.");
    
    $stmt_head = query("INSERT INTO form_submissions (form_id, user_id) VALUES (?, ?)", [$form_id, $_SESSION['user_id'] ?? null]);
    if ($stmt_head) {
        global $conn;
        $submission_id = $conn->insert_id;
        foreach ($fields as $field) {
            $val = $_POST[$field['field_name']] ?? '';
            // Handle array values (checkboxes, multi-select)
            if (is_array($val)) {
                $val = implode(', ', $val);
            }
            query("INSERT INTO form_data (submission_id, field_id, field_value) VALUES (?, ?, ?)", [$submission_id, $field['field_id'], $val]);
        }
        $success = "Submission successful!";
    } else {
        $error = "Failed to save submission.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $form['form_name']; ?> | LogicERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; padding: 40px 10px; }
        .form-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 15px rgba(0,0,0,0.03); max-width: 750px; margin: 0 auto; overflow: hidden; border: 1px solid #eef2f6; }
        .header { background: #4f46e5; color: #fff; padding: 40px; }
        .body { padding: 40px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; font-size: 0.95rem; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); border-color: #4f46e5; }
        .btn-primary { background: #4f46e5; border: none; border-radius: 12px; padding: 14px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="form-card">
        <div class="header">
            <h1 class="fw-bold h4 mb-1"><?php echo $form['form_name']; ?></h1>
            <p class="mb-0 opacity-75 small"><?php echo $form['form_description'] ?: 'Please fill all metadata.'; ?></p>
        </div>

        <div class="body">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success p-5 rounded-4 text-center">
                    <h5 class="fw-bold">Success!</h5>
                    <p class="mb-4"><?php echo $success; ?></p>
                    <a href="render.php?id=<?php echo $id_enc; ?>" class="btn btn-outline-success px-5 rounded-pill">OK</a>
                </div>
            <?php else: ?>
                <form action="render.php?id=<?php echo $id_enc; ?>" method="POST" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
                    <?php foreach ($fields as $field): ?>
                        <div class="col-md-<?php echo (($field['field_type'] ?? 'text') == 'textarea' ? '12' : '6'); ?> <?php echo !($field['is_visible'] ?? 1) ? 'd-none' : ''; ?>">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">
                                <?php echo $field['field_label'] ?? 'Untitled'; ?> <?php echo ($field['is_required'] ?? 0) ? '<span class="text-danger">*</span>' : ''; ?>
                            </label>
                            
                            <?php 
                                $options = [];
                                if(!empty($field['dynamic_module']) && !empty($field['dynamic_label_column'])) {
                                    $mod_id = $field['dynamic_module'];
                                    $label_field_id = $field['dynamic_label_column'];
                                    
                                    // Fetch data from form_data table joining with submissions
                                    // The ID is the submission_id, the Val is the value of the selected label field
                                    $results = fetch_all("SELECT s.submission_id as id, d.field_value as val 
                                                         FROM form_submissions s 
                                                         JOIN form_data d ON s.submission_id = d.submission_id 
                                                         WHERE s.form_id = ? AND d.field_id = ? AND s.deleted_at IS NULL", [$mod_id, $label_field_id]);
                                    
                                    foreach($results as $row) $options[] = ['id' => $row['id'], 'val' => $row['val']];
                                } elseif(!empty(trim($field['dynamic_query'] ?? ''))) {
                                    $results = fetch_all($field['dynamic_query']);
                                    foreach($results as $row) $options[] = ['id' => $row['id'], 'val' => $row['val']];
                                } else {
                                    $opts = explode("\n", $field['field_options'] ?? '');
                                    foreach($opts as $opt) { 
                                        $opt = trim($opt); 
                                        if($opt) $options[] = ['id' => $opt, 'val' => $opt]; 
                                    }
                                }
                            ?>

                            <?php if (($field['field_type'] ?? 'text') == 'select'): ?>
                                <select name="<?php echo $field['field_name'] ?? 'none'; ?>" class="form-select" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                                    <option value="">Select option...</option>
                                    <?php foreach($options as $opt): ?>
                                        <option value="<?php echo htmlspecialchars($opt['id']); ?>"><?php echo htmlspecialchars($opt['val']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif (($field['field_type'] ?? 'text') == 'multi_select'): ?>
                                <select name="<?php echo $field['field_name'] ?? 'none'; ?>[]" class="form-select" multiple style="height: 120px;" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                                    <?php foreach($options as $opt): ?>
                                        <option value="<?php echo htmlspecialchars($opt['id']); ?>"><?php echo htmlspecialchars($opt['val']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="smaller text-muted mt-1" style="font-size: 0.65rem;">Hold Ctrl (or Cmd) to select multiple.</div>
                            <?php elseif (($field['field_type'] ?? 'text') == 'checkbox_group'): ?>
                                <div class="card bg-light border-0 p-3 rounded-3">
                                    <div class="row g-2">
                                        <?php foreach($options as $index => $opt): ?>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="<?php echo $field['field_name']; ?>[]" value="<?php echo htmlspecialchars($opt['id']); ?>" id="check_<?php echo $field['field_id'].'_'.$index; ?>">
                                                    <label class="form-check-label small" for="check_<?php echo $field['field_id'].'_'.$index; ?>">
                                                        <?php echo htmlspecialchars($opt['val']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php elseif (($field['field_type'] ?? 'text') == 'textarea'): ?>
                                <textarea name="<?php echo $field['field_name'] ?? 'none'; ?>" class="form-control" rows="4" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>></textarea>
                            <?php else: ?>
                                <input type="<?php echo $field['field_type'] ?? 'text'; ?>" name="<?php echo $field['field_name'] ?? 'none'; ?>" class="form-control" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-12 mt-5 pt-3">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Submit Entry</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
