<?php $__env->startSection('title', 'Program Reports'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Program Reports</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs</li>
    <li class="breadcrumb-item active">Reports</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-1 mx-auto mb-3"><i class="icofont icofont-listing-box"></i></div>
                <h6>Program Report</h6>
                <p class="text-muted small">Every program with classification, scope, access and totals.</p>
                <a href="<?php echo e(route('programs.index')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('programs.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-2 mx-auto mb-3"><i class="icofont icofont-ticket"></i></div>
                <h6>Registration Report</h6>
                <p class="text-muted small">Every registration by program, member, status and payment.</p>
                <a href="<?php echo e(route('program-reports.registrations')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('program-reports.registrations.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-4 mx-auto mb-3"><i class="icofont icofont-check-circled"></i></div>
                <h6>Attendance Report</h6>
                <p class="text-muted small">Every attendance record by program, date, status and method.</p>
                <a href="<?php echo e(route('program-reports.attendance')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('program-reports.attendance.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-3 mx-auto mb-3"><i class="icofont icofont-badge"></i></div>
                <h6>New Souls Report</h6>
                <p class="text-muted small">First-time visitors with follow-up status and church.</p>
                <a href="<?php echo e(route('new-souls.index')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('program-reports.new-souls.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

</div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/reports/index.blade.php ENDPATH**/ ?>