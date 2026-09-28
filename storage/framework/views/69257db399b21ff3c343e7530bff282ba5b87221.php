<?php $__env->startSection('title', 'My Check-in QR'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .my-qr { display: inline-block; background: #fff; padding: 14px; border-radius: 16px; box-shadow: 0 2px 12px rgba(46,90,172,.12); }
    .my-qr svg { display: block; width: 240px; height: 240px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>My Check-in QR</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">My Check-in QR</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-5">
<div class="card prog-card">
<div class="card-body text-center">
    <h4 class="mb-1"><?php echo e(trim($member->first_name . ' ' . $member->last_name)); ?></h4>
    <p class="text-muted"><?php echo e(optional($member->church)->name); ?></p>
    <div class="my-qr mb-3"><?php echo $qrSvg; ?></div>
    <p class="text-muted mb-0" style="font-size:.88rem;">
        Show this code at the church entrance during a service. The usher scans it and you are marked as attended.
        It is yours alone &mdash; don't share it.
    </p>
</div>
</div>
</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/my-qr.blade.php ENDPATH**/ ?>