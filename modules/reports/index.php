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

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php"
                            class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Reports</li>
                </ol>
            </nav>
            <h2 class="fw-800 m-0">Reports Dashboard</h2>
            <p class="text-muted text-sm mt-1">Access and manage custom analytical views and system metrics.</p>
        </div>
        <div class="col-4 text-md-end mt-2 mt-md-0">
            <div class="d-flex gap-3 justify-content-end">
                <a href="template_builder.php"
                    class="btn btn-outline-primary border-primary border-opacity-50 shadow-sm px-4 rounded-pill fw-800 btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Document
                </a>
                <a href="designer.php" class="btn btn-primary shadow-sm px-4 rounded-pill fw-800 btn-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> New Report
                </a>
            </div>
        </div>
    </div>

    <!-- Data Analysis Section -->
    <div class="mb-5">
        <h5 class="fw-800 mb-4 d-flex align-items-center"><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Data
            Analysis Reports</h5>
        <div class="row g-4">
            <?php if (empty($reports)): ?>
                <div class="col-12 text-center py-4 bg-light rounded-4 border border-dashed text-muted">No analytical
                    reports saved yet.</div>
            <?php else: ?>
                <?php foreach ($reports as $r): ?>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative hover-lift">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="rounded-pill bg-primary bg-opacity-10 px-3 py-1 text-primary small fw-800">Source: <?php echo $r['form_name']; ?></div>
                                <button class="btn btn-link link-danger p-0 text-decoration-none" onclick="deleteReport('<?php echo encrypt_id($r['report_id']); ?>')" title="Delete Report"><i class="bi bi-trash"></i></button>
                            </div>
                            <h5 class="fw-800 mb-1"><?php echo $r['report_name']; ?></h5>
                            <div class="text-xs text-muted mb-4 opacity-50">Last Update: <?php echo date('d M Y', strtotime($r['updated_at'])); ?></div>
                            <div class="d-flex gap-2">
                                <a href="view.php?id=<?php echo encrypt_id($r['report_id']); ?>" class="btn btn-primary flex-grow-1 py-1 rounded-3 fw-800 small text-uppercase shadow-sm">View</a>
                                <a href="designer.php?id=<?php echo encrypt_id($r['report_id']); ?>" class="btn btn-outline-light border-light-subtle text-dark flex-grow-1 py-1 rounded-3 fw-800 small text-uppercase"><i class="bi bi-gear-fill me-1"></i> Edit</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Document Templates Section -->
    <div class="mb-5">
        <h5 class="fw-800 mb-4 d-flex align-items-center"><i class="bi bi-file-earmark-pdf me-2 text-info"></i> Document
            & Print Templates</h5>
        <div class="row g-4">
            <?php if (empty($templates)): ?>
                <div class="col-12 text-center py-4 bg-light rounded-4 border border-dashed text-muted">No document
                    templates saved yet. Click 'Document Designer' to start.</div>
            <?php else: ?>
                <?php foreach ($templates as $t): ?>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative hover-lift border-start border-info border-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="rounded-pill bg-info bg-opacity-10 px-3 py-1 text-info small fw-800">Module: <?php echo $t['form_name']; ?></div>
                                <button class="btn btn-link link-danger p-0 text-decoration-none opacity-25 hover-opacity-100" onclick="deleteTemplate('<?php echo encrypt_id($t['template_id']); ?>')" title="Delete Forever"><i class="bi bi-x-circle-fill"></i></button>
                            </div>
                            <h5 class="fw-800 mb-1"><?php echo $t['template_name']; ?></h5>
                            <div class="text-xs text-muted mb-4 italic">Design Active since <?php echo date('M Y', strtotime($t['created_at'])); ?></div>
                            <div class="d-flex gap-2">
                                <a href="template_builder.php?id=<?php echo encrypt_id($t['template_id']); ?>" class="btn btn-info text-white flex-grow-1 py-1 rounded-3 fw-800 small text-uppercase shadow-sm">Design</a>
                                <a href="template_builder.php?id=<?php echo encrypt_id($t['template_id']); ?>" class="btn btn-outline-light border-light-subtle text-dark flex-grow-1 py-1 rounded-3 fw-800 small text-uppercase"><i class="bi bi-pencil-square me-1"></i> Modify</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteReport(rid) {
            if (confirm('Are you sure you want to delete this analytical report?')) {
                $.post('ajax_delete_report.php', { id: rid }, function (res) {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                }, 'json');
            }
        }

        function deleteTemplate(tid) {
            if (confirm('Are you sure you want to PERMANENTLY delete this document template?')) {
                $.post('ajax_delete_template.php', { id: tid }, function (res) {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                }, 'json');
            }
        }
    </script>

    <style>
        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
            cursor: default;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .italic {
            font-style: italic;
            opacity: 0.6;
        }
    </style>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>