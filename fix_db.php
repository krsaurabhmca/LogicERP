<?php
require_once 'core/init.php';
$conn->query("ALTER TABLE report_templates ADD COLUMN IF NOT EXISTS html_content LONGTEXT AFTER form_id");
$conn->query("ALTER TABLE report_templates ADD COLUMN IF NOT EXISTS data_connections LONGTEXT AFTER html_content");
$conn->query("ALTER TABLE report_templates MODIFY COLUMN css_content LONGTEXT");
echo "Table structure updated!";
?>
