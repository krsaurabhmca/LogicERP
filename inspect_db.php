<?php
require 'config/db.php';
$res = $conn->query('DESCRIBE forms');
if ($res) {
    echo "DESCRIBE forms:\n";
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ") - " . $row['Default'] . "\n";
    }
} else { echo "Error: " . $conn->error; }
?>
