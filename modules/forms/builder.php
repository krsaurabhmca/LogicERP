<?php
/**
 * Dynamic Form Builder (Core Engine) - Enhanced
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth();

$id_enc = $_GET['id'] ?? '';
$form_id = decrypt_id($id_enc);

if (!$form_id) redirect('index.php', 'Invalid Form ID.', 'danger');

$form = fetch_one("SELECT * FROM forms WHERE form_id = ?", [$form_id]);
if (!$form) redirect('index.php', 'Form not found.', 'danger');

$fields = fetch_all("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC", [$form_id]);

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-5">
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small">Forms</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary small">Builder</li>
                </ol>
            </nav>
            <h2 class="fw-bold m-0 h4 text-dark"><?php echo $form['form_name']; ?></h2>
        </div>
        <div class="col-4 text-end">
            <a href="render.php?id=<?php echo $id_enc; ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold me-2">Preview</a>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold" id="btn-add-field">
                <i class="bi bi-plus-lg me-2"></i> Add Field
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 min-vh-50">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                    <h5 class="fw-bold mb-0 h6">Structure</h5>
                    <span class="badge bg-light text-muted fw-normal rounded-pill px-3"><?php echo count($fields); ?> Elements</span>
                </div>

                <div class="row g-3" id="field-structure">
                <?php foreach ($fields as $field): ?>
                    <div class="col-md-6 field-card" data-id="<?php echo $field['field_id']; ?>">
                        <div class="card border mb-2 <?php echo ($field['is_visible'] ?? 1) ? 'bg-light' : 'bg-secondary bg-opacity-10'; ?> rounded-4 p-3 h-100 position-relative draggable">
                            <div class="position-absolute top-0 end-0 p-2 d-flex">
                                <div class="drag-handle p-2 text-muted cursor-move" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></div>
                                <button class="btn btn-sm btn-white shadow-sm rounded-circle me-1 edit-btn" data-field='<?php echo htmlspecialchars(json_encode($field), ENT_QUOTES, 'UTF-8'); ?>'>
                                    <i class="bi bi-pencil small"></i>
                                </button>
                                <button class="btn btn-sm btn-white shadow-sm rounded-circle delete-field" data-id="<?php echo $field['field_id']; ?>">
                                    <i class="bi bi-trash small text-danger"></i>
                                </button>
                            </div>
                            
                            <label class="smaller fw-bold text-muted mb-1 text-uppercase">
                                <?php echo $field['field_type'] ?? 'text'; ?> <?php echo ($field['is_required'] ?? 0) ? '<span class="text-danger">*</span>' : ''; ?>
                                <?php if(!($field['is_visible'] ?? 1)) echo '<i class="bi bi-eye-slash ms-2"></i> HIDDEN'; ?>
                            </label>
                            <div class="fw-bold text-dark small mb-2"><?php echo $field['field_label'] ?? 'Untitled'; ?></div>
                            <div class="smaller text-muted">ID: <code><?php echo $field['field_name'] ?? 'none'; ?></code></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
             <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <h6 class="fw-bold mb-3 small">Field Logic Tips</h6>
                <ul class="smaller text-muted ps-3">
                    <li class="mb-2"><b>Auto-Generate:</b> Field names are automatically created from labels.</li>
                    <li class="mb-2"><b>Dropdowns:</b> Use 'Options' for static, or 'Dynamic Query' for SQL sources.</li>
                    <li><b>Visibilty:</b> Hidden fields can be used for system keys.</li>
                </ul>
             </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Field -->
<div class="modal fade" id="fieldModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <form id="fieldForm">
        <div class="modal-header border-0 bg-primary text-white p-4">
            <h5 class="modal-title fw-bold h6" id="modalTitle">Add Form Field</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
          <input type="hidden" name="field_id" id="field_id" value="">
          
          <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label smaller fw-bold text-muted">FIELD LABEL</label>
                <input type="text" name="field_label" id="field_label" class="form-control rounded-3" placeholder="e.g. Student Name" required>
            </div>
            <div class="col-md-6">
                <label class="form-label smaller fw-bold text-muted">FIELD NAME (AUTO)</label>
                <input type="text" name="field_name" id="field_name" class="form-control rounded-3 bg-light" placeholder="student_name" required readonly>
                <div class="smaller text-primary mt-1" style="font-size: 0.65rem;">Generated automatically for database consistency.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label smaller fw-bold text-muted">FIELD TYPE</label>
                <select name="field_type" id="field_type" class="form-select rounded-3">
                    <optgroup label="Standard Inputs">
                        <option value="text">Short Text</option>
                        <option value="number">Number (Basic)</option>
                        <option value="textarea">Long Text / Textarea</option>
                        <option value="email">Email Address</option>
                        <option value="url">Website URL / Link</option>
                    </optgroup>
                    <optgroup label="Selection">
                        <option value="select">Single Dropdown</option>
                        <option value="multi_select">Multiple Select</option>
                        <option value="checkbox_group">Checkbox Group</option>
                        <option value="status">Status List (Active/Inactive)</option>
                    </optgroup>
                    <optgroup label="Validations">
                        <option value="mobile">Mobile Number (10 Digits)</option>
                        <option value="whatsapp">WhatsApp Number</option>
                        <option value="aadhar">Aadhar Number (12 Digits)</option>
                        <option value="serial">Auto-Serial No.</option>
                    </optgroup>
                    <optgroup label="Advanced & Media">
                        <option value="date">Date Picker</option>
                        <option value="time">Time Picker</option>
                        <option value="month">Month Picker</option>
                        <option value="file">File Upload</option>
                        <option value="color">Color Picker</option>
                        <option value="section_heading">Section Divider / Heading</option>
                        <option value="camera">Capture Camera</option>
                        <option value="geolocation">Capture Location (GPS)</option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label smaller fw-bold text-muted">VISIBILITY</label>
                <select name="is_visible" id="is_visible" class="form-select rounded-3">
                    <option value="1">Show on Form</option>
                    <option value="0">Hide on Form</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label smaller fw-bold text-muted">WIDTH (COL)</label>
                <select name="field_width" id="field_width" class="form-select rounded-3">
                    <option value="12">Full (12)</option>
                    <option value="6">Half (6)</option>
                    <option value="4">1/3rd (4)</option>
                    <option value="3">1/4th (3)</option>
                </select>
            </div>

            <!-- Configuration Logic (Select/File/Date etc) -->
            <div class="col-12 d-none" id="dropdown-config">
                <div class="card bg-light border-0 p-3 rounded-4">
                    <h6 class="fw-bold smaller mb-3" id="config-title">Data Source Settings</h6>
                    <div class="row g-3">
                        <div class="col-md-6" id="opts-wrapper">
                            <label class="form-label smaller fw-bold text-muted" id="opts-label">STATIC OPTIONS</label>
                            <textarea name="field_options" id="field_options" class="form-control rounded-3" rows="3" placeholder="Option 1&#10;Option 2&#10;One per line"></textarea>
                        </div>
                        <div class="col-md-6" id="query-wrapper">
                            <label class="form-label smaller fw-bold text-muted">DYNAMIC QUERY (SQL) <span class="badge bg-secondary ms-1">Expert</span></label>
                            <textarea name="dynamic_query" id="dynamic_query" class="form-control rounded-3" rows="3" placeholder="SELECT id, val FROM table"></textarea>
                        </div>
                        <div class="col-md-12" id="dynamic-module-wrapper">
                            <hr class="my-2 opacity-10">
                            <label class="form-label smaller fw-bold text-primary mb-3">OR SELECT FROM MODULE (EASY)</label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="smaller fw-bold text-muted">MODULE NAME</label>
                                    <select name="dynamic_module" id="dynamic_module" class="form-select rounded-3">
                                        <option value="">-- Select Module --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="smaller fw-bold text-muted">VALUE COLUMN</label>
                                    <select name="dynamic_value_column" id="dynamic_value_column" class="form-select rounded-3">
                                        <option value="">-- Row ID --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="smaller fw-bold text-muted">LABEL COLUMN</label>
                                    <select name="dynamic_label_column" id="dynamic_label_column" class="form-select rounded-3">
                                        <option value="">-- Display Text --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 d-none" id="date-constraints">
                            <div class="row g-2">
                                <div class="col-12 d-flex gap-3 mb-2">
                                     <div class="form-check">
                                         <input class="form-check-input" type="checkbox" name="allow_past" id="allow_past" value="1" checked>
                                         <label class="form-check-label smaller fw-bold" for="allow_past">Allow Past Date</label>
                                     </div>
                                     <div class="form-check">
                                         <input class="form-check-input" type="checkbox" name="allow_future" id="allow_future" value="1" checked>
                                         <label class="form-check-label smaller fw-bold" for="allow_future">Allow Future Date</label>
                                     </div>
                                </div>
                                <div class="col-md-6 text-muted smaller">HARD LIMITS:</div>
                                <div class="col-md-6 text-muted smaller d-none d-md-block"></div>
                                <div class="col-md-6">
                                    <label class="form-label smaller fw-bold text-muted">MIN DATE (Optional)</label>
                                    <input type="date" name="min_date" id="min_date" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label smaller fw-bold text-muted">MAX DATE (Optional)</label>
                                    <input type="date" name="max_date" id="max_date" class="form-control rounded-3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label smaller fw-bold text-muted">DEFAULT VALUE</label>
                <input type="text" name="default_value" id="default_value" class="form-control rounded-3" placeholder="Leave empty for none">
                <div class="smaller text-primary mt-1" style="font-size: 0.6rem;">Use <code>{{current_date}}</code> for today's date.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label smaller fw-bold text-muted">ALLOWED ROLES (RBAC)</label>
                <select name="allowed_roles[]" id="allowed_roles" class="form-select rounded-3" multiple style="height: 100px;">
                    <option value="admin" selected>Admin</option>
                    <option value="staff">Staff</option>
                    <option value="manager">Manager</option>
                    <option value="user">User</option>
                </select>
                <div class="smaller text-muted mt-1" style="font-size: 0.6rem;">Ctrl+Click to select multiple. Leave empty for all.</div>
            </div>

            <div class="col-12 mt-4 d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_required" id="is_required_check" value="1">
                    <label class="form-check-label smaller fw-bold" for="is_required_check">Is Required?</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="show_in_table" id="show_in_table" value="1" checked>
                    <label class="form-check-label smaller fw-bold" for="show_in_table">Show in Table Column?</label>
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 p-4">
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Save Element</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
$(function() {
    const modal = new bootstrap.Modal('#fieldModal');

    // 1. Initialize SortableJS
    if(document.getElementById('field-structure')) {
        Sortable.create(document.getElementById('field-structure'), {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-primary-subtle',
            onEnd: function() {
                let order = [];
                $('#field-structure .field-card').each(function() {
                    order.push($(this).data('id'));
                });
                
                $.post('ajax_save_order.php', { order: order }, function(res) {
                    if(res.status !== 'success') alert('Order save failed.');
                }, 'json');
            }
        });
    }

    // 2. Toggle Config Sections
    $('#field_type').on('change', function() {
        const type = $(this).val();
        const selectionTypes = ['select', 'multi_select', 'checkbox_group', 'status'];
        
        if (selectionTypes.includes(type)) {
            $('#dropdown-config').removeClass('d-none');
            $('#query-wrapper').removeClass('d-none');
            $('#dynamic-module-wrapper').removeClass('d-none');
            $('#date-constraints').addClass('d-none');
            $('#config-title').text('Selection Data Source');
            $('#opts-label').text('STATIC OPTIONS');
            
            if (type === 'status') {
                $('#field_options').val('Active\nInactive\nBlocked');
                $('#query-wrapper').addClass('d-none'); 
                $('#dynamic-module-wrapper').addClass('d-none');
            }
        } else if (type === 'file') {
            $('#dropdown-config').removeClass('d-none');
            $('#query-wrapper').addClass('d-none');
            $('#date-constraints').addClass('d-none');
            $('#config-title').text('File Constraints');
            $('#opts-label').text('ALLOWED TYPES & SIZE (JSON)');
            $('#field_options').attr('placeholder', '{"types":["jpg","pdf"], "size":5}'); // 5MB example
        } else if (type === 'date') {
            $('#dropdown-config').removeClass('d-none');
            $('#query-wrapper').addClass('d-none');
            $('#opts-wrapper').addClass('d-none');
            $('#date-constraints').removeClass('d-none');
            $('#config-title').text('Date Constraints');
        } else {
            $('#dropdown-config').addClass('d-none');
        }

        // Logic for Section Heading specifically
        if (type === 'section_heading') {
            $('#show_in_table').prop('checked', false).prop('disabled', true);
            $('#is_required_check').prop('checked', false).prop('disabled', true);
        } else {
            $('#show_in_table').prop('disabled', false);
            $('#is_required_check').prop('disabled', false);
        }
    });

    // AUTO-GENERATE FIELD NAME
    $('#field_label').on('input', function() {
        if($('#field_id').val() === '') { // Only auto-gen for new fields
            let slug = $(this).val().toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_+|_+$/g, '');
            $('#field_name').val(slug);
        }
    });

    $('#dynamic_module').on('change', function() {
        const table = $(this).val();
        if(!table) return;
        
        const currentVal = $('#dynamic_value_column').data('selected') || '';
        const currentLabel = $('#dynamic_label_column').data('selected') || '';

        $.get('ajax_get_columns.php', {table: table}, function(cols) {
            let valHtml = '<option value="">-- Row ID --</option>';
            let labelHtml = '<option value="">-- Display Text --</option>';
            cols.forEach(c => {
                // Now using id/val format
                valHtml += `<option value="${c.id}" ${c.id == currentVal ? 'selected' : ''}>${c.val}</option>`;
                labelHtml += `<option value="${c.id}" ${c.id == currentLabel ? 'selected' : ''}>${c.val}</option>`;
            });
            $('#dynamic_value_column').html(valHtml);
            $('#dynamic_label_column').html(labelHtml);
        }, 'json').fail(function() {
            console.error('Failed to load columns for table:', table);
        });
    });

    // Fetch Tables for Dynamic Module
    function loadTables(selectedModule = '') {
        $.get('ajax_get_tables.php', function(tables) {
            let html = '<option value="">-- Select Module --</option>';
            tables.forEach(t => {
                // Now using id/val format
                html += `<option value="${t.id}" ${t.id == selectedModule ? 'selected' : ''}>${t.val}</option>`;
            });
            $('#dynamic_module').html(html);
            if(selectedModule) {
                $('#dynamic_module').trigger('change');
            } else {
                $('#dynamic_value_column').html('<option value="">-- Row ID --</option>');
                $('#dynamic_label_column').html('<option value="">-- Display Text --</option>');
            }
        }, 'json');
    }

    // ADD FIELD
    $('#btn-add-field').on('click', function() {
        $('#fieldForm')[0].reset();
        $('#field_id').val('');
        $('#modalTitle').text('Add Form Field');
        $('#dropdown-config').addClass('d-none');
        $('#opts-wrapper').removeClass('d-none'); 
        loadTables();
        modal.show();
    });

    // EDIT FIELD logic
    $('.edit-btn').on('click', function() {
        const data = $(this).data('field');
        $('#field_id').val(data.field_id);
        $('#field_label').val(data.field_label);
        $('#field_name').val(data.field_name);
        $('#field_type').val(data.field_type).trigger('change');
        $('#is_visible').val(data.is_visible);
        $('#field_width').val(data.field_width || 12);
        $('#field_options').val(data.field_options);
        $('#dynamic_query').val(data.dynamic_query);
        $('#default_value').val(data.default_value);
        $('#min_date').val(data.min_date);
        $('#max_date').val(data.max_date);
        $('#allow_future').prop('checked', data.allow_future == 1);
        $('#allow_past').prop('checked', data.allow_past == 1);
        $('#is_required_check').prop('checked', data.is_required == 1);
        $('#show_in_table').prop('checked', data.show_in_table == 1);
        
        // Populate Dynamic Module fields
        $('#dynamic_value_column').data('selected', data.dynamic_value_column);
        $('#dynamic_label_column').data('selected', data.dynamic_label_column);
        loadTables(data.dynamic_module);
        let roles = [];
        try { roles = JSON.parse(data.allowed_roles || '[]'); } catch(e) { roles = (data.allowed_roles || '').split(','); }
        $('#allowed_roles').val(roles);
        
        $('#modalTitle').text('Update Form Field');
        modal.show();
    });

    // SAVE FIELD (AJAX)
    $('#fieldForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const oldText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');

        $.post('ajax_save_field.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                location.reload();
            } else {
                alert(res.message);
                btn.prop('disabled', false).html(oldText);
            }
        }, 'json').fail(function() {
            alert('Server error occurred.');
            btn.prop('disabled', false).html(oldText);
        });
    });

     // DELETE FIELD
    $('.delete-field').on('click', function() {
        if(confirm('Delete element?')) {
            $.post('ajax_delete_field.php', {field_id: $(this).data('id')}, function(res) {
                if(res.status === 'success') location.reload();
            }, 'json');
        }
    });
});
</script>
