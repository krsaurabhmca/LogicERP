<?php
/**
 * Login Page
 * LogicERP Modular Framework
 */
require_once 'core/init.php';

// Redirect if already logged in
if ($is_logged_in) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = xss_clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf($token)) {
        $error = "Security validation failed. Please refresh and try again.";
    } elseif (empty($email) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        // Find user by email
        $user = fetch_one("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if ($user && password_verify($password, $user['password'])) {
            // Success! Start session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role_id'] = $user['role_id'];

            // Get role name for helper session
            $roleRes = fetch_one("SELECT role_name FROM roles WHERE role_id = ?", [$user['role_id']]);
            $_SESSION['user_role'] = strtolower($roleRes['role_name']);

            // Update last login
            query("UPDATE users SET last_login = NOW() WHERE user_id = ?", [$user['user_id']]);

            // Create Audit Log (Step 14)
            query("INSERT INTO audit_logs (user_id, action) VALUES (?, 'Login successful')", [$user['user_id']]);

            redirect('index.php', "Welcome back, " . $user['full_name'] . "!");
        } else {
            $error = "Invalid email or password.";
            // Future feature: login attempt limit (brute force protection)
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LogicERP Professional</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>

<body class="auth-wrapper">

    <div class="login-card animate-up">
        <div class="text-center mb-5">
            <div class="brand-logo-circle">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div class="brand-title">LogicERP</div>
            <div class="text-muted fw-800 text-uppercase"
                style="font-size: 0.7rem; letter-spacing: 0.15em; opacity: 0.6;">Enterprise Suite v2.0</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 rounded-4 px-3 py-2 small mb-4 shadow-sm" role="alert"
                style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); font-weight: 600;">
                <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php echo display_flash(); ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div class="input-group-auth">
                <label for="email">Account Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="name@company.com" required
                    autocomplete="email">
            </div>

            <div class="input-group-auth">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="mb-0" for="password">Password</label>
                    <a href="forgot-password.php" class="text-primary text-xs text-decoration-none fw-800">Forgot?</a>
                </div>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••"
                    required autocomplete="current-password">
            </div>

            <div class="mb-4 form-check py-1">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label text-xs text-muted fw-600" for="remember">Stay signed in for 30
                    days</label>
            </div>

            <button type="submit"
                class="btn btn-primary w-100 py-3 rounded-4 fw-800 shadow-lg text-uppercase tracking-wider">
                Access Workspace <i class="bi bi-arrow-right-short fs-4 align-middle ms-1"></i>
            </button>

            <div class="text-center mt-4 pt-3 border-top border-light">
                <span class="text-muted text-xs fw-600">Need a system account?</span>
                <a href="signup.php" class="text-primary text-xs text-decoration-none fw-800 ms-1">Contact Dev</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>