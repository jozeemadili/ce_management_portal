<?php $__env->startSection('title', 'SMS Templates'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .sms-type-help { font-size: .82rem; color: #6b7280; margin-bottom: 12px; }
    .sms-template { border: 1px solid #eef0f3; border-radius: 12px; padding: 14px 16px; margin-bottom: 12px; }
    .sms-template.inactive { opacity: .65; }
    .sms-body { white-space: pre-wrap; font-size: .88rem; margin: 8px 0; }
    .sms-preview { background: #f7f9fc; border-radius: 10px; padding: 10px 12px; font-size: .84rem; white-space: pre-wrap; }
    .sms-meta { font-size: .75rem; color: #8a92a6; }
    .sms-counter { font-size: .8rem; }
    .sms-counter .warn { color: #b45309; font-weight: 600; }
    .placeholder-chip { font-size: .75rem; margin: 0 4px 6px 0; }
    .sms-balance { background: #e6f7ee; color: #0f9d58; font-weight: 600; border-radius: 20px; padding: 6px 14px; font-size: .82rem; }
    .sms-balance.unknown { background: #fff4e5; color: #b45309; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>SMS Templates</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <button class="btn btn-primary" type="button" id="newTemplateBtn">
                New Template <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">SMS Templates</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if($errors->any()): ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo e($error); ?>

            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <?php if($balance !== null): ?>
        <span class="sms-balance"><i class="icofont icofont-ui-message"></i> SMS balance: <?php echo e(number_format($balance)); ?></span>
    <?php else: ?>
        <span class="sms-balance unknown"><i class="icofont icofont-warning"></i> SMS balance unavailable - check the SMS gateway settings in .env</span>
    <?php endif; ?>
    <span class="text-muted" style="font-size:.82rem;">
        A template for one program replaces the "All programs" template of the same type for that program.
        Turn a template off to stop that SMS.
    </span>
</div>

<div class="row">
<?php $__currentLoopData = \App\Models\ProgramSmsTemplate::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-1"><?php echo e($label); ?></p>
                <p class="sms-type-help"><?php echo e(\App\Models\ProgramSmsTemplate::TYPE_HELP[$type]); ?></p>

                <?php $__empty_1 = true; $__currentLoopData = $templates->get($type, collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="sms-template <?php echo e($t->is_active ? '' : 'inactive'); ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong style="font-size:.85rem;"><?php echo e($t->program ? $t->program->name : 'All programs'); ?></strong><br>
                                <span class="badge-pill <?php echo e($t->is_active ? 'badge-status-active' : 'badge-status-draft'); ?>"><?php echo e($t->is_active ? 'On' : 'Off'); ?></span>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-light edit-template-btn" title="Edit"
                                    data-action="<?php echo e(route('program-sms.update', $t->id)); ?>"
                                    data-type="<?php echo e($t->type); ?>"
                                    data-program="<?php echo e($t->program_id); ?>"
                                    data-body="<?php echo e($t->body); ?>"
                                    data-active="<?php echo e($t->is_active ? 1 : 0); ?>">
                                    <i class="icofont icofont-edit"></i>
                                </button>
                                <form method="POST" action="<?php echo e(route('program-sms.destroy', $t->id)); ?>" onsubmit="return confirm('Delete this SMS template?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete"><i class="icofont icofont-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="sms-body"><?php echo e($t->body); ?></div>
                        <div class="sms-preview"><?php echo e(\App\Models\ProgramSmsTemplate::example($t->body)); ?></div>
                        <div class="sms-meta mt-1 sms-static-count" data-text="<?php echo e(\App\Models\ProgramSmsTemplate::example($t->body)); ?>"></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="prog-empty py-4">
                        <i class="icofont icofont-ui-message"></i>
                        No template - this SMS is not sent.
                    </div>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-primary btn-sm new-of-type-btn" data-type="<?php echo e($type); ?>">
                    <i class="icofont icofont-plus"></i> Add <?php echo e(strtolower($label)); ?> template
                </button>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

</div>


<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" method="POST" id="templateForm" action="<?php echo e(route('program-sms.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_editing" id="tplEditing">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">New SMS Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="tplType" class="form-select" required>
                            <?php $__currentLoopData = \App\Models\ProgramSmsTemplate::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted" id="tplTypeHelp"></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Applies to</label>
                        <select name="program_id" id="tplProgram" class="form-select">
                            <option value="">All programs (default)</option>
                            <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?><?php echo e($p->start_date ? ' - ' . $p->start_date->format('d M Y') : ''); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <label class="form-label">Message</label>
                <div class="mb-1">
                    <?php $__currentLoopData = \App\Models\ProgramSmsTemplate::PLACEHOLDERS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph => [$desc, $example]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="btn btn-light btn-sm placeholder-chip" data-ph="<?php echo e($ph); ?>" title="<?php echo e($desc); ?> (e.g. <?php echo e($example); ?>)"><?php echo e($ph); ?></button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <textarea name="body" id="tplBody" class="form-control" rows="4" maxlength="1000" required
                    placeholder="Dear {first_name}, you are registered for {program}. Your code is {code}."></textarea>
                <div class="sms-counter mt-1" id="tplCounter"></div>

                <label class="form-label mt-3">Preview (with example values)</label>
                <div class="sms-preview" id="tplPreview"></div>
                <div class="sms-counter mt-1" id="tplPreviewCounter"></div>
                <small class="text-muted d-block mt-1">Real messages use each person's own name, code and amounts, so their length varies a little.</small>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tplActive" checked>
                    <label class="form-check-label" for="tplActive">On - send this SMS</label>
                </div>

                <hr>
                <label class="form-label">Send a test SMS <span class="text-muted">(uses the example values above)</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="tplTestPhone" placeholder="e.g. 0712345678">
                    <button type="button" class="btn btn-outline-secondary" id="tplTestBtn"><i class="icofont icofont-paper-plane"></i> Send test</button>
                </div>
                <small id="tplTestResult" class="d-block mt-1"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var PLACEHOLDERS = <?php echo json_encode(collect(\App\Models\ProgramSmsTemplate::PLACEHOLDERS)->map(fn ($p) => $p[1]), 15, 512) ?>;
    var TYPE_HELP = <?php echo json_encode(\App\Models\ProgramSmsTemplate::TYPE_HELP, 15, 512) ?>;
    var STORE_URL = <?php echo json_encode(route('program-sms.store'), 15, 512) ?>;
    var TEST_URL = <?php echo json_encode(route('program-sms.test'), 15, 512) ?>;

    // GSM 03.38: basic characters cost 1, extension characters 2; anything
    // else (emoji, curly quotes, ...) switches the SMS to Unicode (70 chars).
    var GSM = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
    var GSM_EXT = "^{}\\[~]|€";

    function smsInfo(text) {
        var length = 0, unicode = false;
        for (var ch of text) {
            if (GSM.indexOf(ch) !== -1) length += 1;
            else if (GSM_EXT.indexOf(ch) !== -1) length += 2;
            else unicode = true;
        }
        if (unicode) length = text.length;
        var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
        var parts = length === 0 ? 0 : (length <= single ? 1 : Math.ceil(length / multi));
        return { length: length, parts: parts, unicode: unicode };
    }

    function describe(info, withParts) {
        var s = info.length + ' characters';
        if (withParts) {
            s += ' · ' + info.parts + ' SMS' + (info.unicode ? ' (Unicode)' : '');
            if (info.parts > 1) s += ' <span class="warn">- costs ' + info.parts + ' credits per person</span>';
            if (info.unicode) s += ' <span class="warn">- special characters (e.g. emoji, curly quotes) shorten each SMS to 70</span>';
        }
        return s;
    }

    function example(body) {
        return body.split(/(\{[a-z_]+\})/).map(function (part) {
            return Object.prototype.hasOwnProperty.call(PLACEHOLDERS, part) ? PLACEHOLDERS[part] : part;
        }).join('').replace(/[ \t]{2,}/g, ' ').trim();
    }

    var modalEl = document.getElementById('templateModal');
    var modal = new bootstrap.Modal(modalEl);
    var form = document.getElementById('templateForm');
    var body = document.getElementById('tplBody');
    var type = document.getElementById('tplType');

    function refresh() {
        var preview = example(body.value);
        document.getElementById('tplCounter').innerHTML = 'Template: ' + describe(smsInfo(body.value), false);
        document.getElementById('tplPreview').textContent = preview || '—';
        document.getElementById('tplPreviewCounter').innerHTML = 'Example SMS: ' + describe(smsInfo(preview), true);
        document.getElementById('tplTypeHelp').textContent = TYPE_HELP[type.value] || '';
    }

    function open(opts) {
        form.action = opts.action || STORE_URL;
        document.getElementById('tplEditing').value = opts.action || '';
        document.getElementById('templateModalTitle').textContent = opts.action ? 'Edit SMS Template' : 'New SMS Template';
        type.value = opts.type || 'registration';
        document.getElementById('tplProgram').value = opts.program || '';
        body.value = opts.body || '';
        document.getElementById('tplActive').checked = opts.active !== '0';
        document.getElementById('tplTestResult').textContent = '';
        refresh();
        modal.show();
    }

    document.getElementById('newTemplateBtn').addEventListener('click', function () { open({}); });
    document.querySelectorAll('.new-of-type-btn').forEach(function (b) {
        b.addEventListener('click', function () { open({ type: b.dataset.type }); });
    });
    document.querySelectorAll('.edit-template-btn').forEach(function (b) {
        b.addEventListener('click', function () {
            open({ action: b.dataset.action, type: b.dataset.type, program: b.dataset.program, body: b.dataset.body, active: b.dataset.active });
        });
    });

    document.querySelectorAll('.placeholder-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var start = body.selectionStart, end = body.selectionEnd, ph = chip.dataset.ph;
            body.value = body.value.slice(0, start) + ph + body.value.slice(end);
            body.focus();
            body.selectionStart = body.selectionEnd = start + ph.length;
            refresh();
        });
    });

    body.addEventListener('input', refresh);
    type.addEventListener('change', refresh);

    document.querySelectorAll('.sms-static-count').forEach(function (el) {
        el.innerHTML = describe(smsInfo(el.dataset.text), true);
    });

    document.getElementById('tplTestBtn').addEventListener('click', function () {
        var btn = this, out = document.getElementById('tplTestResult');
        btn.disabled = true;
        out.className = 'd-block mt-1 text-muted';
        out.textContent = 'Sending...';
        fetch(TEST_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
            body: JSON.stringify({ phone: document.getElementById('tplTestPhone').value, body: body.value })
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
          .then(function (res) {
              out.className = 'd-block mt-1 ' + (res.ok ? 'text-success' : 'text-danger');
              out.textContent = res.ok ? 'Test SMS sent.' : (res.j.message || 'Sending failed.');
          })
          .catch(function () { out.className = 'd-block mt-1 text-danger'; out.textContent = 'Sending failed.'; })
          .finally(function () { btn.disabled = false; });
    });

    <?php if($errors->any() && old('body')): ?>
        open({ action: <?php echo json_encode(old('_editing'), 15, 512) ?> || null, type: <?php echo json_encode(old('type'), 15, 512) ?>, program: <?php echo json_encode(old('program_id'), 15, 512) ?>, body: <?php echo json_encode(old('body'), 15, 512) ?>, active: <?php echo json_encode(old('is_active') ? '1' : '0', 15, 512) ?> });
    <?php endif; ?>
})();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/sms-templates/index.blade.php ENDPATH**/ ?>