<?php
/**
 * Add New User
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins
check_auth('admin');

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = xss_clean($_POST['full_name'] ?? '');
    $email = xss_clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf($token)) {
        $error = "Security validation failed. Please refresh and try again.";
    } elseif (empty($full_name) || empty($email) || empty($password) || empty($role_id)) {
        $error = "All fields are required.";
    } else {
        // Check if email already exists
        $existing = fetch_one("SELECT user_id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $error = "Email address already exists.";
        } else {
            // Success! Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = query("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)", 
                          [$full_name, $email, $hashed_password, $role_id]);
            
            if ($stmt) {
                // Log the action
                query("INSERT INTO audit_logs (user_id, action, table_name, new_value) VALUES (?, 'Created user', 'users', ?)", 
                      [$_SESSION['user_id'], "User: $full_name ($email)"]);

                redirect('index.php', "User <strong>$full_name</strong> created successfully!");
            } else {
                $error = "Failed to create user. Please try again.";
            }
        }
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
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Users</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">Add New</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 text-dark">Add New User</h2>
        </div>
    </div>

    <!-- Error/Success Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4 shadow-sm py-3 small rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-4">
        <form action="add.php" method="POST" class="row g-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="col-md-6 mb-3">
                <label for="full_name" class="form-label small fw-semibold text-muted">FULL NAME</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text" name="full_name" id="full_name" class="form-control border-start-0" 
                           placeholder="Enter full name" required style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label small fw-semibold text-muted">EMAIL ADDRESS</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" id="email" class="form-control border-start-0" 
                           placeholder="Enter email address" required style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="password" class="form-label small fw-semibold text-muted">PASSWORD</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="password" class="form-control border-start-0" 
                           placeholder="••••••••" required style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="mt-1 smaller text-muted" style="font-size: 0.75rem;">Minimum 8 characters with mixing numbers.</div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="role_id" class="form-label small fw-semibold text-muted">ASSIGN ROLE</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-shield-lock text-muted"></i>
                    </span>
                    <select name="role_id" id="role_id" class="form-control border-start-0" required style="border-radius: 0 12px 12px 0;">
                        <option value="">Select a role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['role_id']; ?>"><?php echo $role['role_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-12 mt-5 border-top pt-4 text-end">
                <a href="index.php" class="btn btn-light rounded-pill px-4 text-muted me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    Save User <i class="bi bi-check-lg ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
