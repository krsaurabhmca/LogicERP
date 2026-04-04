<?php
/**
 * Sidebar Component
 * LogicERP Modular Framework
 */
$current_script = $_SERVER['PHP_SELF'];
?>

<div class="sidebar d-none d-lg-block">
    <div class="p-4 mb-2 border-bottom border-light border-opacity-10">
       <div class="d-flex align-items-center">
           <div class="bg-primary rounded-3 text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 38px; height: 38px;">
               <i class="bi bi-grid-fill"></i>
           </div>
           <div>
               <div class="fw-800 text-white lh-1 mb-1" style="font-size: 1.2rem; letter-spacing: -0.5px;">Logic<span class="text-primary">ERP</span></div>
               <div class="sidebar-text opacity-50 fw-600" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em;">Suite v2.0</div>
           </div>
       </div>
    </div>

    <div class="sidebar-menu">
        <?php $is_dashboard = (strpos($current_script, '/index.php') !== false && strpos($current_script, '/modules/') === false); ?>
        <a href="<?php echo BASE_URL; ?>index.php" class="sidebar-link <?php echo $is_dashboard ? 'active' : ''; ?>">
            <i class="bi bi-columns-gap text-primary opacity-100"></i> Dashboard
        </a>

        <?php
        // Dynamic Workspace Modules with RBAC (MOVED TO TOP)
        $user_modules = fetch_all("SELECT form_id, form_name, module_icon, allowed_roles FROM forms WHERE is_module = 1 AND is_active = 1");
        
        $filtered_modules = [];
        foreach ($user_modules as $mod) {
            $allowed = json_decode($mod['allowed_roles'] ?? '[]', true);
            if (empty($allowed) || in_array($user_role, $allowed) || $user_role === 'dev') {
                $filtered_modules[] = $mod;
            }
        }

        if (!empty($filtered_modules)):
        ?>
        <div class="sidebar-label text-truncate">My Workspace</div>
        <?php foreach ($filtered_modules as $mod): 
            $mod_id_enc = encrypt_id($mod['form_id']);
            $is_active_mod = (strpos($_SERVER['REQUEST_URI'], $mod_id_enc) !== false && strpos($current_script, 'view_data.php') !== false);
            $colors = ['text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger', 'text-secondary'];
            $color_idx = $mod['form_id'] % count($colors);
        ?>
            <a href="<?php echo BASE_URL; ?>modules/forms/view_data.php?id=<?php echo $mod_id_enc; ?>" 
               class="sidebar-link <?php echo $is_active_mod ? 'active' : ''; ?>">
                <i class="bi <?php echo $mod['module_icon'] ?: 'bi-collection'; ?> <?php echo $is_active_mod ? '' : $colors[$color_idx]; ?> opacity-100"></i> <?php echo $mod['form_name']; ?>
            </a>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="sidebar-label">Analytics</div>
        <?php $is_reports = (strpos($current_script, '/modules/reports/') !== false); ?>
        <a href="<?php echo BASE_URL; ?>modules/reports/index.php" class="sidebar-link <?php echo $is_reports ? 'active' : ''; ?>">
            <i class="bi bi-graph-up text-danger opacity-100"></i> Reports
        </a>
        
        <?php if ($user_role === 'dev' || $user_role === 'admin'): ?>
        <div class="sidebar-label">Organization</div>
        <?php $is_users = (strpos($current_script, '/modules/users/') !== false); ?>
        <a href="<?php echo BASE_URL; ?>modules/users/index.php" class="sidebar-link <?php echo $is_users ? 'active' : ''; ?>">
            <i class="bi bi-person-badge text-success opacity-100"></i> Users Management
        </a>

        <div class="sidebar-label">System</div>
        <?php $is_roles = (strpos($current_script, 'roles.php') !== false); ?>
        <a href="<?php echo BASE_URL; ?>modules/settings/roles.php" class="sidebar-link <?php echo $is_roles ? 'active' : ''; ?>">
            <i class="bi bi-shield-check text-info opacity-100"></i> Roles & RBAC
        </a>

        <?php if ($user_role === 'dev'): ?>
        <div class="sidebar-label">Developer Tools</div>
        <?php $is_forms = (strpos($current_script, '/modules/forms/index.php') !== false); ?>
        <a href="<?php echo BASE_URL; ?>modules/forms/index.php" class="sidebar-link <?php echo $is_forms ? 'active' : ''; ?>">
            <i class="bi bi-window-stack text-primary opacity-100"></i> Form Builder
        </a>

        <?php $is_menu = (strpos($current_script, 'menu_designer.php') !== false); ?>
        <a href="<?php echo BASE_URL; ?>modules/settings/menu_designer.php" class="sidebar-link <?php echo $is_menu ? 'active' : ''; ?>">
            <i class="bi bi-palette text-warning opacity-100"></i> Menu Designer
        </a>

        <?php $is_audit = (strpos($current_script, 'audit_logs.php') !== false); ?>
        <a href="<?php echo BASE_URL; ?>audit_logs.php" class="sidebar-link <?php echo $is_audit ? 'active' : ''; ?>">
            <i class="bi bi-journal-text text-secondary opacity-100"></i> Audit Log
        </a>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Mobile Toggle (Bootstrap Offcanvas can be added later) -->
