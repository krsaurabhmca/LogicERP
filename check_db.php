<?php
require_once 'config/db.php';
$res = $conn->query("DESCRIBE form_fields");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
?>
