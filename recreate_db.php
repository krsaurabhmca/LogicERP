<?php
require_once 'core/init.php';
$conn->query("DROP TABLE IF EXISTS report_templates");
$sql = "CREATE TABLE `report_templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `form_id` int(11) NOT NULL,
  `html_content` longtext DEFAULT NULL,
  `data_connections` longtext DEFAULT NULL,
  `css_content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if($conn->query($sql)) {
    echo "Table recreated successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>
