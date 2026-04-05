<?php
/**
 * Professional LogicERP Studio - Advanced Document Designer (Wizard-Driven)
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
check_auth('admin');

include_once __DIR__ . '/../../includes/header.php';
$modules = fetch_all("SELECT form_id, form_name FROM forms WHERE is_active = 1");

// 1. Fetch Existing Template (If modifying)
$template_id_enc = $_GET['id'] ?? '';
$template_id = decrypt_id($template_id_enc);
$template = null;

if ($template_id) {
    $template = fetch_one("SELECT * FROM report_templates WHERE template_id = ?", [$template_id]);
}

$saved_name = $template['template_name'] ?? '';
$saved_form_id = $template['form_id'] ?? '';
$saved_html = $template['html_content'] ?? "<!-- Design Your Document Document Here -->\n<div class='document-body'>\n  <h1 style='color: #4f46e5;'>{{global.sys_date}}</h1>\n</div>";
$saved_css = $template['css_content'] ?? '';
$saved_connections = $template['data_connections'] ?? '[]';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=JetBrains+Mono:wght@400;700&display=swap');

:root {
    --studio-primary: #6366f1;
    --studio-accent: #f43f5e;
    --studio-bg: #f8fafc;
    --studio-card-bg: rgba(255, 255, 255, 0.95);
    --studio-border: #e2e8f0;
}

body { 
    background: var(--studio-bg) !important; 
    font-family: 'Outfit', sans-serif !important;
    height: 100vh;
    overflow: hidden;
}

/* Glassmorphism & Premium Layout */
.studio-wrapper { 
    display: flex; 
    height: calc(100vh - 60px); 
    width: 100vw; 
    position: relative;
    overflow: hidden;
}

/* Wizard Header */
.studio-header {
    height: 60px;
    background: #fff;
    border-bottom: 1px solid var(--studio-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 1000;
}

.wizard-steps {
    display: flex;
    gap: 25px;
}

.wiz-step {
    display: flex;
    align-items: center;
    gap: 10px;
    opacity: 0.3;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    cursor: pointer;
}

.wiz-step.active { opacity: 1; transform: scale(1.02); }
.wiz-step.completed { opacity: 0.7; color: var(--studio-primary); }

.step-bubble {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eee;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 800;
    color: #666;
    transition: all 0.3s;
}

.wiz-step.active .step-bubble {
    background: var(--studio-primary);
    color: white;
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
}

.wiz-step.completed .step-bubble {
    background: #e0e7ff;
    color: var(--studio-primary);
}

.step-text {
    display: flex;
    flex-direction: column;
}

.step-label { font-size: 0.55rem; font-weight: 800; text-uppercase: uppercase; letter-spacing: 0.5px; color: #64748b; }
.step-title { font-size: 0.8rem; font-weight: 700; color: #1e293b; }

/* Panes */
.wizard-pane {
    width: 100%;
    height: 100%;
    display: flex;
    overflow: hidden;
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Step 1 & 4 Content */
.centered-content {
    flex: 1;
    overflow-y: auto;
    padding: 60px 20px;
    background: radial-gradient(circle at top right, #f1f5f9, #f8fafc);
}

/* RELATIONSHIP CARDS */
.rel-card {
    background: #fff;
    border: 1px solid var(--studio-border);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s;
    position: relative;
    border-left: 5px solid var(--studio-primary);
}
.rel-card:hover {
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
    transform: translateY(-4px);
}

/* STEP 2: PAINTER LAYOUT */
.painter-sidebar {
    width: 280px;
    background: #fff;
    border-right: 1px solid var(--studio-border);
    display: flex;
    flex-direction: column;
}

.painter-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f1f5f9;
}

/* ACE EDITOR */
.editor-toolbar {
    height: 50px;
    background: #fff;
    border-bottom: 1px solid var(--studio-border);
    display: flex;
    align-items: center;
    padding: 0 20px;
    gap: 15px;
}

#html-ace { flex: 1; font-family: 'JetBrains Mono', monospace; font-size: 14px; }

/* TOKEN BUTTONS */
.token-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.token-item:hover {
    background: #f1f5f9;
    border-color: var(--studio-primary);
}
.token-item i { color: var(--studio-primary); font-size: 0.9rem; margin-top: 2px; }
.token-name { font-size: 0.7rem; font-weight: 800; color: #334155; line-height: 1.2; }
.token-meta { font-size: 0.6rem; color: #94a3b8; margin-top: 2px; font-family: 'JetBrains Mono', monospace; }

/* STEP 3: PREVIEW */
.preview-stage {
    flex: 1;
    background: #64748b;
    padding: 40px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.artboard-a4 {
    background: white;
    width: 210mm;
    min-height: 297mm;
    box-shadow: 0 25px 80px -15px rgba(0,0,0,0.5);
    padding: 20mm;
    box-sizing: border-box;
    border-radius: 4px;
}

/* DEPLOY HUB */
.deploy-card {
    background: #fff;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 0 auto;
    border: 1px solid #e2e8f0;
}

/* BUTTONS */
.btn-indigo {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.3s;
}
.btn-indigo:hover { transform: scale(1.02); box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4); color: #fff; }

/* MODALS */
.modal-content-glass {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 24px;
}
</style>

<!-- HEADER WITH WIZARD STEPS -->
<div class="studio-header">
    <div class="d-flex align-items-center gap-3">
        <a href="index.php" class="btn btn-light rounded-pill px-3 shadow-sm"><i class="bi bi-chevron-left me-1"></i> Studio</a>
        <div class="h-divider mx-2 opacity-25"></div>
        <div class="wizard-steps">
            <div class="wiz-step active" data-id="1">
                <div class="step-bubble">1</div>
                <div class="step-text">
                    <span class="step-label">STAGE 01</span>
                    <span class="step-title">Relations Map</span>
                </div>
            </div>
            <div class="wiz-step" data-id="2">
                <div class="step-bubble">2</div>
                <div class="step-text">
                    <span class="step-label">STAGE 02</span>
                    <span class="step-title">Studio Painter</span>
                </div>
            </div>
            <div class="wiz-step" data-id="3">
                <div class="step-bubble">3</div>
                <div class="step-text">
                    <span class="step-label">STAGE 03</span>
                    <span class="step-title">Quality Assurance</span>
                </div>
            </div>
            <div class="wiz-step" data-id="4">
                <div class="step-bubble">4</div>
                <div class="step-text">
                    <span class="step-label">FINALE</span>
                    <span class="step-title">Deployment Hub</span>
                </div>
            </div>
        </div>
    </div>

    <div>
        <span id="save-status" class="text-xs fw-700 text-muted me-3"></span>
        <button id="master-action" class="btn btn-indigo shadow-lg px-4">NEXT STEP <i class="bi bi-arrow-right-short ms-1"></i></button>
    </div>
</div>

<div class="studio-wrapper">
    
    <!-- STEP 1: RELATIONS MAP -->
    <div id="pane-1" class="wizard-pane">
        <div class="centered-content">
            <div class="container maxWidth-md" style="max-width: 900px;">
                <div class="mb-5 text-center">
                    <h2 class="fw-800 text-dark">Data Foundation</h2>
                    <p class="text-muted">Define the identity and map the data relationships for your document.</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                            <h6 class="fw-800 mb-4 opacity-50"><i class="bi bi-shield-check me-2"></i> IDENTITY</h6>
                            <div class="mb-4">
                                <label class="text-xs fw-800 text-muted text-uppercase d-block mb-1">Template Branding Name</label>
                                <input type="text" id="template-name" class="form-control form-control-lg bg-light border-0 rounded-4 fw-700" value="<?php echo $saved_name; ?>" placeholder="e.g. Executive Invoice Platinum">
                            </div>

                            <div class="mb-4">
                                <label class="text-xs fw-800 text-muted text-uppercase d-block mb-1">Primary Data Source (Root)</label>
                                <select id="prime-module" class="form-select form-select-lg bg-light border-0 rounded-4 fw-700">
                                    <option value="">Select Primary Module...</option>
                                    <?php foreach ($modules as $m): ?>
                                        <option value="<?php echo $m['form_id']; ?>" <?php echo ($m['form_id'] == $saved_form_id) ? 'selected' : ''; ?>>
                                            <?php echo $m['form_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <h6 class="fw-800 opacity-50"><i class="bi bi-diagram-3-fill me-2"></i> MAP RELATIONS</h6>
                                <button class="btn btn-sm btn-light rounded-pill border fw-700" onclick="openConnectionModal()">+ Add Relation</button>
                            </div>

                            <div id="connection-list-wizard" class="row g-3">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: STUDIO PAINTER -->
    <div id="pane-2" class="wizard-pane d-none">
        <div class="painter-sidebar shadow-sm">
            <div class="p-4 border-bottom">
                <h6 class="fw-800 m-0">Studio Tokens</h6>
                <p class="text-xxs text-muted m-0">Click tokens to bind them into your design.</p>
            </div>
            <div class="p-3">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-white border-0 shadow-sm rounded-start-3"><i class="bi bi-search"></i></span>
                    <input type="text" id="token-search" class="form-control border-0 shadow-sm rounded-end-3" placeholder="Filter tokens...">
                </div>
            </div>
            <div class="sidebar-content p-3 overflow-auto flex-1" id="token-list">
                <!-- Loaded by JS -->
            </div>
        </div>

        <div class="painter-main">
            <div class="editor-toolbar">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light border rounded-pill fw-700 px-3" onclick="loadPreset('invoice')"><i class="bi bi-file-earmark-text me-1"></i> Invoice</button>
                    <button class="btn btn-sm btn-light border rounded-pill fw-700 px-3" onclick="loadPreset('receipt')"><i class="bi bi-receipt me-1"></i> Receipt</button>
                    <button class="btn btn-sm btn-light border rounded-pill fw-700 px-3" onclick="loadPreset('clean')"><i class="bi bi-stars me-1"></i> Clean Canvas</button>
                </div>
                <div class="h-divider mx-2 opacity-25"></div>
                <div class="text-xs fw-800 opacity-50">EDITOR: PAINTER MODE (HTML5 / CSS3)</div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="text-xxs fw-800 text-muted"><i class="bi bi-keyboard text-primary me-1"></i> ACE ATOM READY</span>
                </div>
            </div>
            <div id="html-ace" class="w-100"></div>
        </div>
    </div>

    <!-- STEP 3: QUALITY ASSURANCE -->
    <div id="pane-3" class="wizard-pane d-none">
        <div class="painter-sidebar shadow-sm border-end">
            <div class="p-4 bg-light border-bottom">
                <h6 class="fw-800 m-0">QA Controls</h6>
                <p class="text-xxs text-muted m-0">Live data preview with latest records.</p>
            </div>
            <div class="p-4">
                <div class="card bg-indigo text-white border-0 rounded-4 p-4 mb-4 shadow-lg" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                    <h3 class="fw-800 mb-1">Live Mode</h3>
                    <p class="text-xs opacity-75">All tokens are now replaced with real database records for validation.</p>
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-sm fw-700">Root Module Tokens</span>
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </li>
                    <li class="list-group-item bg-transparent border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-sm fw-700">Relationship Tokens</span>
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </li>
                    <li class="list-group-item bg-transparent border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-sm fw-700">Dynamic UI Rendering</span>
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </li>
                </ul>
            </div>
        </div>
        <div class="preview-stage">
            <div id="live-content" class="artboard-a4">
                <!-- Live Preview Result -->
            </div>
        </div>
    </div>

    <!-- STEP 4: DEPLOYMENT HUB -->
    <div id="pane-4" class="wizard-pane d-none">
        <div class="centered-content d-flex align-items-center">
            <div class="deploy-card text-center">
                <div class="mb-5">
                    <div class="mb-4">
                        <i class="bi bi-rocket-takeoff-fill display-1 text-primary animate-bounce"></i>
                    </div>
                    <h2 class="fw-800">Ready for Launch?</h2>
                    <p class="text-muted mb-0">Your document template has been compiled and validated.</p>
                    <p class="text-muted fw-700" id="final-summary"></p>
                </div>

                <div class="p-4 bg-light rounded-4 mb-5 border text-start">
                    <h6 class="fw-800 text-xs mb-3 text-uppercase opacity-50">FINAL CHECKLIST</h6>
                    <div class="d-flex gap-3 mb-3">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <div>
                            <div class="text-sm fw-800">Template Logic Bound</div>
                            <p class="text-xxs text-muted m-0">All module relationships linked correctly.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <div>
                            <div class="text-sm fw-800">Performance Tuned</div>
                            <p class="text-xxs text-muted m-0">Optimized HTML markup and CSS structures.</p>
                        </div>
                    </div>
                </div>

                <button id="final-deploy" class="btn btn-indigo btn-lg w-100 rounded-pill py-3 fw-800 shadow-xl">DEPLOY DESIGN LIVE</button>
                <button class="btn btn-link text-muted fw-700 mt-3 text-decoration-none exit-studio" onclick="location.href='index.php'">Cancel & Discard</button>
            </div>
        </div>
    </div>

</div>

<!-- HIDDEN MODALS & UTILS -->
<div class="modal fade" id="relModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-glass shadow-xxl border-0">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h5 class="fw-800 m-0"><i class="bi bi-diagram-3-fill me-2 text-primary"></i> Link Relation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
          <div class="mb-4">
              <label class="text-xs fw-800 text-uppercase text-muted d-block mb-1">Child Table (Related Module)</label>
              <select id="rel-child" class="form-select border-0 bg-light rounded-3 fw-700">
                   <option value="">Select Module...</option>
                   <?php foreach ($modules as $m): ?>
                        <option value="<?php echo $m['form_id']; ?>"><?php echo $m['form_name']; ?></option>
                    <?php endforeach; ?>
              </select>
          </div>
          <div class="row g-3 mb-4">
               <div class="col-6">
                    <label class="text-xs fw-800 text-uppercase d-block mb-1">Primary key</label>
                    <select id="rel-pfield" class="form-select border-0 bg-light rounded-3 fw-700 text-xs">
                        <option value="submission_id">submission_id</option>
                    </select>
               </div>
               <div class="col-6">
                    <label class="text-xs fw-800 text-uppercase d-block mb-1">Foreign Key</label>
                    <div id="rel-child-fields-wrapper">
                         <select id="rel-cfield" class="form-select border-0 bg-light rounded-3 fw-700 text-xs" disabled><option>Select module...</option></select>
                    </div>
               </div>
          </div>
          <div class="p-3 bg-white bg-opacity-50 rounded-4 border border-dashed text-center">
              <i class="bi bi-shield-check text-indigo me-1"></i>
              <span class="text-xs fw-800">Enables dynamic loop <code class='text-indigo'>{{#collection}}</code></span>
          </div>
      </div>
      <div class="modal-footer border-0 p-4 pt-0">
          <button class="btn btn-indigo w-100 rounded-pill py-3 fw-800" onclick="addRelation()">Link Now</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" id="template-id" value="<?php echo $template_id_enc; ?>">

<!-- SCRIPTS & REQUIRED LIBS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
<script>
$(function() {
    // 0. Workspace Init
    if (!$('body').hasClass('sidebar-collapsed')) { $('#sidebarToggle').trigger('click'); }
    
    let currentStep = 1;
    window.dataRelations = [];
    window.sampleData = {};
    
    try {
        let dcRaw = `<?php echo addslashes($saved_connections); ?>`;
        window.dataRelations = JSON.parse(dcRaw || '[]');
    } catch(e) { window.dataRelations = []; }
    
    // 1. ACE Init
    const editor = ace.edit("html-ace");
    editor.setTheme("ace/theme/tomorrow_night_eighties");
    editor.session.setMode("ace/mode/html");
    editor.setValue(<?php echo json_encode($saved_html); ?>, -1);
    editor.setOptions({
        fontSize: "14px",
        showPrintMargin: false,
        wrap: true,
        useWorker: false
    });
    window.studioEditor = editor;

    refreshRelationUI();

    // 2. Wizard Navigation
    $('#master-action').on('click', function() {
        if(currentStep === 1) {
            if(!$('#template-name').val()) { toastr.error('Name your masterpiece first!'); return; }
            if(!$('#prime-module').val()) { toastr.error('Primary data source is mandatory.'); return; }
            gotoStep(2);
        } else if(currentStep === 2) {
            gotoStep(3);
        } else if(currentStep === 3) {
            gotoStep(4);
        }
    });

    $('.wiz-step').on('click', function() {
        const id = parseInt($(this).data('id'));
        if(id < currentStep) gotoStep(id);
    });

    function gotoStep(step) {
        currentStep = step;
        $('.wizard-pane').addClass('d-none');
        $(`#pane-${step}`).removeClass('d-none');
        
        $('.wiz-step').removeClass('active');
        $(`.wiz-step[data-id="${step}"]`).addClass('active');
        
        // Header Sync
        if(step === 4) {
             $('#master-action').addClass('d-none');
             $('#final-summary').text(`Template: ${$('#template-name').val()}`);
        } else {
             $('#master-action').removeClass('d-none');
             $('#master-action').html(`NEXT STEP <i class="bi bi-arrow-right-short ms-1"></i>`);
        }

        if(step === 2) {
            loadTokens();
            setTimeout(() => { editor.resize(); }, 100);
        }

        if(step === 3) {
            renderLivePreview();
        }
    }

    // 3. Relationships Module
    window.openConnectionModal = function() {
        if(!$('#prime-module').val()) { toastr.warning('Set primary module first'); return; }
        new bootstrap.Modal(document.getElementById('relModal')).show();
    };

    $('#rel-child').on('change', function() {
        const mid = $(this).val();
        if(!mid) return;
        $.get('ajax_get_fields.php', { form_id: mid }, function(res) {
            if(res.status === 'success') {
                let h = '<select id="rel-cfield" class="form-select border-0 bg-light rounded-3 fw-700 text-xs">';
                res.data.forEach(f => { h += `<option value="${f.field_id}">${f.field_label} (${f.field_name})</option>`; });
                h += '</select>';
                $('#rel-child-fields-wrapper').html(h);
            }
        });
    });

    window.addRelation = function() {
        const mid = $('#rel-child').val();
        const mhead = $('#rel-child option:selected');
        const mname = mhead.text().toLowerCase().replace(/[^a-z0-9]+/g, '_').trim();
        const pfield = $('#rel-pfield').val();
        const cfield = $('#rel-cfield').val();
        const clabel = $('#rel-cfield option:selected').text();

        if(!mid) return;
        window.dataRelations.push({ mid, mname, pfield, cfield, clabel });
        refreshRelationUI();
        bootstrap.Modal.getInstance(document.getElementById('relModal')).hide();
    };

    function refreshRelationUI() {
        let html = '';
        window.dataRelations.forEach((rel, idx) => {
            html += `
            <div class="col-12">
                <div class="rel-card shadow-sm h-100">
                    <button class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 m-2" onclick="removeRel(${idx})"><i class="bi bi-trash3-fill"></i></button>
                    <div class="d-flex align-items-center gap-3">
                         <div class="p-2 bg-light rounded-3"><i class="bi bi-diagram-3 fs-4 text-primary"></i></div>
                         <div>
                              <h6 class="fw-800 mb-1">${rel.mname.toUpperCase()} (LINKED)</h6>
                              <div class="text-xs opacity-75">Connects via <span class="fw-800">${rel.pfield}</span> to <span class="fw-800">${rel.clabel}</span></div>
                         </div>
                    </div>
                </div>
            </div>`;
        });
        $('#connection-list-wizard').html(html || '<div class="text-center py-5 opacity-25 w-100"><i class="bi bi-link-45deg fs-1"></i><p class="fw-700 m-0">No active relations mapped.</p></div>');
    }

    window.removeRel = function(i) {
        window.dataRelations.splice(i, 1);
        refreshRelationUI();
    };

    // 4. Token & Studio Logic
    async function loadTokens() {
        const mid = $('#prime-module').val();
        const mname = $('#prime-module option:selected').text().toLowerCase().trim().replace(/[^a-z0-9]+/g, '_');
        
        $('#token-list').html('<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-indigo"></div></div>');

        let sections = [];

        // Section 1: System Tokens
        let systemHtml = '<div class="mb-4"><h6 class="text-xxs fw-800 opacity-50 px-2 mb-2">SYSTEM TOKENS</h6>';
        systemHtml += tokenRow('{{global.sys_date}}', 'Current Date', 'bi-clock');
        systemHtml += tokenRow('{{global.submission_id}}', 'Document ID', 'bi-hash');
        systemHtml += '</div>';
        sections.push(systemHtml);

        // Section 2: Primary Module
        try {
            const res = await $.get('ajax_get_fields.php', { form_id: mid });
            if(res.status === 'success') {
                let primaryHtml = `<div class="mb-4"><h6 class="text-xxs fw-800 opacity-50 px-2 mb-2">${mname.toUpperCase()} DATA</h6>`;
                res.data.forEach(f => {
                    const cleanName = (f.field_name || f.field_label).toLowerCase().replace(/[^a-z0-9]+/g, '_');
                    primaryHtml += tokenRow(`{{${mname}.${cleanName}}}`, f.field_label, 'bi-input-cursor-text');
                });
                primaryHtml += '</div>';
                sections.push(primaryHtml);
            }
        } catch(e) { console.error("Failed to load primary tokens", e); }

        // Section 3: Collections / Loops
        if(window.dataRelations.length > 0) {
            let loopHtml = '<div class="mb-4"><h6 class="text-xxs fw-800 opacity-50 px-2 mb-2 text-indigo">COLLECTIONS (LOOPS)</h6>';
            window.dataRelations.forEach(rel => {
                const start = `{{#${rel.mname}}}`;
                const end = `{{/${rel.mname}}}`;
                loopHtml += `
                <div class="token-item border-indigo border-opacity-25 bg-indigo bg-opacity-5" onclick="inject('${start}\\n  [DATA HERE]\\n${end}')">
                    <i class="bi bi-arrow-repeat text-indigo"></i>
                    <div class="token-text">
                        <div class="token-name text-indigo">${rel.mname.toUpperCase()} LOOP</div>
                        <div class="token-meta">Wrap collection fields inside this</div>
                    </div>
                </div>`;
            });
            loopHtml += '</div>';
            sections.push(loopHtml);

            // Section 4+: Related Module Fields
            for (const rel of window.dataRelations) {
                try {
                    const res = await $.get('ajax_get_fields.php', { form_id: rel.mid });
                    if(res.status === 'success') {
                        let relHtml = `<div class="mb-4"><h6 class="text-xxs fw-800 opacity-50 px-2 mb-2 text-primary">${rel.mname.toUpperCase()} FIELDS</h6>`;
                        res.data.forEach(f => {
                            const cleanName = (f.field_name || f.field_label).toLowerCase().replace(/[^a-z0-9]+/g, '_');
                            relHtml += tokenRow(`{{${rel.mname}.${cleanName}}}`, f.field_label, 'bi-input-cursor-text');
                        });
                        relHtml += '</div>';
                        sections.push(relHtml);
                    }
                } catch(e) { console.error(`Failed to load tokens for ${rel.mname}`, e); }
            }
        }
        
        $('#token-list').html(sections.join(''));

        // Background Fetch Sample Data (Primary + Relations)
        window.sampleData = {};
        const fetchSample = (fid) => $.get('ajax_get_sample_data.php', { form_id: fid });

        // 1. Fetch Primary
        fetchSample(mid).done(res => {
            if(res.status === 'success') Object.assign(window.sampleData, res.data);
        });

        // 2. Fetch Relations
        for (const rel of window.dataRelations) {
            fetchSample(rel.mid).done(res => {
                if(res.status === 'success') {
                    // Remap prefixes if rel.mname differs from the module's own name
                    Object.keys(res.data).forEach(k => {
                        const baseField = k.includes('.') ? k.split('.')[1] : k;
                        const aliasedKey = `${rel.mname}.${baseField}`;
                        window.sampleData[aliasedKey] = res.data[k];
                    });
                    if(currentStep === 3) renderLivePreview(); // Auto-refresh if viewing preview
                }
            });
        }
    }

    function tokenRow(token, label, icon) {
        return `
        <div class="token-item" onclick="inject('${token}')">
            <i class="bi ${icon}"></i>
            <div class="token-text">
                <div class="token-name">${label}</div>
                <div class="token-meta">${token}</div>
            </div>
        </div>`;
    }

    window.inject = function(t) {
        const pos = editor.getCursorPosition();
        editor.session.insert(pos, t);
        editor.focus();
    };

    window.loadPreset = function(type) {
        let html = '';
        if(type === 'clean') html = '<!-- Blank Canvas -->\n<div class="document-root">\n  \n</div>';
        if(type === 'invoice') html = '<style>\n  .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 20px; }\n  .v-label { font-weight: 800; color: #666; font-size: 10px; text-transform: uppercase; }\n</style>\n<div class="invoice-header">\n  <div><h1 style="margin:0">INVOICE</h1><p class="v-label">#{{global.submission_id}}</p></div>\n  <div style="text-align:right"><p class="v-label">DATE</p><strong>{{global.sys_date}}</strong></div>\n</div>';
        if(type === 'receipt') html = '<div style="max-width:400px; margin:auto; border:1px dashed #000; padding:20px; text-align:center;">\n  <h3>OFFICIAL RECEIPT</h3>\n  <p>Transaction ID: {{global.submission_id}}</p>\n</div>';
        editor.setValue(html, -1);
    };

    // 5. Preview Logic
    function renderLivePreview() {
        let html = editor.getValue();
        
        // 1. First, replace all tokens with sample data
        if(window.sampleData) {
            Object.keys(window.sampleData).forEach(key => {
                const regex = new RegExp(`{{${key}}}`, 'g');
                html = html.replace(regex, window.sampleData[key]);
            });
        }

        // 2. Next, handle Loop Markers ({{#collection}} ... {{/collection}})
        // For the QA Stage premium preview, we strip the loop tags so the inner 
        // content (now populated with data) is visible to the user.
        window.dataRelations.forEach(rel => {
            const loopStart = new RegExp(`{{#${rel.mname}}}`, 'g');
            const loopEnd = new RegExp(`{{/${rel.mname}}}`, 'g');
            html = html.replace(loopStart, `<div class="qa-loop-indicator text-xxs fw-800 text-indigo opacity-50 mb-2 border-bottom">LOOP: ${rel.mname.toUpperCase()} START</div>`);
            html = html.replace(loopEnd, `<div class="qa-loop-indicator text-xxs fw-800 text-indigo opacity-50 mt-2 border-top">LOOP: ${rel.mname.toUpperCase()} END</div>`);
        });

        // 3. Global Tokens (Alternative fallback)
        html = html.replace(/{{global\.sys_date}}/g, new Date().toLocaleDateString());

        $('#live-content').html(html);
    }

    // 6. Deployment
    $('#final-deploy').on('click', function() {
        const btn = $(this);
        const name = $('#template-name').val();
        const mid = $('#prime-module').val();
        const html = editor.getValue();

        if(!name || !mid || !html) return;

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> DEPLOYING...');

        $.post('ajax_save_template.php', {
            template_id: $('#template-id').val(),
            template_name: name,
            form_id: mid,
            html: html,
            data_connections: JSON.stringify(window.dataRelations),
            css: ''
        }, function(res) {
            if(res.status === 'success') {
                toastr.success('Document Deployed Successfully');
                setTimeout(() => { location.href = 'index.php'; }, 1000);
            } else {
                btn.prop('disabled', false).text('DEPLOY DESIGN LIVE');
                toastr.error(res.message || 'Deployment error');
            }
        }, 'json').fail(function(xhr) {
            btn.prop('disabled', false).text('DEPLOY DESIGN LIVE');
            console.error('Save failed:', xhr.responseText);
            toastr.error('Server side error. Check console.');
        });
    });

    // Token Filter
    $('#token-search').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('.token-item').each(function() {
            const txt = $(this).text().toLowerCase();
            $(this).toggle(txt.indexOf(val) > -1);
        });
    });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
