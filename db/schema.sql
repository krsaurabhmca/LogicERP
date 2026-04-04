-- LogicERP Core Database Schema

-- 1. Roles
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(100) NOT NULL UNIQUE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `perm_id` INT AUTO_INCREMENT PRIMARY KEY,
  `perm_name` VARCHAR(100) NOT NULL UNIQUE, -- e.g., 'view_members', 'edit_members'
  `perm_group` VARCHAR(100) DEFAULT 'General',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Role Permissions (Mapping)
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `rp_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `perm_id` INT NOT NULL,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE,
  FOREIGN KEY (`perm_id`) REFERENCES `permissions`(`perm_id`) ON DELETE CASCADE
);

-- 4. Users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL, -- Direct role assignment
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`)
);

-- 5. Audit logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `audit_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action` VARCHAR(255),
  `table_name` VARCHAR(100),
  `old_value` TEXT,
  `new_value` TEXT,
  `ip_address` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Roles
INSERT INTO `roles` (`role_name`) VALUES ('Admin'), ('Staff'), ('Viewer');

-- Initial Admin User (password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_DEFAULT);
INSERT INTO `users` (`full_name`, `email`, `password`, `role_id`) 
VALUES ('System Admin', 'admin@logicerp.com', '$2y$10$0xUxtno3CMf2G68s9wuqGeWEAjf4VrIi/XAH0VBc3c.MU9P4Szl/q', 1);

-- 6. Forms
CREATE TABLE IF NOT EXISTS `forms` (
  `form_id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_name` VARCHAR(100) NOT NULL,
  `form_description` TEXT,
  `layout_json` JSON, -- Stores the row/column structure
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
);

-- 7. Form Fields
CREATE TABLE IF NOT EXISTS `form_fields` (
  `field_id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `field_label` VARCHAR(255) NOT NULL,
  `field_name` VARCHAR(100) NOT NULL, -- The column name in DB/JSON
  `field_type` ENUM('text', 'number', 'email', 'textarea', 'select', 'checkbox', 'radio', 'date', 'file', 'hidden') DEFAULT 'text',
  `field_options` TEXT, -- JSON for select/radio options
  `is_required` TINYINT(1) DEFAULT 0,
  `is_visible` TINYINT(1) DEFAULT 1, -- Hide/Show toggle
  `field_order` INT DEFAULT 0,
  `field_rules` TEXT, -- Validation rules (JSON)
  `dynamic_query` TEXT, -- For dynamic dropdowns (SQL)
  FOREIGN KEY (`form_id`) REFERENCES `forms`(`form_id`) ON DELETE CASCADE
);

-- 8. Form Submissions (The Header)
CREATE TABLE IF NOT EXISTS `form_submissions` (
  `submission_id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `user_id` INT, -- Who submitted
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`form_id`) REFERENCES `forms`(`form_id`) ON DELETE CASCADE
);

-- 9. Form Data (The Values)
CREATE TABLE IF NOT EXISTS `form_data` (
  `data_id` INT AUTO_INCREMENT PRIMARY KEY,
  `submission_id` INT NOT NULL,
  `field_id` INT NOT NULL,
  `field_value` LONGTEXT,
  FOREIGN KEY (`submission_id`) REFERENCES `form_submissions`(`submission_id`) ON DELETE CASCADE,
  FOREIGN KEY (`field_id`) REFERENCES `form_fields`(`field_id`) ON DELETE CASCADE
);

-- 10. Tasks
CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_title` VARCHAR(255) NOT NULL,
  `task_desc` TEXT,
  `assigned_to` INT,
  `due_date` DATE,
  `status` ENUM('pending', 'in-progress', 'done', 'overdue') DEFAULT 'pending',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
);
