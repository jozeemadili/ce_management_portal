<?php $__env->startSection('title', 'Services Dashboard'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .testimony { border-bottom: 1px solid #f0f2f7; padding: 10px 0; }
    .testimony:last-child { border-bottom: 0; }
    .testimony-text { white-space: pre-wrap; font-size: .88rem; margin: 4px 0; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Services Dashboard</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li><a class="btn btn-primary" href="<?php echo e(route('services.checkin')); ?>"><i class="icofont icofont-qr-code"></i> Service Check-in</a></li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Services Dashboard</li>
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

<form method="GET" class="prog-filter-bar row g-2 align-items-end">
    <div class="col-md-3">
        <label class="form-label">Church</label>
        <select name="church" class="form-select">
            <option value="">All my churches</option>
            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if((int) request('church') === $c->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Service</label>
        <select name="service" class="form-select">
            <option value="">All services</option>
            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s->id); ?>" <?php if((int) request('service') === $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="<?php echo e($from->format('Y-m-d')); ?>">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="<?php echo e($to->format('Y-m-d')); ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100"><i class="icofont icofont-filter"></i> Show</button>
    </div>
</form>

<div class="row">
    <?php $__currentLoopData = [
        ['Services held', $totals['services'], 'bg-1', 'icofont-building-alt'],
        ['Attended', $totals['attended'], 'bg-2', 'icofont-users-alt-4'],
        ['Avg. per service', $totals['average'] ?? '—', 'bg-6', 'icofont-chart-line'],
        ['Late', $totals['late'], 'bg-4', 'icofont-clock-time'],
        ['Absent', $totals['absent'], 'bg-5', 'icofont-close-circled'],
        ['New souls', $totals['new_souls'], 'bg-3', 'icofont-heart-alt'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $bg, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-6 col-xl-2 mb-3">
            <div class="card prog-stat-card"><div class="stat-body">
                <div class="prog-stat-icon <?php echo e($bg); ?>"><i class="icofont <?php echo e($icon); ?>"></i></div>
                <div><p class="prog-stat-value"><?php echo e(is_numeric($value) ? number_format($value) : $value); ?></p><p class="prog-stat-label"><?php echo e($label); ?></p></div>
            </div></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card prog-card h-100"><div class="card-body">
            <p class="modal-section-label">Attendance &amp; new souls per service day</p>
            <?php if($trend->isEmpty()): ?>
                <div class="prog-empty py-4"><i class="icofont icofont-chart-line"></i>No services in this period yet.</div>
            <?php else: ?>
                <canvas id="trendChart" height="120"></canvas>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card prog-card h-100"><div class="card-body">
            <p class="modal-section-label">Latest testimonies</p>
            <?php $__empty_1 = true; $__currentLoopData = $testimonies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="testimony">
                    <strong><?php echo e(trim($t->member->first_name . ' ' . $t->member->last_name)); ?></strong>
                    <small class="text-muted">&middot; <?php echo e(optional($t->member->church)->name); ?> &middot; <?php echo e($t->submitted_at->diffForHumans()); ?></small>
                    <div class="testimony-text"><?php echo e(\Illuminate\Support\Str::limit($t->feedback, 300)); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0" style="font-size:.85rem;">No testimonies yet. After a service, send the follow-up SMS to its new souls from the table below.</p>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Services</p>
    <?php if($occurrences->isEmpty()): ?>
        <div class="prog-empty py-4"><i class="icofont icofont-building-alt"></i>No services in this period.</div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table prog-table">
        <thead>
            <tr>
                <th>Date</th><th>Service</th><th>Church</th><th>Attended</th><th>Late</th><th>Absent</th><th>New souls</th><th>Follow-up</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $occurrences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($o->occurrence_date->format('D d M Y')); ?>

                    <?php if (! ($o->isClosed())): ?><span class="badge-pill badge-status-active ms-1">Open</span><?php endif; ?>
                </td>
                <td><?php echo e($o->program->name); ?></td>
                <td><?php echo e(strtoupper(optional($o->church)->name)); ?></td>
                <td><strong><?php echo e($o->stats['attended']); ?></strong></td>
                <td><?php echo e($o->stats['late']); ?></td>
                <td><?php echo e($o->isClosed() ? $o->stats['absent'] : '—'); ?></td>
                <td><?php echo e($o->stats['new_souls']); ?></td>
                <td>
                    <?php if($o->followup): ?>
                        <small class="d-block"><?php echo e($o->followup->sent); ?> SMS sent &middot; <?php echo e($o->followup->replies); ?> replied</small>
                    <?php endif; ?>
                    <?php if($o->stats['new_souls'] > 0): ?>
                        <form method="POST" action="<?php echo e(route('services.followup', $o->id)); ?>"
                              onsubmit="return confirm('Send the follow-up SMS (with the testimony link) to this service\'s new souls who have not received it yet?')">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-outline-success"><i class="icofont icofont-ui-message"></i> Send follow-up SMS</button>
                        </form>
                    <?php elseif(!$o->followup): ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if($trend->isNotEmpty()): ?>
<script src="<?php echo e(asset('assets/js/chart/chartjs/chart.min.js')); ?>"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($trend->pluck('label'), 15, 512) ?>,
        datasets: [
            { label: 'Attended', data: <?php echo json_encode($trend->pluck('attended'), 15, 512) ?>, backgroundColor: 'rgba(46,90,172,.75)', borderRadius: 6 },
            { label: 'New souls', data: <?php echo json_encode($trend->pluck('new_souls'), 15, 512) ?>, backgroundColor: 'rgba(168,85,247,.75)', borderRadius: 6 }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/dashboard.blade.php ENDPATH**/ ?>