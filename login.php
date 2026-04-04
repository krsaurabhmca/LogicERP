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
<body>

    <div class="login-card animate-up glass">
        <div class="text-center mb-4">
            <div class="brand-title">LogicERP</div>
            <div class="text-muted" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">Enterprise Suite</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 border-0 rounded-3 small mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php echo display_flash(); ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="email" class="form-label text-xs fw-600 text-muted mb-1 text-uppercase">Email Address</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 py-2 px-3" style="border-radius: 10px 0 0 10px;">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" id="email" class="form-control border-start-0 py-2" 
                           placeholder="name@company.com" required style="border-radius: 0 10px 10px 0; font-size: 0.85rem;">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-xs fw-600 text-muted mb-1 text-uppercase">Password</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 py-2 px-3" style="border-radius: 10px 0 0 10px;">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="password" class="form-control border-start-0 py-2" 
                           placeholder="••••••••" required style="border-radius: 0 10px 10px 0; font-size: 0.85rem;">
                </div>
                <div class="text-end mt-2">
                    <a href="forgot-password.php" class="text-primary text-xs text-decoration-none fw-600">Forgot password?</a>
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label text-xs text-muted" for="remember">Keep me logged in</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mb-3 shadow-md fw-600">
                Sign In <i class="bi bi-arrow-right ms-2"></i>
            </button>

            <div class="text-center mt-4 pt-2 border-top">
                <span class="text-muted text-xs">New to LogicERP? </span>
                <a href="signup.php" class="text-primary text-xs text-decoration-none fw-800">Create Account</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
