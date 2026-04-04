# LogicERP Modular Framework v2.0
A robust, low-code ERP platform designed for rapid module deployment and secure role-based operations.

---

## 🚀 Key Features

### 1. Dynamic Form Builder & Module Engine
*   **Low-Code Environment**: Create complex database-driven modules in minutes without writing SQL or PHP.
*   **Dynamic Fields**: Support for text, selection (dynamic and static), date, color, and file uploads.
*   **RBAC Embedded**: Define field-level and module-level permissions for different user types directly from the builder.

### 2. High-Density Professional UI
*   **Vivid Sidebar**: Compact, colorful navigation with absolute path resolution for accurate menu highlighting.
*   **Glassmorphism Dashboard**: Modern, blurred header and responsive summary widgets that adapt to user roles.
*   **Admin/Staff Management**: Segregated organization view allowing admins to manage their respective staff without system-level risks.

### 3. Developer & Enterprise Tools
*   **Menu Designer**: A drag-and-drop structural editor for global navigation nodes (available for `dev` role).
*   **Professional Export Engine**: One-click professional CSV (Excel) and print-ready PDF exports for all dynamic modules.
*   **Auditing**: Built-in `audit_logs` system to track logins, modifications, and system-level changes.

---

## 🛠️ Technology Stack
*   **Backend**: PHP 7.4+ (Standard MySQLi extension)
*   **Database**: MySQL / MariaDB
*   **Frontend**: Vanilla CSS (Premium Tokens), Bootstrap 5.3+
*   **Typography**: Outfit (Modern Sans-Serif)
*   **Icons**: Bootstrap Icons (Vivid Mode)
*   **Interactivity**: jQuery 3.6, Sortable.js (for Menu Designer)

---

## ⚙️ Installation & Setup

1.  **Database Configuration**:
    *   Import `database/schema.sql` (if available) or create a database named `logic_erp`.
    *   Configure connection strings in `config/db.php`.
    ```php
    define('DB_NAME', 'logic_erp');
    define('BASE_URL', 'http://localhost/erp/'); // Set your absolute project root here
    ```

2.  **User Roles Bootstrap**:
    *   The system uses `dev` (Super-admin) and `admin` (Operational Admin) by default.
    *   Developer tools are only available to users with the `dev` role name.

---

## 🔒 Security Highlights
*   **CSRF Protection**: All form submissions are protected with session-based tokens.
*   **AES-256 Encryption**: Sensitive database IDs used in URLs are encrypted to prevent sequential scraping.
*   **XSS Mitigation**: Consistent output sanitization via the `xss_clean` helper.
*   **Hierarchical RBAC**: Secure `check_auth()` logic that separates developer tools from administrative tasks at the core level.

---

## 📈 Roadmap & Upcoming
- [ ] Drag-and-Drop Dashboard Widget Reordering
- [ ] Bulk PDF Batch Generator
- [ ] Multi-tenant Schema Support
- [ ] Automated Git-Sync (One-Click Update)

**Developed with ❤️ by the LogicERP Advanced Agentic Coding Team.**
