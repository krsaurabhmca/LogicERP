<?php
/**
 * Reports Dashboard
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

include_once __DIR__ . '/../../includes/header.php';

// Fetch all saved analytical reports
$reports = fetch_all("SELECT r.*, f.form_name FROM reports r JOIN forms f ON r.form_id = f.form_id ORDER BY r.created_at DESC");

// Fetch all saved document templates
$templates = fetch_all("SELECT t.*, f.form_name FROM report_templates t JOIN forms f ON t.form_id = f.form_id ORDER BY t.created_at DESC");
?>

<div class="container-fluid py-3">
    <div class="row align-items-center mb-4">
        <div class="col-8">
            <h4 class="fw-800 m-0">Studio Dashboard</h4>
            <div class="text-xs text-muted mt-1 fw-700 opacity-50">MANAGE ANALYTICS & PRINT TEMPLATES</div>
        </div>
        <div class="col-4 text-md-end mt-2 mt-md-0">
            <div class="d-flex gap-2 justify-content-end">
                <a href="template_builder.php" class="btn btn-outline-primary border-2 px-3 rounded-pill fw-800 btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Document
                </a>
                <a href="designer.php" class="btn btn-primary px-3 rounded-pill fw-800 btn-sm shadow-sm">
                    <i class="bi bi-graph-up-arrow me-1"></i> New Analytics
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Data Analysis Section -->
        <div class="col-lg-12 mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                 <div class="p-2 bg-primary bg-opacity-10 rounded-3"><i class="bi bi-bar-chart-fill text-primary"></i></div>
                 <h6 class="fw-800 m-0">Analytical Reports</h6>
            </div>
            <div class="row g-3">
                <?php if (empty($reports)): ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 border border-dashed text-muted opacity-50 fw-700 text-sm">No analytical reports found.</div>
                <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                        <div class="col-xl-3 col-md-4">
                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 hover-lift bg-white">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-2 py-1 fw-800 text-xxs"><?php echo $r['form_name']; ?></span>
                                    <button class="btn btn-link link-danger p-0 opacity-25 hover-opacity-100" onclick="deleteReport('<?php echo encrypt_id($r['report_id']); ?>')"><i class="bi bi-x-circle-fill"></i></button>
                                </div>
                                <h6 class="fw-800 mb-1 text-truncate"><?php echo $r['report_name']; ?></h6>
                                <div class="text-xxs text-muted mb-3 opacity-50 fw-700 uppercase">UPDATED <?php echo strtoupper(date('d M Y', strtotime($r['updated_at']))); ?></div>
                                <div class="d-flex gap-2">
                                    <a href="view.php?id=<?php echo encrypt_id($r['report_id']); ?>" class="btn btn-light border-0 fw-800 text-xxs py-2 flex-grow-1">OPEN</a>
                                    <a href="designer.php?id=<?php echo encrypt_id($r['report_id']); ?>" class="btn btn-light border-0 fw-800 text-xxs py-2"><i class="bi bi-gear-fill"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Document Templates Section -->
        <div class="col-lg-12">
            <div class="d-flex align-items-center gap-2 mb-3">
                 <div class="p-2 bg-info bg-opacity-10 rounded-3"><i class="bi bi-file-earmark-pdf-fill text-info"></i></div>
                 <h6 class="fw-800 m-0">Print & PDF Templates</h6>
            </div>
            <div class="row g-3">
                <?php if (empty($templates)): ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 border border-dashed text-muted opacity-50 fw-700 text-sm">No Document templates created yet.</div>
                <?php else: ?>
                    <?php foreach ($templates as $t): ?>
                        <div class="col-xl-3 col-md-4">
                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 hover-lift bg-white border-bottom border-4 border-info">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-info bg-opacity-10 text-info border-0 rounded-pill px-2 py-1 fw-800 text-xxs"><?php echo $t['form_name']; ?></span>
                                    <button class="btn btn-link link-danger p-0 opacity-25 hover-opacity-100" onclick="deleteTemplate('<?php echo encrypt_id($t['template_id']); ?>')"><i class="bi bi-trash-fill"></i></button>
                                </div>
                                <h6 class="fw-800 mb-1 text-truncate"><?php echo $t['template_name']; ?></h6>
                                <div class="text-xxs text-muted mb-3 opacity-50 fw-700 uppercase">SYNCHRONIZED <?php echo strtoupper(date('d M Y', strtotime($t['created_at']))); ?></div>
                                <div class="d-flex gap-2">
                                    <a href="template_builder.php?id=<?php echo encrypt_id($t['template_id']); ?>" class="btn btn-info text-white border-0 fw-800 text-xxs py-2 flex-grow-1">STUDIO</a>
                                    <a href="template_builder.php?id=<?php echo encrypt_id($t['template_id']); ?>" class="btn btn-light border-0 fw-800 text-xxs py-2"><i class="bi bi-pencil-square"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function deleteReport(rid) {
            if (confirm('Delete analytical report?')) {
                $.post('ajax_delete_report.php', { id: rid }, function (res) {
                    if (res.status === 'success') location.reload();
                    else toastr.error(res.message);
                }, 'json');
            }
        }

        function deleteTemplate(tid) {
            if (confirm('Permanently delete print template?')) {
                $.post('ajax_delete_template.php', { id: tid }, function (res) {
                    if (res.status === 'success') location.reload();
                    else toastr.error(res.message);
                }, 'json');
            }
        }
    </script>

    <style>
        .hover-lift {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important;
        }
        .text-xxs { font-size: 0.65rem; }
        .text-xxs.uppercase { letter-spacing: 0.5px; }
    </style>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>