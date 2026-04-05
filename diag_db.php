<?php
require_once 'core/init.php';
$r = fetch_all("SHOW TABLES LIKE 'report_templates'");
if(!empty($r)) {
    echo "Table exists.\n";
    $c = fetch_all("DESCRIBE report_templates");
    foreach($c as $row) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Table DOES NOT exist!\n";
}
?>
