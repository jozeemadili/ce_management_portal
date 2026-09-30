<?php $__env->startSection('title', 'New Souls Dashboard'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .funnel-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; }
    .funnel-row:last-child { border-bottom: none; }
    .funnel-label { width: 160px; font-size: .82rem; color: #4b5563; font-weight: 600; }
    .funnel-bar-wrap { flex: 1; }
    .funnel-count { width: 40px; text-align: right; font-weight: 700; font-size: .9rem; }
    .mini-list-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f2f7; font-size: .84rem; }
    .mini-list-row:last-child { border-bottom: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>New Souls Dashboard</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e($churchView ? route('invitees.index') : route('new-souls.index')); ?>">
                <i class="icofont icofont-listing-box"></i> <?php echo e($churchView ? 'New Invitees List' : 'New Souls List'); ?>

            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <?php if($churchView): ?>
        <li class="breadcrumb-item">Church Setup</li>
        <li class="breadcrumb-item active">New Souls Dashboard</li>
    <?php else: ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('new-souls.index')); ?>">New Souls</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
    <?php endif; ?>
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
    <div class="col-md-5">
        <label class="form-label">Church</label>
        <select name="church" class="form-select" onchange="this.form.submit()">
            <option value="">All my churches</option>
            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if($selectedChurch && $selectedChurch->id === $c->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-7 text-muted" style="font-size:.85rem;">
        <?php echo e($selectedChurch ? 'New souls assigned to ' . strtoupper($selectedChurch->name) . '.' : 'New souls across all your churches.'); ?>

        Follow them up below until they become members.
    </div>
</form>

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-sun"></i></div>
                <div><p class="prog-stat-value"><?php echo e($counts['today']); ?></p><p class="prog-stat-label">Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value"><?php echo e($counts['week']); ?></p><p class="prog-stat-label">This Week</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value"><?php echo e($counts['month']); ?></p><p class="prog-stat-label">This Month</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-people"></i></div>
                <div><p class="prog-stat-value"><?php echo e($counts['total']); ?></p><p class="prog-stat-label">All Time</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Follow-Up Funnel</p>
                <?php $maxFunnel = max(array_merge(array_values($funnel), [1])); ?>
                <?php $__currentLoopData = ['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="funnel-row">
                    <div class="funnel-label"><?php echo e($label); ?></div>
                    <div class="funnel-bar-wrap">
                        <div class="prog-progress"><div class="prog-progress-bar" style="width: <?php echo e($funnel[$key] > 0 ? max(4, round($funnel[$key]/$maxFunnel*100)) : 0); ?>%"></div></div>
                    </div>
                    <div class="funnel-count"><?php echo e($funnel[$key]); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-3 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">By Program</p>
                <?php $__empty_1 = true; $__currentLoopData = $byProgram; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mini-list-row">
                    <span><?php echo e(optional($row->firstVisitProgram)->name ?? 'Unknown'); ?></span>
                    <strong><?php echo e($row->total); ?></strong>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">No data yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-3 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">By Church</p>
                <?php $__empty_1 = true; $__currentLoopData = $byChurch; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mini-list-row">
                    <span><?php echo e(optional($row->church)->name ?? 'Unknown'); ?></span>
                    <strong><?php echo e($row->total); ?></strong>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">No data yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card prog-card mt-1">
<div class="card-body">
    <p class="modal-section-label">Follow-up worklist (<?php echo e($worklist->count()); ?>)</p>
    <p class="text-muted" style="font-size:.85rem;">New souls still being followed up, longest waiting first. Update their status as you go; choose <strong>Became Member</strong> when they join (they get a login).</p>
    <?php if($worklist->isEmpty()): ?>
        <div class="prog-empty py-3"><i class="icofont icofont-check-circled"></i>Everyone is followed up. 🎉</div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th>New soul</th><th>First visit</th><th>Follow-up status</th><th>Church (assign)</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $worklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <strong><?php echo e(trim($v->first_name . ' ' . $v->last_name)); ?></strong>
                    <div class="text-muted" style="font-size:.75rem;"><?php echo e($v->phone ?: '—'); ?><?php if($v->location): ?> &middot; <?php echo e($v->location); ?><?php endif; ?></div>
                </td>
                <td><?php echo e(optional($v->first_visit_date)->format('d M Y')); ?><div class="text-muted" style="font-size:.75rem;"><?php echo e(optional($v->first_visit_date)->diffForHumans()); ?> &middot; <?php echo e(optional($v->firstVisitProgram)->name); ?></div></td>
                <td>
                    <form method="POST" action="<?php echo e(route('new-souls.status', $v->id)); ?>" class="d-flex gap-1 flex-wrap"
                          onsubmit="return this.status.value !== 'became_member' || confirm('Make <?php echo e(addslashes($v->first_name)); ?> a member?')">
                        <?php echo csrf_field(); ?>
                        <select name="status" class="form-select form-select-sm" style="max-width:190px;">
                            <?php $__currentLoopData = ['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php if($v->follow_up_status === $val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input name="notes" class="form-control form-control-sm" style="max-width:170px;" placeholder="Note (optional)">
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
                <td><?php echo $__env->make('portal.churches.invitees.partials.assign', ['invitee' => $v, 'occurrence' => null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></td>
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

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/new-souls/dashboard.blade.php ENDPATH**/ ?>