<?php
require_once 'core/init.php';
$name = "Test Template";
$mid = 1; // Assuming module 1 exists
$html = "<html><body>TEST</body></html>";
$css = "";
$connections = "[]";

$sql = "INSERT INTO report_templates (template_name, form_id, html_content, css_content, data_connections) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if(!$stmt) die($conn->error);
$stmt->bind_param("sisss", $name, $mid, $html, $css, $connections);
if($stmt->execute()) {
    echo "Save Success!";
    $tid = $stmt->insert_id;
    // Cleanup
    $conn->query("DELETE FROM report_templates WHERE template_id = $tid");
} else {
    echo "Save Failed: " . $stmt->error;
}
?>
