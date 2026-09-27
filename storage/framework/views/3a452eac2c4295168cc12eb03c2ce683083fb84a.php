<?php $__env->startSection('title', 'Program Payments'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Program Payments</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs</li>
    <li class="breadcrumb-item active">Payments to Confirm</li>
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

<ul class="nav nav-pills mb-3">
    <?php $__currentLoopData = ['pending' => 'Awaiting Confirmation', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e($status === $key ? 'active' : ''); ?>" href="<?php echo e(route('program-payments.index', ['status' => $key])); ?>">
            <?php echo e($label); ?> <?php if($key === 'pending' && $pendingCount): ?><span class="badge bg-warning text-dark ms-1"><?php echo e($pendingCount); ?></span><?php endif; ?>
        </a>
    </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>

<div class="card prog-card">
    <div class="card-body">
        <?php if($status === 'pending'): ?>
        <p class="text-muted small">
            Payments submitted by attendees with a proof of payment. Open the proof, check it against your records
            (e.g. the M-Pesa statement), then confirm it - or reject it with a reason so they can submit again.
            Only confirmed payments count as paid and allow check-in.
        </p>
        <?php endif; ?>

        <?php if($payments->count()): ?>
        <div class="table-responsive">
        <table class="table prog-table align-middle">
            <thead>
                <tr>
                    <th>Registration</th><th>Attendee</th><th>Program</th><th class="text-end">Amount</th>
                    <th>Method / Reference</th><th>Proof</th>
                    <th><?php echo e($status === 'pending' ? 'Submitted' : 'Reviewed'); ?></th>
                    <?php if($status === 'pending'): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $reg = $pay->registration; ?>
                <tr>
                    <td>
                        <a href="<?php echo e(route('programs.show', $reg->program_id)); ?>" class="fw-semibold"><?php echo e($reg->registration_reference); ?></a>
                        <div class="text-muted small">
                            Due <?php echo e(number_format($reg->amount_due)); ?> &middot; confirmed <?php echo e(number_format($reg->totalPaid())); ?>

                        </div>
                    </td>
                    <td>
                        <?php echo e(optional($reg->member)->first_name); ?> <?php echo e(optional($reg->member)->last_name); ?>

                        <div class="text-muted small"><?php echo e(optional(optional($reg->member)->church)->name); ?></div>
                    </td>
                    <td><?php echo e(optional($reg->program)->name); ?></td>
                    <td class="text-end fw-semibold"><?php echo e(optional($reg->program)->currency); ?> <?php echo e(number_format($pay->amount)); ?></td>
                    <td>
                        <?php echo e($pay->payment_method ?? '—'); ?>

                        <?php if($pay->payment_reference): ?><div class="text-muted small"><?php echo e($pay->payment_reference); ?></div><?php endif; ?>
                        <div class="text-muted small">Paid <?php echo e($pay->payment_date->format('d M Y')); ?></div>
                    </td>
                    <td>
                        <?php if($pay->proof_path): ?>
                            <a href="<?php echo e(route('program-payments.proof', $pay->id)); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light">
                                <i class="icofont icofont-attachment"></i> View Proof
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($status === 'pending'): ?>
                            <?php echo e(trim(optional($pay->recorder)->first_name . ' ' . optional($pay->recorder)->last_name) ?: '—'); ?>

                            <div class="text-muted small"><?php echo e($pay->created_at->format('d M Y, H:i')); ?></div>
                        <?php else: ?>
                            <?php echo e(trim(optional($pay->reviewer)->first_name . ' ' . optional($pay->reviewer)->last_name) ?: '—'); ?>

                            <div class="text-muted small"><?php echo e(optional($pay->reviewed_at)->format('d M Y, H:i')); ?></div>
                            <?php if($pay->review_note): ?><div class="text-danger small"><?php echo e($pay->review_note); ?></div><?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <?php if($status === 'pending'): ?>
                    <td class="text-end" style="min-width: 230px;">
                        <form method="POST" action="<?php echo e(route('program-payments.confirm', $pay->id)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-success"><i class="icofont icofont-check"></i> Confirm</button>
                        </form>
                        <form method="POST" action="<?php echo e(route('program-payments.reject', $pay->id)); ?>" class="d-inline-flex gap-1 mt-1">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="review_note" class="form-control form-control-sm" placeholder="Reason" required style="width: 120px;" aria-label="Reason for rejecting">
                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
        <?php echo e($payments->links()); ?>

        <?php else: ?>
        <div class="prog-empty"><i class="icofont icofont-money"></i><p class="mb-0">
            <?php echo e($status === 'pending' ? 'No payments are waiting for confirmation.' : 'Nothing here yet.'); ?>

        </p></div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/payments/index.blade.php ENDPATH**/ ?>