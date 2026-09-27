<?php $__env->startSection('title', 'Programs & Attendance Settings'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Programs & Attendance Settings</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">Settings</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Per-Program Settings</p>
                <p class="text-muted">Most Programs settings live on each individual program, not here:</p>
                <ul class="text-muted">
                    <li>Recurring vs special, church/department/cell scope, free/paid access &mdash; on the program's <strong>Create / Edit</strong> form.</li>
                    <li>QR/barcode check-in &mdash; always on for special programs, opt-in for recurring ones via the <strong>Enable QR/Barcode Check-in</strong> switch.</li>
                </ul>
                <a href="<?php echo e(route('programs.index')); ?>" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="icofont icofont-listing-box"></i> Go to Programs
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Access Permissions</p>
                <p class="text-muted">These permission codes gate Programs & Attendance actions. Assign them to a designation via the existing role/permission tables to grant non-ADMIN staff access to specific actions.</p>
                <div class="table-responsive">
                <table class="table prog-table align-middle">
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

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/settings/index.blade.php ENDPATH**/ ?>