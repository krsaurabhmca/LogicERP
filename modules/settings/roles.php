<?php
/**
 * Role & Permission Management
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins
check_auth('admin');

// Handle Role Creation (MUST BE BEFORE HEADER)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_role') {
    $role_name = trim(xss_clean($_POST['role_name'] ?? ''));
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf($csrf)) {
        redirect(BASE_URL . 'modules/settings/roles.php', 'CSRF Security failed.', 'danger');
    }

    if (empty($role_name)) {
        redirect(BASE_URL . 'modules/settings/roles.php', 'Role name cannot be empty.', 'warning');
    }

    // Role Name Protection: Prevent Admins from creating dev-equivalent roles
    if ($user_role === 'admin') {
        $restricted_keywords = ['dev', 'developer', 'admin', 'administrator', 'root', 'super', 'system'];
        $is_restricted = false;
        foreach ($restricted_keywords as $rk) {
            if (strpos(strtolower($role_name), $rk) !== false) {
                $is_restricted = true;
                break;
            }
        }
        if ($is_restricted) {
            redirect(BASE_URL . 'modules/settings/roles.php', 'Illegal role name: Reserved for system developers.', 'danger');
        }
    }

    // Check if role already exists
    $check = fetch_one("SELECT role_id FROM roles WHERE role_name = ?", [$role_name]);
    if ($check) {
        redirect(BASE_URL . 'modules/settings/roles.php', 'Role name already exists.', 'warning');
    }

    // Insert new role
    query("INSERT INTO roles (role_name) VALUES (?)", [$role_name]);
    $new_id = $conn->insert_id;

    // Log the action
    query("INSERT INTO audit_logs (user_id, action) VALUES (?, ?)", [$_SESSION['user_id'], "Created new role: $role_name"]);

    redirect(BASE_URL . 'modules/settings/roles.php', "Role '$role_name' created successfully!");
}

include_once __DIR__ . '/../../includes/header.php';

// Fetch roles with RBAC filtering
$sql_roles = "SELECT * FROM roles";
if ($user_role === 'admin') {
    $sql_roles .= " WHERE role_name NOT IN ('dev', 'admin')";
}
$sql_roles .= " ORDER BY role_name ASC";
$roles = fetch_all($sql_roles);

// Fetch counts for users per role (respecting filter)
$sql_counts = "SELECT role_id, COUNT(*) as count FROM users GROUP BY role_id";
$user_counts = fetch_all($sql_counts);
$counts_map = [];
foreach ($user_counts as $uc) $counts_map[$uc['role_id']] = $uc['count'];

?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Role Management</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4">System Roles</h2>
        </div>
        <div class="col-md-3 text-md-end mt-3 mt-md-0">
            <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-800" data-bs-toggle="modal" data-bs-target="#newRoleModal">
                <i class="bi bi-plus-lg me-2"></i> Create New Role
            </button>
        </div>
    </div>

    <!-- Roles Grid (Compact Cards) -->
    <div class="row">
        <?php foreach ($roles as $role): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="rounded-pill bg-primary bg-opacity-10 px-3 py-1 text-primary small fw-800">
                        ID: #<?php echo $role['role_id']; ?>
                    </div>
                </div>
                
                <h5 class="fw-800 mb-1"><?php echo $role['role_name']; ?></h5>
                <div class="text-xs text-muted mb-4"><?php echo $counts_map[$role['role_id']] ?? 0; ?> Active Users Assigned</div>
                
                <div class="d-flex mt-auto pt-3 border-top gap-2">
                    <a href="permissions.php?id=<?php echo encrypt_id($role['role_id']); ?>" class="btn btn-sm btn-light border py-2 rounded-pill flex-grow-1 fw-800 smaller" style="font-size: 0.7rem;">
                        <i class="bi bi-shield-check me-2 text-success"></i> Permissions
                    </a>
                    <button class="btn btn-sm btn-light border rounded-circle" title="Edit">
                        <i class="bi bi-pencil-fill small text-muted"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: New Role -->
<div class="modal fade" id="newRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="roles.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="action" value="create_role">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">Role Name</label>
                        <input type="text" name="role_name" class="form-control rounded-3 py-2 fw-600" placeholder="e.g. Sales Manager" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-800" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-800">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
