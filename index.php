<?php
/**
 * Dashboard Homepage
 * LogicERP Modular Framework
 */
require_once 'core/init.php';
include_once 'includes/header.php';
?>

<div class="container-fluid py-3">
    <!-- Welcome Header -->
    <div class="row align-items-center mb-3">
        <div class="col-md-8">
            <h2 class="fw-800 mb-0" style="letter-spacing: -0.5px;">Dashboard</h2>
            <p class="text-muted text-sm mb-0">Welcome back, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>! 👋</p>
        </div>
        <div class="col-md-4 text-md-end mt-2 mt-md-0">
            <button class="btn btn-primary shadow-sm px-3"> <i class="bi bi-file-earmark-bar-graph me-2"></i> Report </button>
            <button class="btn btn-outline-secondary bg-white shadow-sm px-3 ms-1"> <i class="bi bi-plus-lg me-2"></i> New </button>
        </div>
    </div>
    
    <div class="row g-3">
        <?php if ($user_role === 'dev'): 
            // Dev Metrics
            $total_users = fetch_one("SELECT COUNT(*) as c FROM users")['c'];
            $total_forms = fetch_one("SELECT COUNT(*) as c FROM forms")['c'];
            $total_entries = fetch_one("SELECT COUNT(*) as c FROM form_submissions WHERE deleted_at IS NULL")['c'];
            $today_logs = fetch_one("SELECT COUNT(*) as c FROM audit_logs WHERE DATE(created_at) = CURDATE()")['c'];
        ?>
            <!-- Dev Widget 1 -->
            <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary me-3">
                                <i class="bi bi-people-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">System Users</h6>
                                <h4 class="fw-bold mb-0"><?php echo number_format($total_users); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dev Widget 2 -->
            <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success me-3">
                                <i class="bi bi-ui-checks fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Total Forms</h6>
                                <h4 class="fw-bold mb-0"><?php echo number_format($total_forms); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dev Widget 3 -->
            <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-warning bg-opacity-10 p-2 text-warning me-3">
                                <i class="bi bi-database-fill-check fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Total Entries</h6>
                                <h4 class="fw-bold mb-0"><?php echo number_format($total_entries); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dev Widget 4 -->
            <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info me-3">
                                <i class="bi bi-journal-text fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Logs Today</h6>
                                <h4 class="fw-bold mb-0"><?php echo number_format($today_logs); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: 
            // Admin/Staff Metrics
            // Count modules this user can actually see
            $all_mods = fetch_all("SELECT allowed_roles FROM forms WHERE is_module = 1 AND is_active = 1");
            $mod_count = 0;
            foreach($all_mods as $m) {
                $allowed = json_decode($m['allowed_roles'] ?? '[]', true);
                if(empty($allowed) || in_array($user_role, $allowed)) $mod_count++;
            }
            $my_entries = fetch_one("SELECT COUNT(*) as c FROM form_submissions WHERE user_id = ? AND deleted_at IS NULL", [$_SESSION['user_id']])['c'];
        ?>
            <!-- Admin Widget 1 -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-primary bg-opacity-10 border border-primary border-opacity-10">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-primary p-2 text-white me-3 shadow-sm">
                                <i class="bi bi-grid-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-primary text-xs mb-0 fw-600 text-uppercase">My Modules</h6>
                                <h4 class="fw-bold mb-0"><?php echo $mod_count; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Admin Widget 2 -->
            <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success me-3">
                                <i class="bi bi-plus-circle-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">My Submissions</h6>
                                <h4 class="fw-bold mb-0"><?php echo number_format($my_entries); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Admin Widget 3 -->
            <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-warning bg-opacity-10 p-2 text-warning me-3">
                                <i class="bi bi-calendar-check fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Current Role</h6>
                                <h4 class="fw-bold mb-0 text-uppercase" style="font-size: 1.1rem;"><?php echo $user_role; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <!-- Admin Widget 4 -->
             <div class="col-xl-3 col-md-6">
                <div class="card widget-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center text-muted opacity-75">
                            <div class="rounded-3 bg-light p-2 me-3">
                                <i class="bi bi-info-circle fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-xs mb-0 fw-600 text-uppercase">Session</h6>
                                <div class="text-xs fw-bold">Active</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Dynamic Dashboard Widgets
    $user_role = $_SESSION['user_role'] ?? 'guest';
    $widgets = fetch_all("
        SELECT f.form_id, f.form_name, f.module_icon, f.allowed_roles,
        (SELECT COUNT(*) FROM form_submissions WHERE form_id = f.form_id AND deleted_at IS NULL) as submission_count
        FROM forms f WHERE f.show_on_dashboard = 1 AND f.is_active = 1
    ");
    
    $filtered_widgets = [];
    foreach ($widgets as $w) {
        $allowed = json_decode($w['allowed_roles'] ?? '[]', true);
        if (empty($allowed) || in_array($user_role, $allowed) || $user_role === 'dev') {
            $filtered_widgets[] = $w;
        }
    }

    if (!empty($filtered_widgets)):
    ?>
    <div class="row mt-4 mb-2">
        <div class="col-12">
            <h6 class="fw-800 text-muted text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">Module Insights</h6>
            <div class="row g-3">
                <?php foreach ($filtered_widgets as $w): ?>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100 position-relative overflow-hidden widget-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary me-2">
                                    <i class="bi <?php echo $w['module_icon'] ?: 'bi-collection'; ?> fs-6"></i>
                                </div>
                                <div class="fw-800 text-sm text-dark text-truncate" style="max-width: calc(100% - 40px);"><?php echo $w['form_name']; ?></div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between">
                                <div>
                                    <h3 class="fw-800 mb-0"><?php echo number_format($w['submission_count']); ?></h3>
                                    <div class="text-muted" style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase;">Total Entries</div>
                                </div>
                                <a href="modules/forms/view_data.php?id=<?php echo encrypt_id($w['form_id']); ?>" class="btn btn-xs btn-white border shadow-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-arrow-right text-primary text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Area: Dev Only -->
    <?php if ($user_role === 'dev'): ?>
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2 me-3"><i class="bi bi-cpu fs-5"></i></div>
                <h6 class="fw-800 mb-0" style="letter-spacing: -0.5px;">Developer Workspace</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-primary bg-opacity-10 border-primary border-opacity-10">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-800 text-primary mb-1">Create New Module</h6>
                                    <p class="text-xs text-primary text-opacity-75 mb-0">No coding required. Design fields and go live.</p>
                                </div>
                                <a href="modules/forms/create.php" class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-light/50">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-800 text-dark mb-1">Module Management</h6>
                                    <p class="text-xs text-muted mb-0">Toggle modules in sidebar and manage icons.</p>
                                </div>
                                <a href="modules/forms/index.php" class="btn btn-white border shadow-sm rounded-pill px-3 py-1 text-xs fw-600">
                                    Manage <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <h6 class="fw-bold mb-0">System Activity</h6>
                    <button class="btn btn-link btn-sm text-decoration-none p-0 text-xs">View All</button>
                </div>
                <div class="card-body p-4 text-center text-muted bg-light/30">
                    <i class="bi bi-graph-up-arrow fs-2 opacity-25"></i>
                    <p class="mt-2 mb-0 text-sm">Analytics engine initializing...</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header py-2">
                    <h6 class="fw-bold mb-0">Quick Actions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-0">
                        <a href="<?php echo BASE_URL; ?>modules/forms/index.php" class="list-group-item list-group-item-action border-0 px-3 py-2 d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2 me-3"><i class="bi bi-plus-square"></i></div>
                            <div>
                                <div class="fw-600 text-sm">Create New Form</div>
                                <div class="text-xs text-muted">No coding required</div>
                            </div>
                        </a>
                        <a href="<?php echo BASE_URL; ?>modules/users/add.php" class="list-group-item list-group-item-action border-0 px-3 py-2 d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success p-2 rounded-2 me-3"><i class="bi bi-person-plus"></i></div>
                            <div>
                                <div class="fw-600 text-sm">Add New User</div>
                                <div class="text-xs text-muted">Assign roles</div>
                            </div>
                        </a>
                        <a href="<?php echo BASE_URL; ?>modules/reports/index.php" class="list-group-item list-group-item-action border-0 px-3 py-2 d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-2 me-3"><i class="bi bi-file-earmark-medical"></i></div>
                            <div>
                                <div class="fw-600 text-sm">Built-in Reports</div>
                                <div class="text-xs text-muted">Excel / PDF</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include_once 'includes/footer.php';
?>
