<?php
/**
 * Advanced Report Designer
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth('admin'); // Only admins/devs can design reports

include_once __DIR__ . '/../../includes/header.php';

// Fetch available modules
$modules = fetch_all("SELECT form_id, form_name FROM forms WHERE is_active = 1");
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php" class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small">Reports</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Designer</li>
                </ol>
            </nav>
            <h2 class="fw-800 m-0">Advanced Report Designer</h2>
            <p class="text-muted text-sm mt-1">Build custom analytical views, aggregations, and data summaries.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary px-4 shadow-sm fw-800" id="btnSaveReport">
                <i class="bi bi-cloud-arrow-up me-2"></i> Save Report
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Configuration Sidebar -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">1. Base Module</label>
                        <select id="select-module" class="form-select rounded-3 py-2 fw-600">
                            <option value="">Select Data Source</option>
                            <?php foreach ($modules as $m): ?>
                                <option value="<?php echo $m['form_id']; ?>"><?php echo $m['form_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="designer-steps" class="d-none">
                        <div class="mb-4">
                            <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">2. Select Columns</label>
                            <div id="field-list" class="border rounded-3 p-3 bg-light overflow-auto" style="max-height: 250px;">
                                <!-- Dynamic Fields via AJAX -->
                                <p class="text-muted text-center py-4 small">Select a module first...</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">3. Aggregations</label>
                            <div class="d-flex flex-column gap-2" id="aggregators">
                                <button class="btn btn-outline-secondary btn-sm rounded-pill text-xs fw-bold py-2" id="add-sum">
                                    <i class="bi bi-plus-circle me-1"></i> Add Sum/Count Field
                                </button>
                                <div id="agg-list"></div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label text-xs fw-800 text-muted opacity-50 text-uppercase">4. Report Name</label>
                            <input type="text" id="report-name" class="form-control rounded-3 py-2 fw-600" placeholder="e.g. Monthly Revenue Summary">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Area -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 min-vh-50">
                <div class="card-header bg-white py-3 border-light border-opacity-10 d-flex justify-content-between align-items-center">
                    <h6 class="fw-800 mb-0">Live Preview</h6>
                    <span class="badge bg-light text-muted fw-bold rounded-pill border px-3">Auto-Sync Active</span>
                </div>
                <div class="card-body p-0" id="preview-area">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 text-muted opacity-50">
                        <i class="bi bi-kanban fs-1 mb-3"></i>
                        <p class="fw-600">Configure your report settings to see a data preview.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    let currentFields = [];

    $('#select-module').on('change', function() {
        const formId = $(this).val();
        if (!formId) {
            $('#designer-steps').addClass('d-none');
            return;
        }

        $('#designer-steps').removeClass('d-none');
        $('#field-list').html('<div class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span></div>');

        // Fetch Fields for this module
        $.get('ajax_get_fields.php', { form_id: formId }, function(res) {
            if (res.status === 'success') {
                currentFields = res.data;
                let html = '';
                res.data.forEach(f => {
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input field-toggle" type="checkbox" value="${f.field_id}" id="f-${f.field_id}" checked>
                            <label class="form-check-label text-sm fw-600" for="f-${f.field_id}">${f.field_label}</label>
                        </div>
                    `;
                });
                $('#field-list').html(html);
                updatePreview();
            }
        }, 'json');
    });

    $(document).on('change', '.field-toggle', function() {
        updatePreview();
    });

    function updatePreview() {
        const formId = $('#select-module').val();
        const selectedFields = $('.field-toggle:checked').map(function() { return $(this).val(); }).get();

        if (selectedFields.length === 0) {
            $('#preview-area').html('<div class="p-5 text-center text-muted">Select at least one column to preview.</div>');
            return;
        }

        $('#preview-area').html('<div class="p-5 text-center"><div class="spinner-border text-primary mb-3"></div><p>Calculating aggregates...</p></div>');

        // Fetch Preview Data
        $.post('ajax_preview_report.php', { 
            form_id: formId, 
            fields: selectedFields 
        }, function(res) {
            if (res.status === 'success') {
                renderPreviewTable(res.data, res.headers);
            }
        }, 'json');
    }

    function renderPreviewTable(data, headers) {
        let html = '<div class="table-responsive"><table class="table table-hover align-middle mb-0">';
        html += '<thead class="bg-light"><tr>';
        headers.forEach(h => {
             html += `<th class="px-4 py-3 text-xs fw-800 text-muted text-uppercase">${h}</th>`;
        });
        html += '</tr></thead><tbody>';
        
        data.forEach(row => {
            html += '<tr>';
            headers.forEach(h => {
                html += `<td class="px-4 py-3 text-sm fw-600">${row[h] || '-'}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#preview-area').html(html);
    }

    $('#btnSaveReport').on('click', function() {
        const name = $('#report-name').val();
        const formId = $('#select-module').val();
        const fields = $('.field-toggle:checked').map(function() { return $(this).val(); }).get();

        if (!name || !formId || fields.length === 0) {
            alert('Please fill all report settings before saving.');
            return;
        }

        $.post('ajax_save_report.php', {
            report_name: name,
            form_id: formId,
            config: JSON.stringify({ fields: fields })
        }, function(res) {
            if (res.status === 'success') {
                alert('Report saved successfully!');
                window.location.href = 'index.php';
            }
        }, 'json');
    });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
