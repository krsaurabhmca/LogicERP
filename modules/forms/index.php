<?php
/**
 * Form Management Index
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

include_once __DIR__ . '/../../includes/header.php';

$forms = fetch_all("
    SELECT f.*, u.full_name as creator_name,
    (SELECT COUNT(*) FROM form_fields WHERE form_id = f.form_id) as field_count,
    (SELECT COUNT(*) FROM form_submissions WHERE form_id = f.form_id) as submission_count
    FROM forms f
    LEFT JOIN users u ON f.created_by = u.user_id
    ORDER BY f.created_at DESC
");
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">Form Builder</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0">Dynamic Forms</h2>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="create.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New Form
            </a>
        </div>
    </div>

    <div class="row">
        <?php if (empty($forms)): ?>
            <div class="col-12 text-center py-5">
                <div class="bg-light rounded-4 p-5">
                    <i class="bi bi-ui-checks fs-1 text-muted opacity-25"></i>
                    <h4 class="mt-3 text-muted">No forms created yet.</h4>
                    <p class="text-muted">Start by creating your first dynamic form without coding.</p>
                    <a href="create.php" class="btn btn-primary rounded-pill px-4">Create Form</a>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($forms as $form): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi <?php echo $form['module_icon'] ?: 'bi-file-earmark-text'; ?> fs-5"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <?php if($form['is_module']): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 text-xs me-2">
                                <i class="bi bi-cpu shadow-sm"></i> MODULE
                            </span>
                        <?php endif; ?>
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical text-xs"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px;">
                                <li><a class="dropdown-item py-2 rounded text-sm" href="<?php echo BASE_URL; ?>modules/forms/builder.php?id=<?php echo encrypt_id($form['form_id']); ?>"><i class="bi bi-pencil me-2"></i> Design Fields</a></li>
                                <li><a class="dropdown-item py-2 rounded text-sm" href="javascript:void(0)" onclick="toggleModule(<?php echo $form['form_id']; ?>, <?php echo $form['is_module'] ? 0 : 1; ?>)">
                                    <i class="bi <?php echo $form['is_module'] ? 'bi-toggle-on text-primary' : 'bi-toggle-off'; ?> me-2"></i> <?php echo $form['is_module'] ? 'Unmark Module' : 'Mark as Module'; ?>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 rounded text-danger text-sm" href="<?php echo BASE_URL; ?>modules/forms/delete.php?id=<?php echo encrypt_id($form['form_id']); ?>"><i class="bi bi-trash me-2"></i> Delete Form</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <h6 class="fw-800 mb-1" style="letter-spacing: -0.5px;"><?php echo $form['form_name']; ?></h6>
                <p class="text-muted text-xs mb-3 line-clamp-2"><?php echo $form['form_description'] ?: 'No description provided.'; ?></p>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-2 text-center">
                            <div class="fw-bold text-dark text-sm"><?php echo $form['field_count']; ?></div>
                            <div class="text-muted fw-600" style="font-size: 0.65rem; text-transform: uppercase;">Fields</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-2 text-center">
                            <div class="fw-bold text-dark text-sm"><?php echo $form['submission_count']; ?></div>
                            <div class="text-muted fw-600" style="font-size: 0.65rem; text-transform: uppercase;">Entries</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between border-top pt-2 mt-auto">
                    <div class="text-muted fw-600" style="font-size: 0.65rem;">
                        BY <?php echo explode(' ', $form['creator_name'])[0]; ?>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="<?php echo BASE_URL; ?>modules/forms/view_data.php?id=<?php echo encrypt_id($form['form_id']); ?>" class="btn btn-xs btn-outline-primary py-1 px-2 text-xs fw-600">
                             DATA
                        </a>
                        <a href="<?php echo BASE_URL; ?>modules/forms/render.php?id=<?php echo encrypt_id($form['form_id']); ?>" class="btn btn-xs btn-primary py-1 px-2 text-xs fw-600" target="_blank">
                             FORM
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleModule(formId, status) {
    if(confirm('Toggle this form as a workspace module?')) {
        $.post('ajax_toggle_module.php', {form_id: formId, status: status}, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        }, 'json');
    }
}
</script>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
