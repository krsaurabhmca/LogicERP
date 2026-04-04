<?php
/**
 * User Management Index
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins
check_auth('admin');

include_once __DIR__ . '/../../includes/header.php';

// Fetch users with RBAC filtering
$sql_users = "
    SELECT u.*, r.role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.role_id 
";

if ($user_role === 'admin') {
    $sql_users .= " WHERE r.role_name NOT IN ('dev', 'admin')";
}

$sql_users .= " ORDER BY u.created_at DESC";
$users = fetch_all($sql_users);
?>

<div class="container-fluid py-3">
    <div class="row align-items-center mb-3">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-600 text-primary">Users</li>
                </ol>
            </nav>
            <h3 class="fw-800 m-0" style="letter-spacing: -0.5px;">System Users</h3>
        </div>
        <div class="col-md-4 text-md-end mt-2 mt-md-0">
            <a href="add.php" class="btn btn-primary shadow-sm px-3">
                <i class="bi bi-person-plus me-2"></i> Add User
            </a>
        </div>
    </div>

    <!-- User Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-3 py-2 border-0">USER</th>
                        <th class="py-2 border-0">ROLE</th>
                        <th class="py-2 border-0 text-center">STATUS</th>
                        <th class="py-2 border-0">LAST LOGIN</th>
                        <th class="px-3 py-2 border-0 text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No users found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-3 py-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="fw-600 text-dark"><?php echo $user['full_name']; ?></div>
                                    <div class="text-xs text-muted"><?php echo $user['email']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2">
                            <span class="text-sm fw-500">
                                <?php echo $user['role_name'] ?: 'None'; ?>
                            </span>
                        </td>
                        <td class="py-2 text-center">
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 text-xs">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 text-xs">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <div class="text-xs text-muted"><?php echo $user['last_login'] ? date('M j, y H:i', strtotime($user['last_login'])) : 'Never'; ?></div>
                        </td>
                        <td class="px-3 py-2 text-end">
                            <a href="<?php echo BASE_URL; ?>modules/users/edit.php?id=<?php echo encrypt_id($user['user_id']); ?>" class="btn btn-sm btn-light border px-2 py-1" title="Edit">
                                <i class="bi bi-pencil text-muted"></i>
                            </a>
                            <button class="btn btn-sm btn-light border px-2 py-1" title="Delete">
                                <i class="bi bi-trash text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
