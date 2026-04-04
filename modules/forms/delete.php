<?php
/**
 * Delete Form Handler
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth('admin');

$id_enc = $_GET['id'] ?? '';
$form_id = decrypt_id($id_enc);

if (!$form_id) {
    redirect('index.php', 'Invalid Form ID.', 'danger');
}

// Check if form exists
$form = fetch_one("SELECT form_name FROM forms WHERE form_id = ?", [$form_id]);
if (!$form) {
    redirect('index.php', 'Form not found.', 'danger');
}

// Delete Form (Cascades to fields and submissions)
$stmt = query("DELETE FROM forms WHERE form_id = ?", [$form_id]);

if ($stmt) {
    redirect('index.php', "Module '{$form['form_name']}' has been deleted successfully.", 'success');
} else {
    redirect('index.php', "Failed to delete the module.", 'danger');
}
?>
