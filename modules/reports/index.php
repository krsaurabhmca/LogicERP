<?php
/**
 * Dynamic Report Viewer
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

$id_enc = $_GET['id'] ?? '';
$form_id = decrypt_id($id_enc);

if (!$form_id) {
    redirect('../forms/index.php', 'Invalid Form ID.', 'danger');
}

// Fetch form details
$form = fetch_one("SELECT * FROM forms WHERE form_id = ?", [$form_id]);
if (!$form) {
    redirect('../forms/index.php', 'Form not found.', 'danger');
}

// Fetch field headers for this form
$fields = fetch_all("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC", [$form_id]);

// Fetch submissions header
$submissions = fetch_all("
    SELECT s.*, u.full_name as submitter 
    FROM form_submissions s 
    LEFT JOIN users u ON s.user_id = u.user_id 
    WHERE s.form_id = ? 
    ORDER BY s.created_at DESC
", [$form_id]);

// Index form data by submission_id and field_id
$values = fetch_all("
    SELECT d.submission_id, d.field_id, d.field_value 
    FROM form_data d 
    JOIN form_submissions s ON d.submission_id = s.submission_id 
    WHERE s.form_id = ?
", [$form_id]);

$data_map = [];
foreach ($values as $v) {
    $data_map[$v['submission_id']][$v['field_id']] = $v['field_value'];
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="../forms/index.php" class="text-decoration-none text-muted small">Forms</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Reports</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4">Report Data: <span class="text-primary"><?php echo $form['form_name']; ?></span></h2>
        </div>
        <div class="col-4 text-end">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <button class="btn btn-white border btn-sm fw-bold px-3"> <i class="bi bi-file-earmark-excel text-success me-2"></i> EXCEL </button>
                <button class="btn btn-white border btn-sm fw-bold px-3"> <i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF </button>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold ms-2" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="bi bi-funnel me-2"></i> Filters
            </button>
        </div>
    </div>

    <!-- Filter Panel (Collapsible) -->
    <div class="collapse mb-4" id="filterPanel">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h6 class="fw-bold mb-3">Refine Data</h6>
            <form class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">DATE RANGE</label>
                    <input type="text" class="form-control form-control-sm rounded-3" placeholder="Select range...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">STATUS</label>
                    <select class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">KEYWORD SEARCH</label>
                    <input type="text" class="form-control form-control-sm rounded-3" placeholder="Search values...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 py-2">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table (High Density) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 border-0 smaller text-muted">TIMESTAMP</th>
                        <?php foreach($fields as $field): ?>
                            <th class="py-3 border-0 smaller text-muted"><?php echo strtoupper($field['field_label']); ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 border-0 smaller text-muted text-end">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr><td colspan="<?php echo count($fields) + 2; ?>" class="text-center py-5 text-muted opacity-50">No submissions found for this form.</td></tr>
                    <?php endif; ?>

                    <?php foreach($submissions as $sub): ?>
                    <tr>
                        <td class="px-4 py-3 small">
                            <div class="fw-bold text-dark"><?php echo date('d-M-y', strtotime($sub['created_at'])); ?></div>
                            <div class="smaller text-muted" style="font-size: 0.65rem;"><?php echo date('H:i:s', strtotime($sub['created_at'])); ?></div>
                        </td>
                        <?php foreach($fields as $field): ?>
                            <td class="py-3 small text-muted">
                                <?php echo htmlspecialchars($data_map[$sub['submission_id']][$field['field_id']] ?? '-'); ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="px-4 py-3 text-end">
                            <button class="btn btn-sm btn-light border rounded-pill px-2" title="View Detail"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
