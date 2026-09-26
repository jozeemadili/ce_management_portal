<?php $__env->startSection('title', $registration->registration_reference); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3><?php echo e($registration->registration_reference); ?></h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs</li>
    <li class="breadcrumb-item active">Scanned Registration</li>
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

<?php if(session('error')): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <?php echo e(session('error')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-6 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Registration Details</p>

                <h5 class="mb-1"><?php echo e(optional($registration->member)->first_name); ?> <?php echo e(optional($registration->member)->last_name); ?></h5>
                <p class="text-muted mb-1"><?php echo e(optional($registration->program)->name); ?></p>
                <p class="text-muted mb-3"><i class="icofont icofont-building-alt"></i> <?php echo e(optional(optional($registration->member)->church)->name ?? '—'); ?></p>

                <span class="badge-pill badge-status-<?php echo e($registration->registration_status); ?> mb-3 d-inline-block"><?php echo e(ucfirst($registration->registration_status)); ?></span>
                <span class="badge-pill badge-payment-<?php echo e($registration->payment_status); ?> mb-3 d-inline-block"><?php echo e($registration->paymentLabel()); ?></span>

                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Reference</span>
                    <strong><?php echo e($registration->registration_reference); ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Location</span>
                    <strong><?php echo e(optional($registration->program)->location ?? '—'); ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Date</span>
                    <strong><?php echo e(optional(optional($registration->program)->start_date)->format('d M Y') ?? '—'); ?></strong>
                </div>
                <?php if($session): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Session now</span>
                    <strong><?php echo e($session->name); ?> (<?php echo e($session->timeRange()); ?>)</strong>
                </div>
                <?php elseif($registration->program && $registration->program->sessions->isNotEmpty()): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Sessions</span>
                    <strong class="text-end"><?php echo e($registration->program->sessionsLabel()); ?></strong>
                </div>
                <?php endif; ?>
                <?php if((float) $registration->amount_due > 0): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Amount Due<?php echo e($registration->pricedDesignation ? ' (' . ucwords($registration->pricedDesignation->name) . ')' : ''); ?></span>
                    <strong><?php echo e($registration->program->currency); ?> <?php echo e(number_format($registration->amount_due)); ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Paid / Balance</span>
                    <strong><?php echo e(number_format($registration->totalPaid())); ?> / <?php echo e(number_format($registration->balance())); ?></strong>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Registered On</span>
                    <strong><?php echo e(optional($registration->registered_at)->format('d M Y')); ?></strong>
                </div>

                <hr class="my-3">

                <?php if($ok): ?>
                <form method="POST" action="<?php echo e(route('program-scan.check-in', $registration->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-primary w-100">
                        <i class="icofont icofont-check-circled"></i> Confirm Check-In
                    </button>
                </form>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="icofont icofont-warning"></i> <?php echo e($message); ?>

                </div>
                <?php endif; ?>

                <?php if(!$registration->isSettled() && $registration->registration_status === 'registered'): ?>
                
                <form method="POST" action="<?php echo e(route('program-payments.store', $registration->id)); ?>" class="mt-3 border rounded p-3">
                    <?php echo csrf_field(); ?>
                    <p class="modal-section-label mb-2">Record Payment</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="1" name="amount" class="form-control" value="<?php echo e($registration->balance() ?: ''); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">-- Select --</option>
                                <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($method->name); ?>"><?php echo e($method->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Reference</label>
                            <input type="text" name="payment_reference" class="form-control" placeholder="e.g. M-Pesa code">
                        </div>
                    </div>
                    <button class="btn btn-success w-100 mt-3"><i class="icofont icofont-money"></i> Record Payment</button>
                </form>
                <?php endif; ?>

                <?php if($registration->attendance): ?>
                <div class="alert alert-success mt-3 mb-0">
                    <i class="icofont icofont-check-circled"></i> Last check-in <?php echo e(optional($registration->attendance->checked_in_at)->format('d M Y, H:i')); ?><?php if($registration->attendance->session): ?> &middot; <?php echo e($registration->attendance->session->name); ?><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/scan/show.blade.php ENDPATH**/ ?>