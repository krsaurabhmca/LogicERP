<?php
/**
 * Header Component
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../core/init.php';
// Check session at every header except login/public
if (basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'signup.php') {
    check_auth();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | LogicERP</title>
    <!-- JS Libraries (Prioritized) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style>
        body { 
          background-color: var(--bg-color);
          background-image: none;
        }
        
        .main-content {
          margin-left: var(--sidebar-width); 
          padding: 24px;
          min-height: calc(100vh - var(--navbar-height));
          transition: all 0.3s ease;
        }

        @media (max-width: 991px) {
            .main-content { margin-left: 0; padding: 16px; }
        }

        .navbar-custom {
            height: var(--navbar-height);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid #f1f5f9;
            padding: 0 24px;
            margin-left: var(--sidebar-width);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        @media (max-width: 991px) {
            .navbar-custom { margin-left: 0; padding: 0 16px; }
        }

        .user-pill {
            display: flex;
            align-items: center;
            background: #f8fafc;
            padding: 4px 12px;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-pill:hover { 
            background: #f1f5f9;
            border-color: #e2e8f0;
        }

        .user-pill .avatar {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?php include_once 'sidebar.php'; ?>

    <!-- MOBILE SIDEBAR (OFFCANVAS) -->
    <div class="offcanvas offcanvas-start border-0 shadow" tabindex="-1" id="sidebarOffcanvas" style="width: 280px;">
        <div class="offcanvas-header border-bottom p-4">
            <h5 class="offcanvas-title fw-bold text-primary">LogicERP</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <!-- Reuse Sidebar Content (logic can be abstracted if needed) -->
            <?php 
               // For simplicity, we'll re-include the sidebar links here or use same CSS classes
               $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            <div class="sidebar-menu pt-3">
                <a href="<?php echo BASE_URL; ?>index.php" class="sidebar-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="<?php echo BASE_URL; ?>modules/users/index.php" class="sidebar-link <?php echo strpos($current_page, 'user') !== false ? 'active' : ''; ?>"><i class="bi bi-people"></i> User Management</a>
                <a href="<?php echo BASE_URL; ?>modules/forms/index.php" class="sidebar-link <?php echo strpos($current_page, 'form') !== false ? 'active' : ''; ?>"><i class="bi bi-ui-checks"></i> Form Builder</a>
                <a href="<?php echo BASE_URL; ?>modules/reports/index.php" class="sidebar-link <?php echo strpos($current_page, 'report') !== false ? 'active' : ''; ?>"><i class="bi bi-bar-chart"></i> Report Builder</a>
                <div class="px-4 py-2 small fw-bold text-muted mt-3">SYSTEM</div>
                <a href="<?php echo BASE_URL; ?>modules/settings/roles.php" class="sidebar-link"><i class="bi bi-gear"></i> Settings</a>
                <a href="<?php echo BASE_URL; ?>logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-left"></i> Sign Out</a>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar-custom sticky-top">
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-lg-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 fw-bold d-none d-sm-block">Dashboard Overview</h5>
        </div>
        
        <div class="user-pill dropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
            <div class="user-info">
                <div class="fw-bold small text-dark d-none d-md-block"><?php echo $_SESSION['user_name']; ?></div>
                <div class="text-muted smaller d-none d-md-block" style="font-size: 0.7rem;"><?php echo strtoupper($_SESSION['user_role']); ?></div>
            </div>
            <i class="bi bi-chevron-down ms-3 text-muted"></i>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 p-2" style="border-radius: 12px; width: 220px;">
                <li><a class="dropdown-item py-2 rounded" href="<?php echo BASE_URL; ?>profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                <li><a class="dropdown-item py-2 rounded" href="<?php echo BASE_URL; ?>modules/settings/roles.php"><i class="bi bi-gear me-2"></i> Account Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 rounded text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-left me-2"></i> Sign Out</a></li>
            </ul>
        </div>
    </nav>

    <!-- MAIN CONTENT START -->
    <div class="main-content">
        <?php echo display_flash(); ?>
