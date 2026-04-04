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
        <!-- Widget 1 -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary me-3">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Total Users</h6>
                            <div class="d-flex align-items-center">
                                <h4 class="fw-bold mb-0 me-2">1,280</h4>
                                <span class="text-success text-xs fw-bold"><i class="bi bi-arrow-up"></i> 12%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 2 -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success me-3">
                            <i class="bi bi-ui-checks fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Active Forms</h6>
                            <div class="d-flex align-items-center">
                                <h4 class="fw-bold mb-0 me-2">42</h4>
                                <span class="text-muted text-xs">5 Modules</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 3 -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-2 text-warning me-3">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Pending</h6>
                            <div class="d-flex align-items-center">
                                <h4 class="fw-bold mb-0 me-2">8</h4>
                                <span class="text-danger text-xs fw-bold">Urgent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 4 -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info me-3">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-xs mb-0 fw-600 text-uppercase">Audit Logs</h6>
                            <div class="d-flex align-items-center">
                                <h4 class="fw-bold mb-0 me-2">156</h4>
                                <span class="text-muted text-xs">Today</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <?php if ($_SESSION['user_role'] == 'admin'): ?>
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
