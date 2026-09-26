<?php $__env->startSection('title', 'Registration Report'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .badge-type-new_soul { background: #fff4e0; color: #8a5300; }
    .badge-type-member { background: #e6effd; color: #1f4a94; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Registration Report</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-success" href="<?php echo e(route('program-reports.registrations.export', request()->query())); ?>">
                <i class="icofont icofont-download"></i> Export Excel
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('program-reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Registrations</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="card prog-card mb-3">
    <div class="card-body">
        <form method="GET" class="prog-filter-bar row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Program</label>
                <select name="program_id" class="form-control">
                    <option value="">-- All --</option>
                    <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php if(request('program_id')==$p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Attendee Type</label>
                <select name="attendee_type" class="form-control">
                    <option value="">-- All --</option>
                    <option value="new_soul" <?php if(request('attendee_type')==='new_soul'): echo 'selected'; endif; ?>>First-time visitors</option>
                    <option value="member" <?php if(request('attendee_type')==='member'): echo 'selected'; endif; ?>>Members</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All --</option>
                    <option value="registered" <?php if(request('status')==='registered'): echo 'selected'; endif; ?>>Registered</option>
                    <option value="cancelled" <?php if(request('status')==='cancelled'): echo 'selected'; endif; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment</label>
                <select name="payment_status" class="form-control">
                    <option value="">-- All --</option>
                    <?php $__currentLoopData = ['free'=>'Free','paid'=>'Paid','pending'=>'Pending','failed'=>'Failed','refunded'=>'Refunded']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php if(request('payment_status')===$val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="form-control">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100"><i class="icofont icofont-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card prog-card">
    <div class="card-body">
        <?php if($registrations->count()): ?>
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Reference</th><th>Attendee</th><th>Type</th><th>Church</th><th>Program</th><th>Invited By</th><th>Status</th><th>Payment</th><th>Registered</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="fw-semibold"><?php echo e($reg->registration_reference); ?></td>
            <td>
                <?php echo e(optional($reg->member)->first_name); ?> <?php echo e(optional($reg->member)->last_name); ?>

                <?php if(optional($reg->member)->phone): ?><div class="text-muted small"><?php echo e($reg->member->phone); ?></div><?php endif; ?>
            </td>
            <td><span class="badge-pill badge-type-<?php echo e(optional($reg->member)->member_type === 'new_soul' ? 'new_soul' : 'member'); ?>"><?php echo e($reg->attendeeTypeLabel()); ?></span></td>
            <td><?php echo e(optional(optional($reg->member)->church)->name ?? '—'); ?></td>
            <td><?php echo e(optional($reg->program)->name); ?></td>
            <td><?php echo e($reg->invitedByLabel()); ?></td>
            <td><span class="badge-pill badge-status-<?php echo e($reg->registration_status); ?>"><?php echo e(ucfirst($reg->registration_status)); ?></span></td>
            <td><span class="badge-pill badge-payment-<?php echo e($reg->payment_status); ?>"><?php echo e(ucfirst($reg->payment_status)); ?></span></td>
            <td><?php echo e(optional($reg->registered_at)->format('d M Y')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        </table>
        </div>
        <?php echo e($registrations->links()); ?>

        <?php else: ?>
        <div class="prog-empty"><i class="icofont icofont-ticket"></i><p class="mb-0">No registrations match these filters.</p></div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/reports/registrations.blade.php ENDPATH**/ ?>