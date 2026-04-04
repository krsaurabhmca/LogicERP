<?php
/**
 * Task & Activity Management
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

include_once __DIR__ . '/../../includes/header.php';

// Fetch tasks with assignee and creator info
$tasks = fetch_all("
    SELECT t.*, u_to.full_name as assignee, u_by.full_name as creator 
    FROM tasks t 
    LEFT JOIN users u_to ON t.assigned_to = u_to.user_id 
    LEFT JOIN users u_by ON t.created_by = u_by.user_id 
    ORDER BY t.due_date ASC
");
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-muted small">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Task Manager</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4 text-dark">Tasks & Activities</h2>
        </div>
        <div class="col-4 text-end">
            <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold">
                <i class="bi bi-plus-lg me-2"></i> Create Task
            </button>
        </div>
    </div>

    <!-- Task Dashboard Layout -->
    <div class="row">
        <!-- Stats Sidebar -->
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-4">Task Status</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 text-warning px-2 py-1 rounded small fw-bold me-3">12</div>
                    <div class="text-muted small fw-medium">Pending Tasks</div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary px-2 py-1 rounded small fw-bold me-3">5</div>
                    <div class="text-muted small fw-medium">In Progress</div>
                </div>
                <div class="d-flex align-items-center border-top pt-3 mt-3">
                    <div class="bg-success bg-opacity-10 text-success px-2 py-1 rounded small fw-bold me-3">128</div>
                    <div class="text-muted small fw-medium">Completed All-Time</div>
                </div>
            </div>
            
            <!-- Quick Filter -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3">Assigned To Me</h6>
                <p class="smaller text-muted mb-0">No urgent tasks assigned to you right now.</p>
            </div>
        </div>

        <!-- Task List Area -->
        <div class="col-xl-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-3 border-0 smaller text-muted" style="width: 50px;">#</th>
                                <th class="py-3 border-0 smaller text-muted">TASK / DESCRIPTION</th>
                                <th class="py-3 border-0 smaller text-muted">ASSIGNEE</th>
                                <th class="py-3 border-0 smaller text-muted">DUE DATE</th>
                                <th class="py-3 border-0 smaller text-muted">STATUS</th>
                                <th class="px-4 py-3 border-0 smaller text-muted text-end">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No active tasks found in the pipeline.</td></tr>
                            <?php endif; ?>

                            <?php foreach($tasks as $t): ?>
                            <tr>
                                <td class="px-4 py-3 text-muted smaller">#<?php echo $t['task_id']; ?></td>
                                <td class="py-3">
                                    <div class="fw-bold text-dark small"><?php echo $t['task_title']; ?></div>
                                    <div class="smaller text-muted line-clamp-1 italic" style="font-size: 0.7rem;"><?php echo $t['task_desc']; ?></div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark fw-medium rounded-pill px-3 py-1 smaller">
                                        <i class="bi bi-person me-1"></i> <?php echo $t['assignee'] ?: 'Unassigned'; ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="smaller fw-bold <?php echo strtotime($t['due_date']) < time() ? 'text-danger' : 'text-muted'; ?>">
                                        <?php echo date('d-M-y', strtotime($t['due_date'])); ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-<?php 
                                        echo $t['status'] == 'done' ? 'success' : 
                                             ($t['status'] == 'in-progress' ? 'primary' : 'warning'); 
                                    ?> bg-opacity-10 text-<?php echo $t['status'] == 'done' ? 'success' : 
                                             ($t['status'] == 'in-progress' ? 'primary' : 'warning'); ?> smaller fw-bold px-3 py-1 rounded-pill">
                                        <?php echo strtoupper($t['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-sm btn-light border py-1 rounded-pill px-3 fw-bold smaller">VIEW</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
