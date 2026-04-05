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
    
    <!-- Third Party Features -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Summernote Lite (Open Source) -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <!-- Ace Editor (Syntax Highlighting) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/ace.js"></script>
    
    <!-- DataTables Buttons (ColVis & Export) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    
    <style>
        /* Base Shell Layout - Core spacing only, design in style.css */
        .main-content {
          margin-left: var(--sidebar-width); 
          padding: 2rem;
          min-height: calc(100vh - 70px);
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-custom {
            height: 70px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            padding: 0 2rem;
            margin-left: var(--sidebar-width);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .main-content,
        body.sidebar-collapsed .navbar-custom {
            margin-left: 0;
        }

        @media (max-width: 991px) {
            .main-content, .navbar-custom { margin-left: 0 !important; padding: 1.5rem !important; }
        }

        .user-pill {
            display: flex;
            align-items: center;
            background: #ffffff;
            padding: 6px 14px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .user-pill:hover { 
            background: #f8fafc;
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .user-pill .avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 800;
            margin-right: 12px;
            box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.2);
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
                <?php if ($_SESSION['user_role'] === 'dev' || $_SESSION['user_role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>modules/reports/template_builder.php" class="sidebar-link"><i class="bi bi-file-earmark-pdf"></i> Document Designer</a>
                <?php endif; ?>
                <div class="px-4 py-2 small fw-bold text-muted mt-3">SYSTEM</div>
                <a href="<?php echo BASE_URL; ?>modules/settings/roles.php" class="sidebar-link"><i class="bi bi-gear"></i> Settings</a>
                <a href="javascript:void(0)" onclick="handleLogout()" class="sidebar-link text-danger"><i class="bi bi-box-arrow-left"></i> Sign Out</a>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar-custom sticky-top">
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-lg-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <button class="btn btn-light border-0 d-none d-lg-flex me-3 rounded-3 p-2" id="sidebarToggle" type="button">
                <i class="bi bi-distribute-vertical text-primary"></i>
            </button>
            <h5 class="mb-0 fw-bold d-none d-sm-block" id="page-title">Dashboard Overview</h5>
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
                <li><a class="dropdown-item py-2 rounded text-danger" href="javascript:void(0)" onclick="handleLogout()"><i class="bi bi-box-arrow-left me-2"></i> Sign Out</a></li>
            </ul>
        </div>
    </nav>

    <script>
    function handleLogout() {
        if(confirm('Are you sure you want to sign out?')) {
            window.location.href = "<?php echo BASE_URL; ?>logout.php";
        }
    }
    </script>

    <!-- MAIN CONTENT START -->
    <div class="main-content">
        <?php echo display_flash(); ?>
