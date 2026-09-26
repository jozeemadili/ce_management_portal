<?php $__env->startSection('title', 'Registration Confirmed'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Registration Confirmed</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('my-programs.browse')); ?>">
                <i class="icofont icofont-plus-circle"></i> Register Another
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('my-programs.browse')); ?>">Browse Programs</a></li>
    <li class="breadcrumb-item active">Confirmation</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="row justify-content-center">
    <div class="col-lg-7 mb-3">
        <div class="card prog-card">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-2 mx-auto mb-3" style="width:64px;height:64px;font-size:30px;">
                    <i class="icofont icofont-check-circled"></i>
                </div>
                <h5 class="mb-1">Registration Successful</h5>
                <p class="text-muted mb-4"><?php echo e(optional($registration->member)->first_name); ?> <?php echo e(optional($registration->member)->last_name); ?> is registered for <?php echo e($program->name); ?>.</p>

                <span class="badge-pill badge-status-<?php echo e($registration->registration_status); ?>"><?php echo e(ucfirst($registration->registration_status)); ?></span>
                <span class="badge-pill badge-payment-<?php echo e($registration->payment_status); ?>"><?php echo e(ucfirst($registration->payment_status)); ?></span>

                <div class="d-flex justify-content-between py-2 border-bottom mt-3 text-start">
                    <span class="text-muted">Reference</span>
                    <strong><?php echo e($registration->registration_reference); ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-start">
                    <span class="text-muted">Church</span>
                    <strong><?php echo e(optional(optional($registration->member)->church)->name ?? '—'); ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-start">
                    <span class="text-muted">Date</span>
                    <strong><?php echo e(optional($program->start_date)->format('d M Y') ?? '—'); ?></strong>
                </div>
                <?php if($program->sessions->isNotEmpty()): ?>
                <div class="d-flex justify-content-between py-2 border-bottom text-start">
                    <span class="text-muted">Sessions (daily)</span>
                    <strong class="text-end"><?php echo e($program->sessionsLabel()); ?></strong>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between py-2 text-start">
                    <span class="text-muted">Amount Due</span>
                    <strong><?php echo e($registration->amountDueLabel()); ?></strong>
                </div>
                <?php if((float) $registration->amount_due > 0): ?>
                <div class="alert alert-warning mt-3 mb-0 text-start small">
                    <i class="icofont icofont-info-circle"></i> Payment of <?php echo e($program->currency); ?> <?php echo e(number_format($registration->balance())); ?> is needed before check-in.
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2 justify-content-center mt-4 flex-wrap">
                    <a href="<?php echo e(route('my-programs.show', $registration->id)); ?>" class="btn btn-primary">
                        <i class="icofont icofont-eye"></i> View Registration
                    </a>
                    <a href="<?php echo e(route('my-programs.pdf', $registration->id)); ?>" class="btn btn-outline-primary">
                        <i class="icofont icofont-download"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-secondary" id="copyPdfLinkBtn" data-url="<?php echo e(route('my-programs.show', $registration->id)); ?>">
                        <i class="icofont icofont-link"></i> <span id="copyPdfLinkLabel">Copy Invitation Link</span>
                    </button>
                    <a href="<?php echo e(route('my-programs.browse')); ?>" class="btn btn-outline-primary">
                        <i class="icofont icofont-plus-circle"></i> Register Another
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('copyPdfLinkBtn').addEventListener('click', function () {
    var url = this.getAttribute('data-url');
    var label = document.getElementById('copyPdfLinkLabel');
    navigator.clipboard.writeText(url).then(function () {
        label.textContent = 'Link Copied!';
        setTimeout(function () { label.textContent = 'Copy Invitation Link'; }, 2000);
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/registrations/confirmation.blade.php ENDPATH**/ ?>