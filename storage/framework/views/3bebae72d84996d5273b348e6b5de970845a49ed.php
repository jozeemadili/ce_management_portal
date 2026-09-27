<?php $__env->startSection('title', 'Pledge Reports'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Pledge Reports</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Reports</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body text-center py-4">
                <div class="pledge-stat-icon bg-1 mx-auto mb-3"><i class="icofont icofont-bullseye"></i></div>
                <h6>Campaign Report</h6>
                <p class="text-muted small">Target, pledged, fulfilled, outstanding and completion per campaign.</p>
                <a href="<?php echo e(route('pledge-campaigns.index')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('pledge-campaigns.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body text-center py-4">
                <div class="pledge-stat-icon bg-2 mx-auto mb-3"><i class="icofont icofont-people"></i></div>
                <h6>Member Pledge Report</h6>
                <p class="text-muted small">Every pledge by member, campaign, amount, fulfillment and status.</p>
                <a href="<?php echo e(route('pledge-management.index')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('pledge-management.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body text-center py-4">
                <div class="pledge-stat-icon bg-6 mx-auto mb-3"><i class="icofont icofont-money"></i></div>
                <h6>Contribution Report</h6>
                <p class="text-muted small">Every payment recorded against a pledge, with method and reference.</p>
                <a href="<?php echo e(route('pledge-contributions.index')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('pledge-contributions.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body text-center py-4">
                <div class="pledge-stat-icon bg-4 mx-auto mb-3"><i class="icofont icofont-badge"></i></div>
                <h6>Staff Recorded Pledges</h6>
                <p class="text-muted small">Pledges recorded by staff on behalf of members, with who and when.</p>
                <a href="<?php echo e(route('pledge-reports.staff-recorded')); ?>" class="btn btn-outline-primary btn-sm">View</a>
                <a href="<?php echo e(route('pledge-reports.staff-recorded.export')); ?>" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

</div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/pledges/reports/index.blade.php ENDPATH**/ ?>