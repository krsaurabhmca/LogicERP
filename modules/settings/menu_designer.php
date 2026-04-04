<?php
/**
 * Developer Menu Designer
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth('dev'); // Super-admin only

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php" class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Developer Tools</li>
                </ol>
            </nav>
            <h2 class="fw-800 m-0">Menu & Layout Designer</h2>
            <p class="text-muted text-sm mt-1">Configure global navigation hierarchies and custom module groupings.</p>
        </div>
        <div class="col-md-3 text-md-end">
            <button class="btn btn-primary px-4 shadow-sm fw-800" id="save-menu"> <i class="bi bi-save me-2"></i> Save Layout </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-light border-opacity-10">
                    <h6 class="fw-800 mb-0">Sidebar Structure (Live Preview)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="menu-sortable">
                        <div class="list-group-item d-flex align-items-center p-3 border-0 border-bottom" data-id="dashboard">
                            <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                            <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary me-3 shadow-sm"><i class="bi bi-speedometer2"></i></div>
                            <div>
                                <div class="fw-800 text-sm">Dashboard</div>
                                <div class="text-xs text-muted">Core / Summary</div>
                            </div>
                            <div class="ms-auto"><span class="badge bg-light text-dark rounded-pill fw-bold">Fixed</span></div>
                        </div>
                        <div class="list-group-item d-flex align-items-center p-3 border-0 border-bottom" data-id="users">
                            <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                            <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success me-3 shadow-sm"><i class="bi bi-person-badge"></i></div>
                            <div>
                                <div class="fw-800 text-sm">Users</div>
                                <div class="text-xs text-muted">Management</div>
                            </div>
                            <div class="ms-auto"><i class="bi bi-pencil-square text-muted me-2 cursor-pointer"></i><i class="bi bi-trash text-danger cursor-pointer"></i></div>
                        </div>
                        <div class="list-group-item d-flex align-items-center p-3 border-0 border-bottom" data-id="forms">
                            <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                            <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info me-3 shadow-sm"><i class="bi bi-window-stack"></i></div>
                            <div>
                                <div class="fw-800 text-sm">Form Builder</div>
                                <div class="text-xs text-muted">Low-Code Module</div>
                            </div>
                            <div class="ms-auto"><i class="bi bi-pencil-square text-muted me-2 cursor-pointer"></i><i class="bi bi-trash text-danger cursor-pointer"></i></div>
                        </div>
                    </div>
                    <div class="p-4 text-center bg-light/30">
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-800">
                             <i class="bi bi-plus-lg me-1"></i> Add Custom Navigation Node
                        </button>
                    </div>
                </div>
            </div>
            <div class="alert alert-info border-0 rounded-4 mt-4 shadow-sm py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3"><i class="bi bi-info-circle-fill text-info fs-4"></i></div>
                    <div>
                        <h6 class="fw-800 mb-1">Developer Mode Active</h6>
                        <p class="mb-0 text-sm text-info opacity-75">Changes to the menu structure are saved globally. Use caution when reordering core system modules to avoid user confusion.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-800 mb-0">Interface Preferences</h6>
                </div>
                <div class="card-body pt-0">
                    <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">Navigation Theme</label>
                    <div class="d-flex gap-2 mb-4">
                        <div class="rounded-circle border-primary border-4" style="width: 32px; height: 32px; background: #1e293b; cursor: pointer;" title="Dark Slate"></div>
                        <div class="rounded-circle border" style="width: 32px; height: 32px; background: #ffffff; cursor: pointer;" title="Light White"></div>
                        <div class="rounded-circle" style="width: 32px; height: 32px; background: #6366f1; cursor: pointer;" title="Primary Blue"></div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="showIcons" checked>
                        <label class="form-check-label text-sm fw-600" for="showIcons">Show Vivid Icons</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="compactMode" checked>
                        <label class="form-check-label text-sm fw-600" for="compactMode">High-Density Scaling</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(function() {
    const el = document.getElementById('menu-sortable');
    const sortable = Sortable.create(el, {
        animation: 150,
        handle: '.cursor-move',
        onEnd: function() {
            // Placeholder for save logic
            console.log('New order established');
        }
    });

    $('#save-menu').on('click', function() {
        alert('Menu structure saved successfully!');
    });
});
</script>

<style>
.cursor-move { cursor: move; }
.cursor-pointer { cursor: pointer; }
.bg-light\/30 { background-color: rgba(248, 250, 252, 0.3); }
</style>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
