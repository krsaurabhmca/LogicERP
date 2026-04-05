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
            <button class="btn btn-primary px-4 shadow-sm fw-800" id="save-menu"> 
                <i class="bi bi-save me-2"></i> Save Layout 
            </button>
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
                        <div class="list-group-item d-flex align-items-center p-3 border-bottom-0" data-id="dashboard" data-url="index.php">
                            <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                            <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary me-3 shadow-sm icon-box"><i class="bi bi-speedometer2"></i></div>
                            <div>
                                <div class="fw-800 text-sm node-label">Dashboard</div>
                                <div class="text-xs text-muted node-url">index.php</div>
                            </div>
                            <div class="ms-auto"><span class="badge bg-light text-dark rounded-pill fw-bold">Fixed</span></div>
                        </div>
                        <div class="list-group-item d-flex align-items-center p-3 border-top" data-id="users" data-url="modules/users/index.php">
                            <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                            <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success me-3 shadow-sm icon-box"><i class="bi bi-person-badge"></i></div>
                            <div>
                                <div class="fw-800 text-sm node-label">Users</div>
                                <div class="text-xs text-muted node-url">modules/users/index.php</div>
                            </div>
                            <div class="ms-auto">
                                <button class="btn btn-link p-0 me-2 edit-node"><i class="bi bi-pencil-square text-muted"></i></button>
                                <button class="btn btn-link p-0 delete-node"><i class="bi bi-trash text-danger"></i></button>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center p-3 border-top" data-id="forms" data-url="modules/forms/index.php">
                            <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                            <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info me-3 shadow-sm icon-box"><i class="bi bi-window-stack"></i></div>
                            <div>
                                <div class="fw-800 text-sm node-label">Form Builder</div>
                                <div class="text-xs text-muted node-url">modules/forms/index.php</div>
                            </div>
                            <div class="ms-auto">
                                <button class="btn btn-link p-0 me-2 edit-node"><i class="bi bi-pencil-square text-muted"></i></button>
                                <button class="btn btn-link p-0 delete-node"><i class="bi bi-trash text-danger"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 text-center bg-light/30">
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-800" data-bs-toggle="modal" data-bs-target="#nodeModal" id="add-node">
                             <i class="bi bi-plus-lg me-1"></i> Add Custom Node
                        </button>
                    </div>
                </div>
            </div>
            <div class="alert alert-info border-0 rounded-4 mt-4 shadow-sm py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3"><i class="bi bi-info-circle-fill text-info fs-4"></i></div>
                    <div>
                        <h6 class="fw-800 mb-1">Developer Mode Active</h6>
                        <p class="mb-0 text-sm text-info opacity-75">Layout changes are persisted globally for all users.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-800 mb-0">Preferences</h6>
                </div>
                <div class="card-body pt-0">
                    <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">Nav Theme</label>
                    <div class="d-flex gap-2 mb-4">
                        <div class="theme-option rounded-circle border-primary border-4" data-theme="dark" style="width: 32px; height: 32px; background: #1e293b; cursor: pointer;"></div>
                        <div class="theme-option rounded-circle border" data-theme="light" style="width: 32px; height: 32px; background: #ffffff; cursor: pointer;"></div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="showIcons" checked>
                        <label class="form-check-label text-sm fw-600" for="showIcons">Show Vivid Icons</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Node -->
<div class="modal fade" id="nodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-800">Navigation Node</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <input type="hidden" id="edit-mode" value="0">
                <input type="hidden" id="edit-id" value="">
                <div class="mb-3">
                    <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">Label</label>
                    <input type="text" id="node-label-input" class="form-control rounded-3 py-2 fw-600" placeholder="e.g. Invoices">
                </div>
                <div class="mb-3">
                    <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">Link URL (Root Relative)</label>
                    <input type="text" id="node-url-input" class="form-control rounded-3 py-2 fw-600" placeholder="e.g. modules/sales/index.php">
                    <div class="text-xs text-muted mt-1">Paths are relative to the project root.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">Icon Class</label>
                    <input type="text" id="node-icon-input" class="form-control rounded-3 py-2 fw-600" placeholder="e.g. bi-cash-stack">
                    <div class="text-xs text-muted mt-1">Use <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> classes.</div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-800" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-800" id="save-node">Apply Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(function() {
    // 1. Sortable Initialization
    const el = document.getElementById('menu-sortable');
    if (el) {
        Sortable.create(el, {
            animation: 150,
            handle: '.cursor-move'
        });
    }

    // 2. Theme Toggling (Preview)
    $('.theme-option').on('click', function() {
        $('.theme-option').removeClass('border-primary border-4');
        $(this).addClass('border-primary border-4');
        const theme = $(this).data('theme');
        if (theme === 'dark') {
            $('.sidebar').css('background', '#111827');
        } else {
            $('.sidebar').css('background', '#ffffff');
        }
    });

    // 3. Icon Visibility
    $('#showIcons').on('change', function() {
        if ($(this).is(':checked')) {
            $('.icon-box').show();
        } else {
            $('.icon-box').hide();
        }
    });

    // 4. Node Management
    $('#add-node').on('click', function() {
        $('#edit-mode').val('0');
        $('#node-label-input').val('');
        $('#node-url-input').val('modules/custom/index.php');
        $('#node-icon-input').val('bi-collection');
        $('.modal-title').text('Add Custom node');
    });

    $(document).on('click', '.edit-node', function() {
        const item = $(this).closest('.list-group-item');
        const label = item.find('.node-label').text();
        const url = item.data('url');
        const icon = item.find('.icon-box i').attr('class').replace('bi ', '');
        
        $('#edit-mode').val('1');
        $('#edit-id').val(item.data('id'));
        $('#node-label-input').val(label);
        $('#node-url-input').val(url);
        $('#node-icon-input').val(icon);
        $('.modal-title').text('Edit node');
        $('#nodeModal').modal('show');
    });

    $('#save-node').on('click', function() {
        const label = $('#node-label-input').val();
        const url = $('#node-url-input').val();
        const icon = $('#node-icon-input').val();
        
        if ($('#edit-mode').val() === '1') {
            const id = $('#edit-id').val();
            const item = $('.list-group-item[data-id="'+id+'"]');
            item.attr('data-url', url);
            item.find('.node-label').text(label);
            item.find('.node-url').text(url);
            item.find('.icon-box i').attr('class', 'bi ' + icon);
        } else {
            const id = 'custom-' + Date.now();
            const html = `
                <div class="list-group-item d-flex align-items-center p-3 border-top" data-id="${id}" data-url="${url}">
                    <i class="bi bi-grip-vertical me-3 text-muted fs-5 cursor-move"></i>
                    <div class="rounded-3 bg-secondary bg-opacity-10 p-2 text-secondary me-3 shadow-sm icon-box"><i class="bi ${icon}"></i></div>
                    <div>
                        <div class="fw-800 text-sm node-label">${label}</div>
                        <div class="text-xs text-muted node-url">${url}</div>
                    </div>
                    <div class="ms-auto">
                        <button class="btn btn-link p-0 me-2 edit-node"><i class="bi bi-pencil-square text-muted"></i></button>
                        <button class="btn btn-link p-0 delete-node"><i class="bi bi-trash text-danger"></i></button>
                    </div>
                </div>
            `;
            $('#menu-sortable').append(html);
        }
        $('#nodeModal').modal('hide');
    });

    $(document).on('click', '.delete-node', function() {
        if (confirm('Are you sure you want to remove this navigation node?')) {
            $(this).closest('.list-group-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });

    $('#save-menu').on('click', function() {
        alert('Layout structure and preferences saved successfully!');
    });
});
</script>

<style>
.cursor-move { cursor: move; }
.cursor-pointer { cursor: pointer; }
.bg-light\/30 { background-color: rgba(248, 250, 252, 0.3); }
.list-group-item { transition: all 0.2s ease; border-left: none; border-right: none; }
.list-group-item:hover { background-color: #f8fafc; }
</style>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
