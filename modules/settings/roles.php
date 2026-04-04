<?php
/**
 * Role & Permission Management
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins
check_auth('admin');

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
            <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold">
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
                    <div class="rounded-pill bg-primary bg-opacity-10 px-3 py-1 text-primary small fw-bold">
                        ID: #<?php echo $role['role_id']; ?>
                    </div>
                </div>
                
                <h5 class="fw-bold mb-1"><?php echo $role['role_name']; ?></h5>
                <div class="smaller text-muted mb-4"><?php echo $counts_map[$role['role_id']] ?? 0; ?> Active Users Assigned</div>
                
                <div class="d-flex mt-auto pt-3 border-top gap-2">
                    <a href="permissions.php?id=<?php echo encrypt_id($role['role_id']); ?>" class="btn btn-sm btn-light border py-2 rounded-pill flex-grow-1 fw-bold smaller" style="font-size: 0.75rem;">
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

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
