<?php $__env->startSection('title', $registration->registration_reference); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .reg-hero {
        position: relative; border-radius: 16px 16px 0 0; overflow: hidden;
        min-height: 180px; display: flex; align-items: flex-end; padding: 24px;
        background: linear-gradient(135deg,#2e5aac,#4d7de0);
    }
    .reg-hero.has-banner { background-size: cover; background-position: center; }
    .reg-hero-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(10,20,42,.15) 0%, rgba(8,16,34,.75) 100%);
    }
    .reg-hero-content { position: relative; z-index: 1; color: #fff; }
    .reg-hero-content h4 { margin-bottom: 4px; }
    .reg-hero-content p { margin: 0; opacity: .9; font-size: .88rem; }
    .reg-qr-box { text-align: center; padding: 20px; border-left: 1px solid #f0f2f7; }
    .reg-qr-box img { max-width: 160px; }
    .reg-qr-box p { font-size: .76rem; color: #9aa2b1; margin-top: 10px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3><?php echo e($registration->registration_reference); ?></h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('my-programs.pdf', $registration->id)); ?>">
                <i class="icofont icofont-download"></i> Download PDF
            </a>
        </li>
        <li>
            <button type="button" class="btn btn-outline-secondary" id="copyPageLinkBtn" data-url="<?php echo e(route('my-programs.show', $registration->id)); ?>">
                <i class="icofont icofont-link"></i> <span id="copyPageLinkLabel">Copy Link</span>
            </button>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('my-programs.index')); ?>">My Registrations</a></li>
    <li class="breadcrumb-item active"><?php echo e($registration->registration_reference); ?></li>
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

<div class="row justify-content-center">
    <div class="col-lg-9 mb-3">
        <div class="card prog-card overflow-hidden">

            <div class="reg-hero <?php if($bannerUrl): ?> has-banner <?php endif; ?>" <?php if($bannerUrl): ?> style="background-image:url('<?php echo e($bannerUrl); ?>');" <?php endif; ?>>
                <?php if($bannerUrl): ?><div class="reg-hero-overlay"></div><?php endif; ?>
                <div class="reg-hero-content">
                    <h4><?php echo e(optional($registration->program)->name); ?></h4>
                    <p><i class="icofont icofont-location-pin"></i> <?php echo e(optional($registration->program)->location ?? '—'); ?></p>
                </div>
            </div>

            <div class="row g-0">
                <div class="col-md-8">
                    <div class="card-body">
                        <span class="badge-pill badge-status-<?php echo e($registration->registration_status); ?>"><?php echo e(ucfirst($registration->registration_status)); ?></span>
                        <span class="badge-pill badge-payment-<?php echo e($registration->payment_status); ?>"><?php echo e(ucfirst($registration->payment_status)); ?></span>

                        <div class="d-flex justify-content-between py-2 border-bottom mt-3">
                            <span class="text-muted">Reference</span>
                            <strong><?php echo e($registration->registration_reference); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Attendee</span>
                            <strong><?php echo e(optional($registration->member)->first_name); ?> <?php echo e(optional($registration->member)->last_name); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Church</span>
                            <strong><?php echo e(optional(optional($registration->member)->church)->name ?? '—'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Start Date</span>
                            <strong><?php echo e(optional(optional($registration->program)->start_date)->format('d M Y') ?? '—'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">End Date</span>
                            <strong><?php echo e(optional(optional($registration->program)->end_date)->format('d M Y') ?? optional(optional($registration->program)->start_date)->format('d M Y') ?? '—'); ?></strong>
                        </div>
                        <?php if(optional($registration->program)->sessions && $registration->program->sessions->isNotEmpty()): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Sessions (daily)</span>
                            <strong class="text-end"><?php $__currentLoopData = $registration->program->sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($session->name); ?> <?php echo e($session->timeRange()); ?><?php if(!$loop->last): ?><br><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Amount Due</span>
                            <strong><?php echo e($registration->amountDueLabel()); ?></strong>
                        </div>
                        <?php if((float) $registration->amount_due > 0): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Paid / Balance</span>
                            <strong><?php echo e(number_format($registration->totalPaid())); ?> / <?php echo e(number_format($registration->balance())); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Registered On</span>
                            <strong><?php echo e(optional($registration->registered_at)->format('d M Y')); ?></strong>
                        </div>

                        <?php if($registration->attendance): ?>
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="icofont icofont-check-circled"></i> Checked in <?php echo e(optional($registration->attendance->checked_in_at)->format('d M Y, H:i')); ?>

                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reg-qr-box h-100 d-flex flex-column justify-content-center">
                        <img src="<?php echo e($qr); ?>" alt="Registration QR code">
                        <p>Scan at the venue to check in</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php if((float) $registration->amount_due > 0): ?>
    
    <div class="col-lg-9 mb-3" id="payments">
        <div class="card prog-card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="mb-0"><i class="icofont icofont-money"></i> Payments</h5>
                    <span class="badge-pill badge-payment-<?php echo e($registration->payment_status); ?>"><?php echo e($registration->paymentLabel()); ?></span>
                </div>
                <div class="row text-center g-2 mb-3">
                    <?php $__currentLoopData = [
                        'Amount Due' => $registration->amount_due,
                        'Confirmed' => $registration->totalPaid(),
                        'Awaiting Confirmation' => $registration->pendingPaymentsTotal(),
                        'Balance' => $registration->balance(),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2">
                            <div class="fw-bold"><?php echo e($registration->program->currency); ?> <?php echo e(number_format($value)); ?></div>
                            <div class="text-muted small"><?php echo e($label); ?></div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($registration->pricedDesignation): ?>
                <p class="text-muted small">Price for <?php echo e(ucwords($registration->pricedDesignation->name)); ?>.</p>
                <?php endif; ?>

                <?php echo $__env->make('portal.programs.partials.payment-list', [
                    'payments' => $registration->payments,
                    'currency' => $registration->program->currency,
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <?php if($registration->registration_status === 'registered' && $registration->payableAmount() > 0): ?>
                <hr>
                <h6 class="mb-1">Submit a Payment</h6>
                <p class="text-muted small">Pay the full balance or part of it, and attach your proof of payment. It counts once the church confirms it.</p>
                <?php echo $__env->make('portal.programs.partials.payment-form', [
                    'action' => route('program-payments.submit', $registration->id),
                    'maxAmount' => $registration->payableAmount(),
                    'currency' => $registration->program->currency,
                    'paymentMethods' => $paymentMethods,
                    'proofRequired' => true,
                    'idPrefix' => 'submit',
                    'submitLabel' => 'Submit Payment',
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php elseif($registration->pendingPaymentsTotal() > 0 && $registration->balance() > 0): ?>
                <div class="alert alert-info mt-3 mb-0">Your payment is waiting for the church to confirm it.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('copyPageLinkBtn').addEventListener('click', function () {
    var url = this.getAttribute('data-url');
    var label = document.getElementById('copyPageLinkLabel');
    navigator.clipboard.writeText(url).then(function () {
        label.textContent = 'Link Copied!';
        setTimeout(function () { label.textContent = 'Copy Link'; }, 2000);
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/registrations/detail.blade.php ENDPATH**/ ?>