<?php
/**
 * Dynamic Module Data Viewer - Low Code Engine
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

$id_enc = $_GET['id'] ?? '';
$form_id = decrypt_id($id_enc);

if (!$form_id) redirect('index.php', 'Invalid Module ID.', 'danger');

$form = fetch_one("SELECT * FROM forms WHERE form_id = ?", [$form_id]);
if (!$form) redirect('index.php', 'Module not found.', 'danger');

// 1. Fetch ALL Fields
$user_role = $_SESSION['user_role'] ?? '';
$all_fields = fetch_all("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC", [$form_id]);

// 2. Filter Fields by RBAC and Table Visibility
$fields = [];
foreach ($all_fields as $f) {
    if ($f['show_in_table'] != 1) continue;
    
    $allowed = json_decode($f['allowed_roles'] ?? '[]', true);
    if ($user_role !== 'dev' && !empty($allowed) && !in_array($user_role, $allowed)) continue;
    
    $fields[] = $f;
}

// 3. For the Addition/Edit modal, we might want ALL visible fields (not just table ones)
$form_fields = [];
foreach ($all_fields as $f) {
    if ($f['is_visible'] != 1) continue;
    $allowed = json_decode($f['allowed_roles'] ?? '[]', true);
    if ($user_role !== 'dev' && !empty($allowed) && !in_array($user_role, $allowed)) continue;
    $form_fields[] = $f;
}

// 4. Fetch Submissions (Active Only)
$submissions = fetch_all("
    SELECT s.*, u.full_name as submitted_by 
    FROM form_submissions s
    LEFT JOIN users u ON s.user_id = u.user_id
    WHERE s.form_id = ? AND s.deleted_at IS NULL
    ORDER BY s.created_at DESC
", [$form_id]);

// 5. Data Mapping & Dynamic Resolution
$data_map = [];
$dynamic_resolutions = []; // Map [submission_id][field_id] -> label

if (!empty($submissions)) {
    $sub_ids = array_column($submissions, 'submission_id');
    $placeholders = implode(',', array_fill(0, count($sub_ids), '?'));
    $raw_data = fetch_all("SELECT submission_id, field_id, field_value FROM form_data WHERE submission_id IN ($placeholders)", $sub_ids);
    foreach ($raw_data as $row) { 
        $data_map[$row['submission_id']][$row['field_id']] = $row['field_value']; 
    }

    // Identify dynamic fields and collect IDs to resolve
    $dynamic_fields = array_filter($fields, function($f) { return !empty($f['dynamic_module']) && !empty($f['dynamic_label_column']); });
    
    if(!empty($dynamic_fields)) {
        $ids_to_resolve = [];
        foreach($dynamic_fields as $df) {
            foreach($submissions as $s) {
                $val = $data_map[$s['submission_id']][$df['field_id']] ?? null;
                if($val) $ids_to_resolve[$df['dynamic_label_column']][] = $val;
            }
        }

        // Batch fetch labels for all referenced IDs
        foreach($ids_to_resolve as $label_field_id => $referenced_ids) {
            $referenced_ids = array_unique($referenced_ids);
            if(empty($referenced_ids)) continue;
            
            $placeholders = implode(',', array_fill(0, count($referenced_ids), '?'));
            $labels = fetch_all("SELECT submission_id, field_value FROM form_data WHERE field_id = ? AND submission_id IN ($placeholders)", array_merge([$label_field_id], $referenced_ids));
            
            foreach($labels as $l) {
                $dynamic_resolutions[$l['submission_id']][$label_field_id] = $l['field_value'];
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<style>
    .dataTables_wrapper .dataTables_filter { display: none; } /* We use our own search */
    .table.dataTable thead th { border-bottom: none; }
</style>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row align-items-center mb-3">
        <div class="col-md-7">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-600 text-primary"><?php echo $form['form_name']; ?></li>
                </ol>
            </nav>
            <h3 class="fw-800 m-0" style="letter-spacing: -0.5px;">
                <i class="bi <?php echo $form['module_icon'] ?: 'bi-collection'; ?> me-2 text-primary"></i>
                <?php echo $form['form_name']; ?>
            </h3>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0 d-flex justify-content-md-end gap-2">
            <div class="input-group input-group-sm w-auto shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search entries..." style="width: 200px;">
            </div>
            <button class="btn btn-primary shadow-sm px-3" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-2"></i> New
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary bg-white shadow-sm px-3" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                    <li><a class="dropdown-item text-sm" href="export.php?id=<?php echo $id_enc; ?>&type=csv"><i class="bi bi-file-earmark-excel me-2"></i> Export Excel (CSV)</a></li>
                    <li><a class="dropdown-item text-sm" href="export.php?id=<?php echo $id_enc; ?>&type=print" target="_blank"><i class="bi bi-file-earmark-pdf me-2"></i> Export PDF (Print View)</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="moduleTable">
                <thead>
                    <tr>
                        <th class="px-3 py-2 border-0" style="width: 60px;">ID</th>
                        <?php foreach($fields as $field): ?>
                            <th class="py-2 border-0"><?php echo strtoupper($field['field_label']); ?></th>
                        <?php endforeach; ?>
                        <th class="px-3 py-2 border-0 text-end" style="width: 100px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td class="px-3 py-2 fw-600 text-muted small">#<?php echo $sub['submission_id']; ?></td>
                        <?php foreach($fields as $field): 
                            $val = $data_map[$sub['submission_id']][$field['field_id']] ?? '-';
                        ?>
                            <td class="py-2 text-sm text-truncate" style="max-width: 150px;">
                                <?php 
                                    if ($field['field_type'] == 'file' && !empty($val)) {
                                        echo '<a href="'.BASE_URL.'uploads/'.$val.'" target="_blank" class="badge bg-light text-primary border text-decoration-none"><i class="bi bi-file-earmark me-1"></i> View</a>';
                                    } elseif ($field['field_type'] == 'color' && !empty($val)) {
                                        echo '<div class="d-inline-block rounded-circle border shadow-sm" style="width: 16px; height: 16px; background: '.$val.'"></div> <span class="ms-1 smaller text-muted">'.strtoupper($val).'</span>';
                                    } elseif ($field['field_type'] == 'status') {
                                        $cls = 'bg-secondary';
                                        if($val == 'Active') $cls = 'bg-success';
                                        if($val == 'Inactive') $cls = 'bg-warning text-dark';
                                        if($val == 'Blocked') $cls = 'bg-danger';
                                        echo '<span class="badge '.$cls.' rounded-pill px-2" style="font-size: 0.65rem;">'.$val.'</span>';
                                    } elseif (!empty($field['dynamic_module']) && !empty($field['dynamic_label_column'])) {
                                        // Resolve Dynamic Label
                                        $label_field_id = $field['dynamic_label_column'];
                                        if (is_array($val) || (strpos($val ?? '', '[') === 0 && strpos($val ?? '', ']') !== false)) {
                                            $arr = is_array($val) ? $val : json_decode($val, true);
                                            $resolved_arr = [];
                                            foreach($arr as $item_id) {
                                                $resolved_arr[] = $dynamic_resolutions[$item_id][$label_field_id] ?? $item_id;
                                            }
                                            echo htmlspecialchars(implode(', ', $resolved_arr));
                                        } else {
                                            echo htmlspecialchars($dynamic_resolutions[$val][$label_field_id] ?? $val);
                                        }
                                    } elseif (is_array($val) || (strpos($val ?? '', '[') === 0 && strpos($val ?? '', ']') !== false)) {
                                        // Handle potential JSON or array from multi-select
                                        $arr = is_array($val) ? $val : json_decode($val, true);
                                        echo htmlspecialchars(is_array($arr) ? implode(', ', $arr) : $val);
                                    } else {
                                        echo htmlspecialchars($val); 
                                    }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="px-3 py-2 text-end">
                            <div class="btn-group bg-white border rounded shadow-sm overflow-hidden">
                                <button onclick="viewSub(<?php echo $sub['submission_id']; ?>)" class="btn btn-xs btn-white border-0 px-2 py-1"><i class="bi bi-eye text-primary text-xs"></i></button>
                                <button onclick="editSub(<?php echo $sub['submission_id']; ?>)" class="btn btn-xs btn-white border-0 px-2 py-1"><i class="bi bi-pencil text-muted text-xs"></i></button>
                                <button onclick="deleteSub(<?php echo $sub['submission_id']; ?>)" class="btn btn-xs btn-white border-0 px-2 py-1"><i class="bi bi-trash text-danger text-xs"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Entry Modal (Add/Edit) -->
<div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <form id="entryForm">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-800" id="entryModalTitle">New Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4" id="entryModalBody">
           <!-- Form generated dynamically -->
           <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
           <input type="hidden" name="submission_id" id="submission_id" value="">
           <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
           
           <div class="row g-3">
           <?php foreach ($form_fields as $field): ?>
                <?php if($field['field_type'] == 'section_heading'): ?>
                    <div class="col-12 mt-4 mb-2">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-info-circle me-1"></i> <?php echo strtoupper($field['field_label']); ?></h6>
                    </div>
                <?php else: ?>
                <div class="col-md-<?php echo $field['field_width'] ?? 12; ?> <?php echo !($field['is_visible'] ?? 1) ? 'd-none' : ''; ?>">
                    <label class="form-label text-xs fw-600 text-muted text-uppercase mb-1">
                        <?php echo $field['field_label']; ?> <?php echo ($field['is_required'] ?? 0) ? '<span class="text-danger">*</span>' : ''; ?>
                    </label>
                    <?php 
                        $def = $field['default_value'] ?? '';
                        if($def == '{{current_date}}') $def = date('Y-m-d');
                    ?>

                    <?php if (in_array($field['field_type'], ['select', 'multi_select', 'status'])): ?>
                        <select name="<?php echo $field['field_name']; ?><?php echo ($field['field_type'] == 'multi_select' ? '[]' : ''); ?>" 
                                id="field_<?php echo $field['field_id']; ?>" 
                                class="form-select form-select-sm rounded-2" 
                                <?php echo ($field['field_type'] == 'multi_select' ? 'multiple' : ''); ?>
                                <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                            <?php if ($field['field_type'] != 'multi_select'): ?>
                                <option value="">Select option...</option>
                            <?php endif; ?>
                            <?php 
                                $options = [];
                                if(!empty($field['dynamic_module']) && !empty($field['dynamic_label_column'])) {
                                    $mod_id = $field['dynamic_module'];
                                    $label_field_id = $field['dynamic_label_column'];
                                    $results = fetch_all("SELECT s.submission_id as id, d.field_value as val FROM form_submissions s JOIN form_data d ON s.submission_id = d.submission_id WHERE s.form_id = ? AND d.field_id = ?", [$mod_id, $label_field_id]);
                                    foreach($results as $row) $options[] = ['id' => $row['id'], 'val' => $row['val']];
                                } elseif(!empty(trim($field['dynamic_query'] ?? ''))) {
                                    $results = fetch_all($field['dynamic_query']);
                                    foreach($results as $row) $options[] = ['id' => $row['id'], 'val' => $row['val']];
                                } else {
                                    $opts = explode("\n", $field['field_options'] ?? '');
                                    foreach($opts as $opt) { 
                                        $opt = trim($opt); if($opt) $options[] = ['id' => $opt, 'val' => $opt]; 
                                    }
                                }

                                foreach($options as $opt) {
                                    $sel = ($opt['id'] == $def) ? 'selected' : '';
                                    echo '<option value="'.htmlspecialchars($opt['id']).'" '.$sel.'>'.htmlspecialchars($opt['val']).'</option>';
                                }
                            ?>
                        </select>
                    <?php elseif ($field['field_type'] == 'checkbox_group'): ?>
                        <div class="border rounded-2 p-2 bg-light">
                             <?php 
                                $opts = explode("\n", $field['field_options'] ?? '');
                                foreach($opts as $opt): $opt = trim($opt); if(!$opt) continue; ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="<?php echo $field['field_name']; ?>[]" value="<?php echo htmlspecialchars($opt); ?>" id="ch_<?php echo $field['field_id'].'_'.uniqid(); ?>">
                                    <label class="form-check-label text-xs" for="ch_<?php echo $field['field_id']; ?>"><?php echo htmlspecialchars($opt); ?></label>
                                </div>
                             <?php endforeach; ?>
                        </div>
                    <?php elseif ($field['field_type'] == 'textarea'): ?>
                        <textarea name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" class="form-control form-control-sm rounded-2" rows="3" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>><?php echo htmlspecialchars($def); ?></textarea>
                    <?php elseif ($field['field_type'] == 'color'): ?>
                        <input type="color" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" value="<?php echo htmlspecialchars($def ?: '#4f46e5'); ?>" class="form-control form-control-color w-100 rounded-2" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                    <?php elseif ($field['field_type'] == 'date' || $field['field_type'] == 'time' || $field['field_type'] == 'month'): ?>
                         <?php 
                            $today = date('Y-m-d');
                            $cur_min = $field['min_date'];
                            $cur_max = $field['max_date'];
                            
                            if (!($field['allow_past'] ?? 1)) {
                                if (!$cur_min || $today > $cur_min) $cur_min = $today;
                            }
                            if (!($field['allow_future'] ?? 1)) {
                                if (!$cur_max || $today < $cur_max) $cur_max = $today;
                            }
                         ?>
                         <input type="<?php echo $field['field_type']; ?>" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" 
                                value="<?php echo htmlspecialchars($def); ?>"
                                min="<?php echo $cur_min; ?>"
                                max="<?php echo $cur_max; ?>"
                                class="form-control form-control-sm rounded-2" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                    <?php elseif ($field['field_type'] == 'mobile' || $field['field_type'] == 'whatsapp'): ?>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-phone"></i></span>
                            <input type="tel" pattern="[0-9]{10}" placeholder="10 Digit Number" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" value="<?php echo htmlspecialchars($def); ?>" class="form-control rounded-end-2" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                        </div>
                    <?php elseif ($field['field_type'] == 'aadhar'): ?>
                         <input type="text" pattern="[0-9]{4} [0-9]{4} [0-9]{4}|[0-9]{12}" maxlength="14" placeholder="XXXX XXXX XXXX" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" value="<?php echo htmlspecialchars($def); ?>" class="form-control form-control-sm rounded-2" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                    <?php elseif ($field['field_type'] == 'camera'): ?>
                        <div class="d-flex gap-2">
                             <input type="file" accept="image/*" capture="environment" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" class="form-control form-control-sm">
                             <button type="button" class="btn btn-sm btn-light border"><i class="bi bi-camera"></i></button>
                        </div>
                    <?php elseif ($field['field_type'] == 'geolocation'): ?>
                        <div class="input-group input-group-sm">
                            <input type="text" readonly placeholder="Click to capture GPS" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" class="form-control rounded-2" value="<?php echo htmlspecialchars($def); ?>">
                            <button type="button" class="btn btn-light border"><i class="bi bi-geo-alt"></i></button>
                        </div>
                    <?php elseif ($field['field_type'] == 'serial'): ?>
                        <input type="text" readonly value="AUTO-GENERATED" class="form-control form-control-sm bg-light text-muted">
                    <?php else: ?>
                        <input type="<?php echo $field['field_type']; ?>" name="<?php echo $field['field_name']; ?>" id="field_<?php echo $field['field_id']; ?>" value="<?php echo htmlspecialchars($def); ?>" class="form-control form-control-sm rounded-2" <?php echo ($field['is_required'] ?? 0) ? 'required' : ''; ?>>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
           <?php endforeach; ?>
           </div>
        </div>
        <div class="modal-footer border-0 p-3">
          <button type="submit" class="btn btn-primary w-100 py-2 fw-600 shadow-sm" id="btnSaveEntry">Save Records</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Global UI Functions for Module View
window.openAddModal = null;
window.editSub = null;
window.viewSub = null;
window.deleteSub = null;

$(function() {
    // 1. Initialize DataTable
    var table = $('#moduleTable').DataTable({
        "order": [[0, "desc"]], // Default sort by ID
        "pageLength": 10,
        "dom": 'rtip', // Hide default search/length
        "language": {
            "emptyTable": '<div class="py-5 text-center"><div class="opacity-25 mb-2"><i class="bi bi-database-exclamation fs-1"></i></div><h6 class="text-muted text-sm">No data entries found yet.</h6><button onclick="openAddModal()" class="btn btn-sm btn-outline-primary mt-2">Add First Record</button></div>',
            "paginate": { "previous": "<", "next": ">" }
        },
        "columnDefs": [
            { "orderable": false, "targets": -1 } // Disable sort on ACTIONS column
        ]
    });

    // 2. Initialize Modal (using JS object for reliability)
    var modalEl = document.getElementById('entryModal');
    var bModal = new bootstrap.Modal(modalEl);

    // 3. SEARCH HANDLER (Hooked to DataTable)
    $("#searchInput").on("keyup", function() {
        table.search($(this).val()).draw();
    });

    // 4. DEFINE GLOBAL FUNCTIONS
    window.openAddModal = function() {
        $('#entryForm')[0].reset();
        $('#submission_id').val('');
        $('#entryModalTitle').text('New Entry');
        $('#btnSaveEntry').show().prop('disabled', false).text('Save Records');
        bModal.show();
    };

    window.editSub = function(subId) {
        $('#entryForm')[0].reset();
        $('#entryModalTitle').text('Edit Entry #' + subId);
        $('#btnSaveEntry').show().prop('disabled', false).text('Save Records');
        
        $.get('ajax_get_submission.php', {submission_id: subId}, function(res) {
            if(res.status === 'success') {
                $('#submission_id').val(subId);
                if(res.data) {
                    Object.keys(res.data).forEach(function(fieldId) {
                        var el = $('#field_' + fieldId);
                        if(el.length) el.val(res.data[fieldId]);
                    });
                }
                bModal.show();
            } else {
                alert(res.message || 'Error fetching data');
            }
        }, 'json').fail(function() {
            alert('Server error while loading data.');
        });
    };

    window.viewSub = function(subId) {
        window.editSub(subId);
        // We update the title and hide button after a small delay because editSub sets them
        setTimeout(function() {
            $('#entryModalTitle').text('View Entry #' + subId);
            $('#btnSaveEntry').hide();
        }, 100);
    };

    window.deleteSub = function(subId) {
        if(confirm('Are you sure you want to delete this entry? This action is permanent.')) {
            $.post('ajax_delete_submission.php', {submission_id: subId}, function(res) {
                if(res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                }
            }, 'json').fail(function() {
                alert('Server error while deleting.');
            });
        }
    };

    // 4. FORM SUBMISSION (SAVE/UPDATE)
    $('#entryForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btnSaveEntry');
        var oldText = btn.text();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');
        
        var formData = new FormData(this);

        $.ajax({
            url: 'ajax_save_submission.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                    btn.prop('disabled', false).text(oldText);
                }
            },
            error: function() {
                alert('Critical server error while saving.');
                btn.prop('disabled', false).text(oldText);
            }
        });
    });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
