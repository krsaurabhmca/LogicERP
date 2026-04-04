<?php
require_once __DIR__ . '/config/db.php';

$queries = [
    "ALTER TABLE forms ADD COLUMN IF NOT EXISTS allowed_roles TEXT DEFAULT NULL AFTER is_module",
    "ALTER TABLE forms ADD COLUMN IF NOT EXISTS show_on_dashboard TINYINT(1) DEFAULT 0 AFTER allowed_roles",
    "ALTER TABLE forms ADD COLUMN IF NOT EXISTS module_category VARCHAR(50) DEFAULT 'General' AFTER show_on_dashboard"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Executed: $q\n";
    } else {
        echo "Error on $q: " . $conn->error . "\n";
    }
}
?>
