<?php
/**
 * Sidebar Component
 * LogicERP Modular Framework
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar d-none d-lg-block">
    <div class="p-3 mb-2 border-bottom">
       <div class="d-flex align-items-center px-1">
           <div class="bg-primary rounded-3 text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
               <i class="bi bi-grid-fill"></i>
           </div>
           <div>
               <div class="fw-800 text-primary lh-1" style="font-size: 1.1rem; letter-spacing: -0.5px;">LogicERP</div>
               <div class="text-muted" style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Enterprise Suite</div>
           </div>
       </div>
    </div>

    <div class="sidebar-menu">
        <a href="<?php echo BASE_URL; ?>index.php" class="sidebar-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="bi bi-columns-gap"></i> Dashboard
        </a>
        
        <div class="px-3 py-2 text-muted mt-2 fw-600" style="font-size: 0.65rem; text-transform: uppercase;">Core Modules</div>
        
        <a href="<?php echo BASE_URL; ?>modules/users/index.php" class="sidebar-link <?php echo strpos($current_page, 'user') !== false ? 'active' : ''; ?>">
            <i class="bi bi-person-badge"></i> Users
        </a>

        <a href="<?php echo BASE_URL; ?>modules/forms/index.php" class="sidebar-link <?php echo strpos($current_page, 'form') !== false ? 'active' : ''; ?>">
            <i class="bi bi-window-stack"></i> Form Builder
        </a>

        <a href="<?php echo BASE_URL; ?>modules/reports/index.php" class="sidebar-link <?php echo strpos($current_page, 'report') !== false ? 'active' : ''; ?>">
            <i class="bi bi-graph-up"></i> Reports
        </a>

        <?php
        // Dynamic Workspace Modules
        $user_modules = fetch_all("SELECT form_id, form_name, module_icon FROM forms WHERE is_module = 1 AND is_active = 1");
        if (!empty($user_modules)):
        ?>
        <div class="px-3 py-2 text-muted mt-2 fw-600" style="font-size: 0.65rem; text-transform: uppercase;">Workspace</div>
        <?php foreach ($user_modules as $mod): 
            $mod_id_enc = encrypt_id($mod['form_id']);
            $is_active_mod = (isset($_GET['id']) && $_GET['id'] == $mod_id_enc);
        ?>
            <a href="<?php echo BASE_URL; ?>modules/forms/view_data.php?id=<?php echo $mod_id_enc; ?>" 
               class="sidebar-link <?php echo $is_active_mod ? 'active' : ''; ?>">
                <i class="bi <?php echo $mod['module_icon'] ?: 'bi-collection'; ?>"></i> <?php echo $mod['form_name']; ?>
            </a>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="px-3 py-2 text-muted mt-2 fw-600" style="font-size: 0.65rem; text-transform: uppercase;">System</div>

        <a href="<?php echo BASE_URL; ?>modules/settings/roles.php" class="sidebar-link <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">
            <i class="bi bi-shield-check"></i> Roles
        </a>

        <a href="<?php echo BASE_URL; ?>audit_logs.php" class="sidebar-link <?php echo $current_page == 'audit_logs.php' ? 'active' : ''; ?>">
            <i class="bi bi-journal-text"></i> Audit Log
        </a>
    </div>

    <div class="sidebar-footer position-absolute bottom-0 w-100 p-3 border-top bg-light/50">
        <a href="<?php echo BASE_URL; ?>logout.php" class="sidebar-link text-danger m-0 p-2">
            <i class="bi bi-power"></i> <span class="fw-600">SIGN OUT</span>
        </a>
    </div>
</div>

<!-- Mobile Toggle (Bootstrap Offcanvas can be added later) -->
