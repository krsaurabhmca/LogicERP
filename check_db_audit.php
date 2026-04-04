<?php
require_once 'config/db.php';

$cols = [
    'field_label', 'field_name', 'field_type', 'is_required', 'is_visible', 
    'field_options', 'dynamic_query', 'show_in_table', 'allowed_roles', 
    'default_value', 'min_date', 'max_date', 'allow_future', 'allow_past', 'field_width'
];

echo "--- Schema Report ---\n";
foreach($cols as $col) {
    $res = $conn->query("SHOW COLUMNS FROM form_fields LIKE '$col'");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo "OK: $col ({$row['Type']})\n";
    } else {
        echo "MISSING: $col\n";
    }
}
?>
