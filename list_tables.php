<?php
require_once 'core/init.php';
global $conn;
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    echo $row[0] . PHP_EOL;
}
?>
