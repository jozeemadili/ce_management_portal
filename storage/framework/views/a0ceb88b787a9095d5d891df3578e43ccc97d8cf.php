<?php $__env->startSection('title', 'My Pledges'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>My Pledges</h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('my-pledges.browse')); ?>">
                <i class="icofont icofont-gift"></i> Make a Pledge
            </a>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">My Pledges</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
<div class="col-sm-12">
<div class="card pledge-card">
<div class="card-body">

<form method="GET" action="<?php echo e(route('my-pledges.index')); ?>" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All Status</option>
            <?php $__currentLoopData = ['pledged','partially_fulfilled','fulfilled','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php if(request('status') == $s): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_',' ',$s))); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i> Filter</button>
    </div>
</div>
</form>

<?php if($pledges->count()): ?>
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>Campaign</th>
    <th>Reference</th>
    <th class="text-end">Amount</th>
    <th class="text-end">Paid</th>
    <th class="text-end">Outstanding</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $pledges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pledge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr class="pledge-row" style="cursor:pointer" onclick="window.location='<?php echo e(route('my-pledges.show', $pledge->id)); ?>'">
    <td><?php echo e(optional($pledge->campaign)->name); ?></td>
    <td><span class="fw-semibold"><?php echo e($pledge->pledge_reference); ?></span></td>
    <td class="text-end"><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->amount, 2)); ?></td>
    <td class="text-end"><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->totalFulfilled(), 2)); ?></td>
    <td class="text-end"><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->outstanding(), 2)); ?></td>
    <td><span class="badge-pill badge-status-<?php echo e($pledge->status); ?>"><?php echo e(ucfirst(str_replace('_',' ',$pledge->status))); ?></span></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<?php echo e($pledges->links()); ?>

</div>
<?php else: ?>
<div class="pledge-empty">
    <i class="icofont icofont-gift"></i>
    <p class="mb-0">You haven't made any pledges yet.</p>
</div>
<?php endif; ?>

</div>
</div>
</div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/pledges/my/index.blade.php ENDPATH**/ ?>