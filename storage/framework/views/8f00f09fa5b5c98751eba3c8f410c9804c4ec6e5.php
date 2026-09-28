<?php $__env->startSection('title', 'Service Check-in'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .svc-chip { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #e3e8f0; border-radius: 12px; padding: 8px 12px; margin: 0 8px 8px 0; background: #fff; color: #1f2937; text-decoration: none; font-size: .85rem; }
    .svc-chip.active { border-color: #2e5aac; box-shadow: 0 0 0 2px rgba(46,90,172,.15); }
    .svc-state { font-size: .68rem; font-weight: 700; border-radius: 20px; padding: 2px 8px; text-transform: uppercase; }
    .svc-state.open { background: #e6f7ee; color: #0f9d58; }
    .svc-state.upcoming { background: #eef2ff; color: #4338ca; }
    .svc-state.closed { background: #eef0f3; color: #6b7280; }
    .count-box { background: #f7f9fc; border-radius: 12px; padding: 12px; text-align: center; }
    .count-box .n { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
    .count-box .l { font-size: .7rem; color: #8a92a6; text-transform: uppercase; letter-spacing: .03em; }
    #scanner { width: 100%; max-width: 420px; margin: 0 auto; border-radius: 12px; overflow: hidden; }
    .result-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; }
    .result-row:last-child { border-bottom: 0; }
    .result-name { font-weight: 600; }
    .result-meta { font-size: .75rem; color: #8a92a6; }
    .result-actions { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    #checkinBanner { display: none; }
    .soul-row { background: #f7f9fc; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Service Check-in</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li><a class="btn btn-outline-primary" href="<?php echo e(route('services.dashboard')); ?>"><i class="icofont icofont-chart-bar-graph"></i> Dashboard</a></li>
        <li><a class="btn btn-outline-secondary" href="<?php echo e(route('services.times')); ?>"><i class="icofont icofont-clock-time"></i> Service Times</a></li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Service Check-in</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if($errors->any()): ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e($error); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo e(session('success')); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card prog-card mb-3">
<div class="card-body">
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label">Church</label>
            <select name="church" class="form-select" onchange="this.form.submit()">
                <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" <?php if($c->id === $church->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-7 text-md-end">
            <span class="text-muted" style="font-size:.85rem;"><?php echo e(now()->format('l, d M Y · H:i')); ?></span>
        </div>
    </form>

    <?php $__empty_1 = true; $__currentLoopData = $today; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $w = $item->window; ?>
        <a class="svc-chip <?php echo e($current && $current->service->id === $item->service->id ? 'active' : ''); ?>"
           href="<?php echo e(route('services.checkin', ['church' => $church->id, 'service' => $item->service->id])); ?>">
            <strong><?php echo e($item->service->name); ?></strong>
            <?php if($item->service->is_training): ?><span class="svc-state" style="background:#fff4e5;color:#b45309;">Training</span><?php endif; ?>
            <span><?php echo e($w['starts']->format('H:i')); ?>&ndash;<?php echo e($w['ends']->format('H:i')); ?></span>
            <span class="svc-state <?php echo e($item->state); ?>"><?php echo e($item->state); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-muted mb-0">No church service today at <?php echo e(strtoupper($church->name)); ?>.
            Services and their days are set up as recurring programs; times per church under <a href="<?php echo e(route('services.times')); ?>">Service Times</a>.</p>
    <?php endif; ?>
</div>
</div>

<?php if(!$occurrence): ?>
    <?php if($today->isNotEmpty()): ?>
        <?php $next = $today->firstWhere('state', 'upcoming'); ?>
        <div class="alert alert-info">
            <i class="icofont icofont-clock-time"></i>
            <?php if($current && $current->state === 'closed'): ?>
                <?php echo e($current->service->name); ?> has ended &mdash; attendance is closed. See the <a href="<?php echo e(route('services.dashboard', ['church' => $church->id])); ?>">dashboard</a>.
            <?php elseif($next): ?>
                No service is open right now. <strong><?php echo e($next->service->name); ?></strong> check-in opens at
                <strong><?php echo e($next->window['opens']->format('H:i')); ?></strong> (starts <?php echo e($next->window['starts']->format('H:i')); ?>).
            <?php else: ?>
                Today's services have ended. See the <a href="<?php echo e(route('services.dashboard', ['church' => $church->id])); ?>">dashboard</a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <?php $w = $current->window; ?>
    <?php if($occurrence->program->is_training): ?>
        <div class="alert" style="background:#fff4e5;color:#92400e;border:1px dashed #f0b429;">
            <i class="icofont icofont-graduate-alt"></i> <strong>Training mode</strong> &mdash; practise freely: nobody is marked absent, no SMS is sent,
            and nothing here appears on the dashboard or in reports. Clear it with <em>Reset</em> on <a href="<?php echo e(route('services.index')); ?>">Church Services &rarr; Services</a>.
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card prog-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-0"><?php echo e($occurrence->program->name); ?> &middot; <?php echo e(strtoupper($church->name)); ?></h5>
                            <small class="text-muted">
                                Check-in open until <?php echo e($w['ends']->format('H:i')); ?> &middot; arriving after <?php echo e($w['late']->format('H:i')); ?> is marked late
                            </small>
                        </div>
                        <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newSoulsModal" data-member="" data-member-name="">
                            <i class="icofont icofont-plus-circle"></i> Walk-in new soul
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 col-md-3"><div class="count-box"><div class="n" id="cntAttended"><?php echo e($counts['attended']); ?></div><div class="l">Checked in</div></div></div>
                        <div class="col-6 col-md-3"><div class="count-box"><div class="n" id="cntPresent"><?php echo e($counts['present']); ?></div><div class="l">On time</div></div></div>
                        <div class="col-6 col-md-3"><div class="count-box"><div class="n" id="cntLate"><?php echo e($counts['late']); ?></div><div class="l">Late</div></div></div>
                        <div class="col-6 col-md-3"><div class="count-box"><div class="n" id="cntSouls"><?php echo e($counts['new_souls']); ?></div><div class="l">New souls</div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="alert" id="checkinBanner" role="alert"></div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card prog-card h-100">
                <div class="card-body">
                    <p class="modal-section-label mb-2"><i class="icofont icofont-qr-code"></i> Scan member QR</p>
                    <p class="text-muted" style="font-size:.82rem;">Members show the QR from <em>My Check-in QR</em> (portal or app). You can also scan it with your phone camera.</p>
                    <div id="scanner" class="mb-2"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="scanStart"><i class="icofont icofont-camera"></i> Start camera</button>
                        <button type="button" class="btn btn-light" id="scanStop" style="display:none;">Stop</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card prog-card h-100">
                <div class="card-body">
                    <p class="modal-section-label mb-2"><i class="icofont icofont-search"></i> Search by name</p>
                    <input type="search" class="form-control mb-2" id="memberSearch" placeholder="Type a name or phone number..." autocomplete="off">
                    <div id="searchResults"><p class="text-muted mb-0" style="font-size:.85rem;">Start typing to find a member of <?php echo e(strtoupper($church->name)); ?>.</p></div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="card prog-card">
                <div class="card-body">
                    <p class="modal-section-label mb-2">Latest check-ins</p>
                    <div id="recentList">
                        <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="result-row">
                                <div>
                                    <div class="result-name"><?php echo e(trim(optional($a->member)->first_name . ' ' . optional($a->member)->last_name)); ?>

                                        <?php if(optional($a->member)->member_type === 'new_soul'): ?><span class="badge-pill badge-status-new ms-1">New soul</span><?php endif; ?>
                                    </div>
                                    <div class="result-meta"><?php echo e(optional($a->checked_in_at)->format('H:i')); ?> &middot; <?php echo e($a->check_in_method === 'qr' ? 'QR' : 'Manual'); ?></div>
                                </div>
                                <span class="badge-pill badge-status-<?php echo e($a->attendance_status); ?>"><?php echo e(ucfirst($a->attendance_status)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted mb-0" id="recentEmpty" style="font-size:.85rem;">Nobody checked in yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="newSoulsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="newSoulsForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="newSoulsTitle">New souls</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="newSoulsMember">
                    <p class="text-muted" id="newSoulsHint" style="font-size:.85rem;"></p>
                    <div id="soulRows"></div>
                    <button type="button" class="btn btn-light btn-sm" id="addSoulRow"><i class="icofont icofont-plus"></i> Add another person</button>
                    <div class="text-danger small mt-2" id="newSoulsError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="newSoulsSave">Save &amp; check in</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php if($occurrence): ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    var URLS = {
        search: <?php echo json_encode(route('services.members', $occurrence->id), 512) ?>,
        checkin: <?php echo json_encode(route('services.checkin.member', [$occurrence->id, '__ID__'])) ?>,
        scan: <?php echo json_encode(route('services.checkin.scan', $occurrence->id), 512) ?>,
        souls: <?php echo json_encode(route('services.new-souls', $occurrence->id), 512) ?>
    };
    var CSRF = <?php echo json_encode(csrf_token(), 15, 512) ?>;
    var CHURCH = <?php echo json_encode(strtoupper($church->name), 15, 512) ?>;

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body || {})
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) {
                if (!r.ok) {
                    var msg = j.message || 'Something went wrong.';
                    if (j.errors) msg = Object.values(j.errors)[0][0];
                    throw new Error(msg);
                }
                return j;
            });
        });
    }

    function setCounts(c) {
        if (!c) return;
        document.getElementById('cntAttended').textContent = c.attended;
        document.getElementById('cntPresent').textContent = c.present;
        document.getElementById('cntLate').textContent = c.late;
        document.getElementById('cntSouls').textContent = c.new_souls;
    }

    var banner = document.getElementById('checkinBanner');
    function showBanner(kind, html) {
        banner.className = 'alert alert-' + kind;
        banner.innerHTML = html;
        banner.style.display = 'block';
    }

    function addRecent(name, status, time, method, newSoul) {
        var list = document.getElementById('recentList');
        var empty = document.getElementById('recentEmpty');
        if (empty) empty.remove();
        var row = document.createElement('div');
        row.className = 'result-row';
        row.innerHTML = '<div><div class="result-name">' + esc(name) + (newSoul ? ' <span class="badge-pill badge-status-new ms-1">New soul</span>' : '') + '</div>' +
            '<div class="result-meta">' + esc(time) + ' &middot; ' + (method === 'qr' ? 'QR' : 'Manual') + '</div></div>' +
            '<span class="badge-pill badge-status-' + esc(status) + '">' + esc(status.charAt(0).toUpperCase() + status.slice(1)) + '</span>';
        list.prepend(row);
    }

    function handleCheckin(res, method) {
        setCounts(res.counts);
        if (!res.already) addRecent(res.member.name, res.status, res.time, method, res.member.new_soul);
        var html = '<strong>' + esc(res.message) + '</strong>';
        if (res.other_church) html += '<br><small>Visiting from another church.</small>';
        if (!res.member.new_soul) {
            html += ' <button type="button" class="btn btn-sm btn-success ms-2" data-bs-toggle="modal" data-bs-target="#newSoulsModal" data-member="' + res.member.id + '" data-member-name="' + esc(res.member.name) + '">' +
                '<i class="icofont icofont-plus-circle"></i> Came with new souls?</button>';
        }
        showBanner(res.already ? 'warning' : 'success', html);
    }

    /* ---------------- search by name ---------------- */
    var searchInput = document.getElementById('memberSearch');
    var results = document.getElementById('searchResults');
    var timer;

    function renderResults(list) {
        if (!list.length) {
            results.innerHTML = '<p class="text-muted mb-0" style="font-size:.85rem;">No member of ' + esc(CHURCH) + ' matches. Use <strong>Walk-in new soul</strong> for a first-time visitor.</p>';
            return;
        }
        results.innerHTML = list.map(function (m) {
            var checked = m.status === 'present' || m.status === 'late';
            return '<div class="result-row">' +
                '<div><div class="result-name">' + esc(m.name) + (m.new_soul ? ' <span class="badge-pill badge-status-new ms-1">New soul</span>' : '') + '</div>' +
                '<div class="result-meta">' + esc(m.phone || 'No phone') + (checked ? ' &middot; checked in ' + esc(m.time) : '') + '</div></div>' +
                '<div class="result-actions">' +
                (checked
                    ? '<span class="badge-pill badge-status-' + m.status + '">' + (m.status === 'late' ? 'Late' : 'Present') + '</span>'
                    : '<button type="button" class="btn btn-sm btn-primary js-checkin" data-id="' + m.id + '">Check in</button>') +
                (m.new_soul ? '' : '<button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#newSoulsModal" data-member="' + m.id + '" data-member-name="' + esc(m.name) + '" title="Came with new souls">+ New souls</button>') +
                '</div></div>';
        }).join('');
    }

    function search() {
        var q = searchInput.value.trim();
        if (q.length < 2) { results.innerHTML = '<p class="text-muted mb-0" style="font-size:.85rem;">Type at least 2 letters.</p>'; return; }
        fetch(URLS.search + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(renderResults);
    }

    searchInput.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(search, 250); });

    results.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-checkin');
        if (!btn) return;
        btn.disabled = true;
        post(URLS.checkin.replace('__ID__', btn.dataset.id))
            .then(function (res) { handleCheckin(res, 'manual'); search(); })
            .catch(function (err) { showBanner('danger', esc(err.message)); btn.disabled = false; });
    });

    /* ---------------- QR scanner ---------------- */
    var scanner = null, busy = false;
    var startBtn = document.getElementById('scanStart'), stopBtn = document.getElementById('scanStop');

    function onScan(text) {
        if (busy) return;
        busy = true;
        post(URLS.scan, { code: text })
            .then(function (res) { handleCheckin(res, 'qr'); })
            .catch(function (err) { showBanner('danger', esc(err.message)); })
            .finally(function () { setTimeout(function () { busy = false; }, 2500); });
    }

    startBtn.addEventListener('click', function () {
        if (!window.Html5Qrcode) { showBanner('danger', 'The scanner could not load. Check the internet connection, or scan with your phone camera.'); return; }
        scanner = scanner || new Html5Qrcode('scanner');
        scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 230, height: 230 } }, onScan, function () {})
            .then(function () { startBtn.style.display = 'none'; stopBtn.style.display = ''; })
            .catch(function () { showBanner('danger', 'Could not open the camera. Allow camera access for this site, or use the search.'); });
    });

    stopBtn.addEventListener('click', function () {
        if (scanner) scanner.stop().finally(function () { startBtn.style.display = ''; stopBtn.style.display = 'none'; });
    });

    /* ---------------- new souls ---------------- */
    var soulRows = document.getElementById('soulRows');

    function addSoulRow() {
        var i = soulRows.children.length;
        var div = document.createElement('div');
        div.className = 'soul-row';
        div.innerHTML =
            '<div class="row g-2">' +
            '<div class="col-md-3"><input class="form-control form-control-sm" name="first_name" placeholder="First name *" required></div>' +
            '<div class="col-md-3"><input class="form-control form-control-sm" name="last_name" placeholder="Last name"></div>' +
            '<div class="col-md-3"><input class="form-control form-control-sm" name="phone" placeholder="Phone (for follow-up SMS)"></div>' +
            '<div class="col-md-2"><select class="form-select form-select-sm" name="gender"><option value="">Gender</option><option value="male">Male</option><option value="female">Female</option></select></div>' +
            '<div class="col-md-1 text-end">' + (i > 0 ? '<button type="button" class="btn btn-sm btn-light js-remove-soul" title="Remove">&times;</button>' : '') + '</div>' +
            '</div>';
        soulRows.appendChild(div);
    }

    document.getElementById('addSoulRow').addEventListener('click', addSoulRow);
    soulRows.addEventListener('click', function (e) {
        if (e.target.closest('.js-remove-soul')) e.target.closest('.soul-row').remove();
    });

    document.getElementById('newSoulsModal').addEventListener('show.bs.modal', function (e) {
        var trigger = e.relatedTarget || {};
        var memberId = trigger.dataset ? trigger.dataset.member : '';
        var memberName = trigger.dataset ? trigger.dataset.memberName : '';
        document.getElementById('newSoulsMember').value = memberId || '';
        document.getElementById('newSoulsTitle').textContent = memberId ? 'New souls brought by ' + memberName : 'Walk-in new soul';
        document.getElementById('newSoulsHint').textContent = memberId
            ? 'Enter each person ' + memberName + ' came with. They are recorded as new souls of ' + CHURCH + ', invited by ' + memberName + ', and checked in.'
            : 'A first-time visitor who came on their own. They are recorded as a new soul of ' + CHURCH + ' and checked in.';
        document.getElementById('newSoulsError').textContent = '';
        soulRows.innerHTML = '';
        addSoulRow();
    });

    document.getElementById('newSoulsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var people = Array.prototype.map.call(soulRows.querySelectorAll('.soul-row'), function (row) {
            var get = function (n) { return row.querySelector('[name="' + n + '"]').value.trim(); };
            return { first_name: get('first_name'), last_name: get('last_name'), phone: get('phone'), gender: get('gender') || null };
        }).filter(function (p) { return p.first_name; });
        if (!people.length) { document.getElementById('newSoulsError').textContent = 'Enter at least one first name.'; return; }

        var btn = document.getElementById('newSoulsSave');
        btn.disabled = true;
        var memberId = document.getElementById('newSoulsMember').value;
        post(URLS.souls, { member_id: memberId || null, people: people })
            .then(function (res) {
                setCounts(res.counts);
                people.forEach(function (p) { addRecent((p.first_name + ' ' + p.last_name).trim(), 'present', new Date().toTimeString().slice(0, 5), 'manual', true); });
                showBanner('success', '<strong>' + esc(res.message) + '</strong>');
                bootstrap.Modal.getInstance(document.getElementById('newSoulsModal')).hide();
            })
            .catch(function (err) { document.getElementById('newSoulsError').textContent = err.message; })
            .finally(function () { btn.disabled = false; });
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/checkin.blade.php ENDPATH**/ ?>