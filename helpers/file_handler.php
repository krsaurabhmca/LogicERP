<?php
/**
 * File Management Wrapper
 * LogicERP Modular Framework
 */

function upload_file($file, $module = 'general') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => 'No file uploaded or upload error.'];
    }

    $upload_base = __DIR__ . '/../uploads/';
    $year = date('Y');
    $month = date('m');
    $target_dir = $upload_base . $module . '/' . $year . '/' . $month . '/';

    // Create directories if not exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

    if (!in_array($file_ext, $allowed_exts)) {
        return ['status' => 'error', 'message' => 'Invalid file type.'];
    }

    // Auto rename file: logic_erp_{timestamp}_{random}.ext
    $new_name = 'lerp_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
    $target_file = $target_dir . $new_name;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Return public path relative to root
        $public_path = 'uploads/' . $module . '/' . $year . '/' . $month . '/' . $new_name;
        return ['status' => 'success', 'path' => $public_path, 'name' => $new_name];
    }

    return ['status' => 'error', 'message' => 'Failed to move uploaded file.'];
}
?>
