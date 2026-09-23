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
                        <?php if(optional($registration->program)->start_time || optional($registration->program)->end_time): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Event Time (Daily)</span>
                            <strong><?php if(optional($registration->program)->start_time): ?><?php echo e(\Illuminate\Support\Carbon::parse($registration->program->start_time)->format('H:i')); ?><?php endif; ?> <?php if(optional($registration->program)->start_time && optional($registration->program)->end_time): ?>&ndash;<?php endif; ?> <?php if(optional($registration->program)->end_time): ?><?php echo e(\Illuminate\Support\Carbon::parse($registration->program->end_time)->format('H:i')); ?><?php endif; ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Access</span>
                            <strong>
                                <?php if(optional($registration->program)->isFree()): ?>FREE
                                <?php else: ?> <?php echo e(optional($registration->program)->currency); ?> <?php echo e(number_format(optional($registration->program)->registration_fee, 2)); ?>

                                <?php endif; ?>
                            </strong>
                        </div>
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

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/registrations/detail.blade.php ENDPATH**/ ?>