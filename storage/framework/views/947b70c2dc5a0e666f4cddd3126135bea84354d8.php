<?php $__env->startSection('title', 'Pledges Settings'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Pledges Settings</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Settings</li>
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

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Payment Methods</p>
                <p class="text-muted">Options shown on the Record Contribution form. Add new ones here or deactivate ones you no longer use &mdash; nothing is hardcoded.</p>

                <form method="POST" action="<?php echo e(route('pledge-settings.payment-methods.store')); ?>" class="d-flex gap-2 mb-3">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. USSD, Cheque, POS...">
                    <button class="btn btn-primary btn-sm text-nowrap"><i class="icofont icofont-plus-circle"></i> Add</button>
                </form>

                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead><tr><th>Name</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($m->name); ?></td>
                    <td>
                        <span class="badge-pill <?php echo e($m->is_active ? 'badge-status-active' : 'badge-status-closed'); ?>">
                            <?php echo e($m->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="<?php echo e(route('pledge-settings.payment-methods.toggle', $m->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-light">
                                <?php echo e($m->is_active ? 'Deactivate' : 'Activate'); ?>

                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="3" class="text-center text-muted">No payment methods yet.</td></tr>
                <?php endif; ?>
                </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Per-Campaign Settings</p>
                <p class="text-muted">Most Pledges settings live on each individual campaign, not here:</p>
                <ul class="text-muted">
                    <li>Allow anonymous pledges, enable live presentation &mdash; on the campaign's <strong>Edit</strong> form.</li>
                    <li>What appears on the presentation screen (amount, target, pledger count, latest pledges, graph, name masking) &mdash; on the <a href="<?php echo e(route('pledge-live.select')); ?>">Live Presentation</a> screen's controls (gear icon on each campaign).</li>
                </ul>
                <a href="<?php echo e(route('pledge-campaigns.index')); ?>" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="icofont icofont-bullseye"></i> Go to Campaigns
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Access Permissions</p>
                <p class="text-muted">These permission codes gate Pledges-module actions. Assign them to a designation via the existing role/permission tables to grant non-ADMIN staff access to specific actions.</p>
                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead><tr><th>Code</th><th>Name</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><code><?php echo e($p->code); ?></code></td>
                    <td><?php echo e($p->name); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/pledges/settings/index.blade.php ENDPATH**/ ?>