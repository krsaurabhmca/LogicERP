<?php
/**
 * Permission Assignment Matrix
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins
check_auth('admin');

$id_enc = $_GET['id'] ?? '';
$role_id = decrypt_id($id_enc);

if (!$role_id) {
    redirect('roles.php', 'Invalid Role ID.', 'danger');
}

// Fetch role details
$role = fetch_one("SELECT * FROM roles WHERE role_id = ?", [$role_id]);
if (!$role) {
    redirect('roles.php', 'Role not found.', 'danger');
}

// Mocking permission modules (in a real system, these would be in the 'permissions' table)
$modules = ['User Management', 'Form Builder', 'Report Builder', 'Settings', 'Audit Logs'];
$perms = ['View', 'Add', 'Edit', 'Delete'];

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="roles.php" class="text-decoration-none text-muted small">Settings</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Permissions</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4">Permissions: <span class="text-primary"><?php echo $role['role_name']; ?></span></h2>
        </div>
        <div class="col-4 text-end">
            <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold">
                <i class="bi bi-save me-2"></i> Save Changes
            </button>
        </div>
    </div>

    <!-- Permission Matrix (Compact & Responsive) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 border-0 small text-muted" style="width: 40%;">MODULE / FEATURE</th>
                        <?php foreach($perms as $p): ?>
                            <th class="py-3 border-0 small text-muted text-center"><?php echo strtoupper($p); ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 border-0 small text-muted text-center">FULL CONTROL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modules as $module): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark mb-0"><?php echo $module; ?></div>
                            <div class="smaller text-muted" style="font-size: 0.7rem;">Control access level for this module</div>
                        </td>
                        <?php foreach($perms as $p): ?>
                            <td class="py-3 text-center">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="checkbox" id="<?php echo str_replace(' ', '_', $module) . '_' . strtolower($p); ?>" <?php echo $role['role_id'] == 1 ? 'checked' : ''; ?>>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <td class="px-4 py-3 text-center">
                            <div class="form-switch m-0 d-inline-block">
                                <input class="form-check-input" type="checkbox" id="<?php echo str_replace(' ', '_', $module); ?>_all" <?php echo $role['role_id'] == 1 ? 'checked' : ''; ?>>
                            </div>
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
