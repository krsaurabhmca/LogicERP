<?php
/**
 * Dynamic Report Viewer
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

$id_enc = $_GET['id'] ?? '';
$report_id = decrypt_id($id_enc);

if (!$report_id) {
    redirect('index.php', 'Invalid Report ID.', 'danger');
}

// 1. Fetch Report Metadata
$report = fetch_one("SELECT r.*, f.form_name FROM reports r JOIN forms f ON r.form_id = f.form_id WHERE r.report_id = ?", [$report_id]);
if (!$report) {
    redirect('index.php', 'Report not found.', 'danger');
}

$config = json_decode($report['config'], true);
$selected_field_ids = $config['fields'] ?? [];

if (empty($selected_field_ids)) {
    redirect('index.php', 'Report configuration is empty.', 'warning');
}

// 2. Fetch Field Labels for headers
$placeholders = implode(',', array_fill(0, count($selected_field_ids), '?'));
$field_meta = fetch_all("SELECT field_id, field_label, field_type FROM form_fields WHERE field_id IN ($placeholders) AND form_id = ?", array_merge($selected_field_ids, [$report['form_id']]));

$headers = [];
$field_id_to_meta = [];
foreach ($field_meta as $fm) {
    $headers[$fm['field_id']] = $fm['field_label'];
    $field_id_to_meta[$fm['field_id']] = $fm;
}

// 3. Fetch All Submissions for this module
$submissions = fetch_all("SELECT submission_id, created_at FROM form_submissions WHERE form_id = ? ORDER BY created_at DESC", [$report['form_id']]);

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php" class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small">Reports</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">View Report</li>
                </ol>
            </nav>
            <h2 class="fw-800 m-0"><?php echo $report['report_name']; ?></h2>
            <div class="text-muted text-sm mt-1">
                <span class="badge bg-light text-muted border px-3 rounded-pill me-2">Source: <?php echo $report['form_name']; ?></span>
                <span class="badge bg-light text-muted border px-3 rounded-pill"><?php echo count($submissions); ?> Total Records</span>
            </div>
        </div>
        <div class="d-flex gap-2">
             <button onclick="window.print()" class="btn btn-outline-primary rounded-pill px-4 shadow-sm btn-sm fw-800">
                <i class="bi bi-printer me-2"></i> Print
            </button>
            <a href="index.php" class="btn btn-light rounded-pill px-4 btn-sm fw-800 border">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Generated Report Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportTable">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-xs fw-800 text-muted text-uppercase" style="width: 80px;">#ID</th>
                        <?php foreach ($headers as $label): ?>
                            <th class="px-4 py-3 text-xs fw-800 text-muted text-uppercase"><?php echo $label; ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 text-xs fw-800 text-muted text-uppercase" style="width: 150px;">Created On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="<?php echo count($headers) + 2; ?>" class="text-center py-5 text-muted small italic">No data available for this report.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): 
                            $values = fetch_all("SELECT field_id, field_value FROM form_data WHERE submission_id = ? AND field_id IN ($placeholders)", array_merge([$sub['submission_id']], $selected_field_ids));
                            $row_map = [];
                            foreach($values as $v) $row_map[$v['field_id']] = $v['field_value'];
                        ?>
                        <tr>
                            <td class="px-4 py-3 text-xs fw-bold text-muted">#<?php echo $sub['submission_id']; ?></td>
                            <?php foreach ($headers as $fid => $label): 
                                $val = $row_map[$fid] ?? '-';
                                $meta = $field_id_to_meta[$fid];
                            ?>
                                <td class="px-4 py-3 text-sm fw-600">
                                    <?php if(($meta['field_type'] == 'camera' || $meta['field_type'] == 'file') && $val !== '-'): ?>
                                        <img src="<?php echo BASE_URL; ?>uploads/<?php echo $val; ?>" class="rounded shadow-sm" style="width: 32px; height: 32px; object-fit: cover; cursor: pointer;" onclick="window.open(this.src)">
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($val); ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="px-4 py-3 text-xs text-muted"><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(function() {
    $('#reportTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "dom": '<"d-flex justify-content-between align-items-center mb-3 p-4"Bf>rtip',
        "buttons": [
            {
                extend: 'colvis',
                className: 'btn btn-light btn-sm rounded-pill border px-3 shadow-none',
                text: '<i class="bi bi-layout-three-columns me-2"></i> Toggle Columns'
            },
            {
                extend: 'excel',
                className: 'btn btn-outline-success btn-sm rounded-pill border px-3 shadow-none ms-2',
                text: '<i class="bi bi-file-earmark-spreadsheet me-2"></i> Excel'
            }
        ]
    });
});
</script>

<style>
@media print {
    .sidebar, .navbar-custom, .btn, .breadcrumb { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #eee !important; }
}
</style>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
