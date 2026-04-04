<?php
require_once 'core/init.php';

// Add all missing columns for LogicERP Low-Code Features
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS show_in_table TINYINT(1) DEFAULT 1;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS allowed_roles TEXT;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS default_value TEXT;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS min_date DATE NULL;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS max_date DATE NULL;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS allow_future TINYINT(1) DEFAULT 1;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS allow_past TINYINT(1) DEFAULT 1;");
$conn->query("ALTER TABLE form_fields ADD COLUMN IF NOT EXISTS field_width INT DEFAULT 12;");

echo "Database successfully standardized for all Low-Code features.";
?>
