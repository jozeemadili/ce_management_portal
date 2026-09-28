<?php $__env->startSection('title', 'Check-in'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Check-in</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">Check-in</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-6">

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
<?php endif; ?>

<div class="card prog-card">
<div class="card-body text-center">
    <h4 class="mb-1"><?php echo e(trim($member->first_name . ' ' . $member->last_name)); ?></h4>
    <p class="text-muted mb-3"><?php echo e(optional($member->church)->name); ?></p>

    <?php if($error): ?>
        <div class="alert alert-warning mb-3"><?php echo e($error); ?></div>
    <?php elseif($already): ?>
        <div class="alert alert-warning mb-3">Already checked in at <?php echo e(optional($attendance->checked_in_at)->format('H:i')); ?> for <?php echo e($occurrence->program->name); ?>.</div>
    <?php else: ?>
        <div class="alert alert-success mb-3">
            <strong>Checked in<?php echo e($attendance->attendance_status === 'late' ? ' (late)' : ''); ?></strong>
            at <?php echo e(optional($attendance->checked_in_at)->format('H:i')); ?> &middot; <?php echo e($occurrence->program->name); ?>, <?php echo e(strtoupper($church->name)); ?>

        </div>
    <?php endif; ?>

    <?php if($occurrence && !$error): ?>
        <p class="mb-2">Did <?php echo e($member->first_name); ?> come with new souls?</p>
        <form method="POST" action="<?php echo e(route('services.new-souls', $occurrence->id)); ?>" class="text-start">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="member_id" value="<?php echo e($member->id); ?>">
            <?php for($i = 0; $i < 3; $i++): ?>
                <div class="row g-2 mb-2">
                    <div class="col-6"><input class="form-control form-control-sm" name="people[<?php echo e($i); ?>][first_name]" placeholder="First name<?php echo e($i === 0 ? ' *' : ''); ?>" <?php echo e($i === 0 ? 'required' : ''); ?>></div>
                    <div class="col-6"><input class="form-control form-control-sm" name="people[<?php echo e($i); ?>][last_name]" placeholder="Last name"></div>
                    <div class="col-7"><input class="form-control form-control-sm" name="people[<?php echo e($i); ?>][phone]" placeholder="Phone"></div>
                    <div class="col-5">
                        <select class="form-select form-select-sm" name="people[<?php echo e($i); ?>][gender]">
                            <option value="">Gender</option><option value="male">Male</option><option value="female">Female</option>
                        </select>
                    </div>
                </div>
            <?php endfor; ?>
            <small class="text-muted d-block mb-2">Fill one row per person; leave the other rows empty.</small>
            <button class="btn btn-success w-100"><i class="icofont icofont-plus-circle"></i> Save new souls</button>
        </form>
    <?php endif; ?>

    <a href="<?php echo e(route('services.checkin')); ?>" class="btn btn-light w-100 mt-3">Back to Service Check-in</a>
</div>
</div>

</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/scan-result.blade.php ENDPATH**/ ?>