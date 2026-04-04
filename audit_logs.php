<?php
/**
 * Audit Log Viewer
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/core/init.php';
// Restrict to admins
check_auth('admin');

include_once __DIR__ . '/includes/header.php';

// Fetch audit logs with user info
$logs = fetch_all("
    SELECT a.*, u.full_name as operator 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    ORDER BY a.created_at DESC 
    LIMIT 500
");
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Audit Logs</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4">System Activity Audit</h2>
            <p class="text-muted smaller mb-0">Track every database action and user login.</p>
        </div>
        <div class="col-md-3 text-md-end mt-3 mt-md-0">
            <button class="btn btn-white border rounded-pill px-4 shadow-sm btn-sm fw-bold">
                 <i class="bi bi-download me-2"></i> Export Logs
            </button>
        </div>
    </div>

    <!-- Audit Table (High Density) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 border-0 smaller text-muted">TIMESTAMP</th>
                        <th class="py-3 border-0 smaller text-muted">OPERATOR</th>
                        <th class="py-3 border-0 smaller text-muted">ACTION</th>
                        <th class="py-3 border-0 smaller text-muted">MODULE / TABLE</th>
                        <th class="px-4 py-3 border-0 smaller text-muted">DETAILS (OLD -> NEW)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No activity recorded yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark small"><?php echo date('d M Y', strtotime($log['created_at'])); ?></div>
                            <div class="smaller text-muted" style="font-size: 0.65rem;"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                        </td>
                        <td class="py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-pill px-2 py-1 small fw-medium">
                                    <i class="bi bi-person-circle me-1 opacity-50"></i> <?php echo $log['operator'] ?: 'System'; ?>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-<?php 
                                echo strpos(strtolower($log['action']), 'delete') !== false ? 'danger' : 
                                     (strpos(strtolower($log['action']), 'login') !== false ? 'success' : 'primary'); 
                                ?> bg-opacity-10 text-<?php 
                                echo strpos(strtolower($log['action']), 'delete') !== false ? 'danger' : 
                                     (strpos(strtolower($log['action']), 'login') !== false ? 'success' : 'primary'); 
                                ?> rounded-pill px-3 py-1 smaller fw-bold">
                                <?php echo strtoupper($log['action']); ?>
                            </span>
                        </td>
                        <td class="py-3">
                            <code class="small"><?php echo $log['table_name'] ?: 'N/A'; ?></code>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-muted smaller text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($log['new_value']); ?>">
                                <?php if($log['old_value']): ?>
                                    <span class="text-danger"><del><?php echo $log['old_value']; ?></del></span> <i class="bi bi-arrow-right"></i>
                                <?php endif; ?>
                                <span class="text-success fw-medium"><?php echo $log['new_value']; ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
include_once __DIR__ . '/includes/footer.php';
?>
