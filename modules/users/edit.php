<?php
/**
 * Edit User
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins
check_auth('admin');

$id_enc = $_GET['id'] ?? '';
$user_id_to_edit = decrypt_id($id_enc);

if (!$user_id_to_edit) {
    redirect('index.php', 'Invalid User ID.', 'danger');
}

// Fetch user details
$user = fetch_one("SELECT * FROM users WHERE user_id = ?", [$user_id_to_edit]);
if (!$user) {
    redirect('index.php', 'User not found.', 'danger');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = xss_clean($_POST['full_name'] ?? '');
    $email = xss_clean($_POST['email'] ?? '');
    $role_id = $_POST['role_id'] ?? '';
    $is_active = $_POST['is_active'] ?? 0;
    $password = $_POST['password'] ?? ''; 
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf($token)) {
        $error = "Security validation failed.";
    } elseif (empty($full_name) || empty($email) || empty($role_id)) {
        $error = "Name, email and role are required.";
    } else {
        // Update user
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            query("UPDATE users SET full_name = ?, email = ?, role_id = ?, is_active = ?, password = ? WHERE user_id = ?", 
                  [$full_name, $email, $role_id, $is_active, $hashed, $user_id_to_edit]);
        } else {
            query("UPDATE users SET full_name = ?, email = ?, role_id = ?, is_active = ? WHERE user_id = ?", 
                  [$full_name, $email, $role_id, $is_active, $user_id_to_edit]);
        }

        // Log action
        query("INSERT INTO audit_logs (user_id, action, table_name, new_value) VALUES (?, 'Updated user', 'users', ?)", 
              [$_SESSION['user_id'], "User: $full_name"]);

        redirect('index.php', "User <strong>$full_name</strong> updated successfully!");
    }
}

// Fetch roles for the dropdown
$roles = fetch_all("SELECT * FROM roles WHERE is_active = 1");

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small">Users</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Edit Profile</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4">Update Account: <span class="text-muted"><?php echo $user['full_name']; ?></span></h2>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-5">
        <form action="edit.php?id=<?php echo $id_enc; ?>" method="POST" class="row g-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">FULL NAME</label>
                <input type="text" name="full_name" class="form-control rounded-3" value="<?php echo $user['full_name']; ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                <input type="email" name="email" class="form-control rounded-3" value="<?php echo $user['email']; ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">NEW PASSWORD (LEAVE BLANK TO KEEP)</label>
                <input type="password" name="password" class="form-control rounded-3" placeholder="••••••••">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">ASSIGN ROLE</label>
                <select name="role_id" class="form-select rounded-3" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['role_id']; ?>" <?php echo $role['role_id'] == $user['role_id'] ? 'selected' : ''; ?>>
                            <?php echo $role['role_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                    <label class="form-check-label small fw-bold">Active Status</label>
                </div>
            </div>

            <div class="col-12 mt-5 border-top pt-4 text-end">
                <a href="index.php" class="btn btn-light rounded-pill px-4 text-muted me-2 fw-medium">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/header.php';
?>
