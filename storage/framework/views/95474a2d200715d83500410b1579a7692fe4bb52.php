<?php $__env->startSection('title', 'People I\'ve Invited'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>People I've Invited</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('my-programs.browse')); ?>">
                <i class="icofont icofont-plus-circle"></i> Browse Programs
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item active">People I've Invited</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="card prog-card">
    <div class="card-body">
        <?php if($registrations->count()): ?>
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Reference</th><th>Attendee</th><th>Church</th><th>Program</th><th>Date</th><th>Status</th><th>Payment</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="fw-semibold"><?php echo e($reg->registration_reference); ?></td>
            <td><?php echo e(optional($reg->member)->first_name); ?> <?php echo e(optional($reg->member)->last_name); ?></td>
            <td><?php echo e(optional(optional($reg->member)->church)->name ?? '—'); ?></td>
            <td><?php echo e(optional($reg->program)->name); ?></td>
            <td><?php echo e(optional(optional($reg->program)->start_date)->format('d M Y') ?? '—'); ?></td>
            <td><span class="badge-pill badge-status-<?php echo e($reg->registration_status); ?>"><?php echo e(ucfirst($reg->registration_status)); ?></span></td>
            <td><span class="badge-pill badge-payment-<?php echo e($reg->payment_status); ?>"><?php echo e(ucfirst($reg->payment_status)); ?></span></td>
            <td class="text-end">
                <a href="<?php echo e(route('my-programs.show', $reg->id)); ?>" class="btn btn-sm btn-light"><i class="icofont icofont-eye"></i></a>
                <a href="<?php echo e(route('my-programs.pdf', $reg->id)); ?>" class="btn btn-sm btn-light"><i class="icofont icofont-download"></i></a>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        </table>
        </div>
        <?php echo e($registrations->links()); ?>

        <?php else: ?>
        <div class="prog-empty"><i class="icofont icofont-listing-box"></i><p class="mb-0">You haven't invited anyone to a program yet.</p></div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/registrations/my.blade.php ENDPATH**/ ?>