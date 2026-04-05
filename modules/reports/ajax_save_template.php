<?php
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = xss_clean($_POST['template_name'] ?? '');
    $mid = $_POST['form_id'] ?? '';
    $html = $_POST['html'] ?? '';
    $css = $_POST['css'] ?? '';
    $connections = $_POST['data_connections'] ?? '[]'; // JSON format

    if(!$name || !$mid || !$html) {
        echo json_encode(['status' => 'error', 'message' => 'Template name and content required.']);
        exit;
    }

    $tid_enc = $_POST['template_id'] ?? '';
    $tid = !empty($tid_enc) ? decrypt_id($tid_enc) : null;

    try {
        if(!empty($tid) && is_numeric($tid)) {
            // Update logic (Use execute if it exists, but let's be more manual to capture errors)
            $sql = "UPDATE report_templates SET template_name=?, form_id=?, html_content=?, css_content=?, data_connections=? WHERE template_id=?";
            $stmt = $conn->prepare($sql);
            if(!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("sisssi", $name, $mid, $html, $css, $connections, $tid);
        } else {
            // Insert logic
            $sql = "INSERT INTO report_templates (template_name, form_id, html_content, css_content, data_connections) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if(!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("sisss", $name, $mid, $html, $css, $connections);
        }

        if($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
