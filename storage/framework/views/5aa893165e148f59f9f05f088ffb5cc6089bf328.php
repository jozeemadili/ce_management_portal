<?php $__env->startSection('title', 'New Souls'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>New Souls</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('new-souls.dashboard')); ?>">
                <i class="icofont icofont-chart-bar-graph"></i> Dashboard
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item active">New Souls</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-people"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['total']); ?></p><p class="prog-stat-label">Total New Souls</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-4"><i class="icofont icofont-badge"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['new']); ?></p><p class="prog-stat-label">New</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-phone"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['in_progress']); ?></p><p class="prog-stat-label">In Follow-Up</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['connected']); ?></p><p class="prog-stat-label">Connected / Became Member</p></div>
            </div>
        </div>
    </div>
</div>

<div class="card prog-card">
    <div class="card-body">
        <form method="GET" class="prog-filter-bar row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Name or phone...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All Statuses --</option>
                    <?php $__currentLoopData = ['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php if(request('status')===$val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="icofont icofont-search"></i> Filter</button>
            </div>
        </form>

        <?php if($visitors->count()): ?>
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Name</th><th>Contact</th><th>First Visit</th><th>Program</th><th>Status</th><th>Church (assign)</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $visitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="fw-semibold"><?php echo e($v->first_name); ?> <?php echo e($v->last_name); ?>

                <?php if($v->location): ?><div class="text-muted fw-normal" style="font-size:.75rem;"><i class="icofont icofont-location-pin"></i> <?php echo e($v->location); ?></div><?php endif; ?>
            </td>
            <td style="font-size:.85rem;"><?php echo e($v->phone ?? '—'); ?><?php if($v->email): ?><br><?php echo e($v->email); ?><?php endif; ?></td>
            <td><?php echo e(optional($v->first_visit_date)->format('d M Y') ?? '—'); ?></td>
            <td><?php echo e(optional($v->firstVisitProgram)->name ?? '—'); ?></td>
            <td><span class="badge-pill badge-status-<?php echo e($v->follow_up_status); ?>"><?php echo e(ucfirst(str_replace('_',' ',$v->follow_up_status))); ?></span></td>
            <td><?php echo $__env->make('portal.churches.invitees.partials.assign', ['invitee' => $v, 'occurrence' => null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo e($v->id); ?>">
                    <i class="icofont icofont-edit"></i> Update Status
                </button>
            </td>
        </tr>

        <div class="modal fade" id="statusModal<?php echo e($v->id); ?>">
        <div class="modal-dialog">
        <div class="modal-content">
        <form method="POST" action="<?php echo e(route('new-souls.status', $v->id)); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><?php echo e($v->first_name); ?> <?php echo e($v->last_name); ?> — Follow-Up Status</h5>
            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <?php $__currentLoopData = ['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php if($v->follow_up_status===$val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <label class="form-label mt-3">Add a Note (optional)</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
            <?php if($v->notes): ?>
            <p class="text-muted mt-2 mb-0"><small><strong>Previous notes:</strong> <?php echo e($v->notes); ?></small></p>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary">Save Status</button>
        </div>
        </form>
        </div>
        </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        </table>
        </div>
        <?php echo e($visitors->links()); ?>

        <?php else: ?>
        <div class="prog-empty"><i class="icofont icofont-people"></i><p class="mb-0">No new souls recorded yet.</p></div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/new-souls/index.blade.php ENDPATH**/ ?>