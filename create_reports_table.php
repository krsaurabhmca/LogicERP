<?php
require_once 'core/init.php';
global $conn;

$sql = "CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_name VARCHAR(150) NOT NULL,
    form_id INT NOT NULL,
    config JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT
)";

if ($conn->query($sql)) {
    echo "Reports table created successfully!" . PHP_EOL;
} else {
    echo "Error: " . $conn->error . PHP_EOL;
}
?>
