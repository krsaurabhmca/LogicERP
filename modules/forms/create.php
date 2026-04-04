<?php
/**
 * Create New Form
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
// Restrict to admins and staff
check_auth();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_name = xss_clean($_POST['form_name'] ?? '');
    $form_description = xss_clean($_POST['form_description'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf($token)) {
        $error = "Security validation failed. Please refresh and try again.";
    } elseif (empty($form_name)) {
        $error = "Form name is required.";
    } else {
        // Success! Create form
        $stmt = query("INSERT INTO forms (form_name, form_description, created_by) VALUES (?, ?, ?)", 
                      [$form_name, $form_description, $_SESSION['user_id']]);
        
        if ($stmt) {
            // Get last inserted ID
            global $conn;
            $form_id = $conn->insert_id;

            // Log the action
            query("INSERT INTO audit_logs (user_id, action, table_name, new_value) VALUES (?, 'Created form', 'forms', ?)", 
                  [$_SESSION['user_id'], "Form: $form_name"]);

            redirect("builder.php?id=" . encrypt_id($form_id), "Form <strong>$form_name</strong> created! Now add fields.");
        } else {
            $error = "Failed to create form. Please try again.";
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Forms</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">Create New Form</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 text-dark">Basic Form Setup</h2>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4 shadow-sm py-3 small rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-5">
        <form action="create.php" method="POST" class="row g-4 justify-content-center">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="col-md-10 mb-3">
                <label for="form_name" class="form-label small fw-semibold text-muted">FORM NAME <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-file-earmark-plus text-muted"></i>
                    </span>
                    <input type="text" name="form_name" id="form_name" class="form-control border-start-0 py-3" 
                           placeholder="e.g., Admission Form, NGO Survey, CRM Lead" required style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <div class="col-md-10 mb-3">
                <label for="form_description" class="form-label small fw-semibold text-muted">FORM DESCRIPTION</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-card-text text-muted"></i>
                    </span>
                    <textarea name="form_description" id="form_description" class="form-control border-start-0" 
                              placeholder="Briefly describe the purpose of this form" rows="4" style="border-radius: 0 12px 12px 0;"></textarea>
                </div>
            </div>

            <div class="col-md-10 mt-5 border-top pt-4 text-end">
                <a href="index.php" class="btn btn-light rounded-pill px-4 text-muted me-2">Go Back</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm fw-bold">
                    Continue to Builder <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
include_once __DIR__ . '/../../includes/footer.php';
?>
