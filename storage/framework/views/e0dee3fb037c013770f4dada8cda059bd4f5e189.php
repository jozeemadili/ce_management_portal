<?php $__env->startSection('title', $program->name); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; font-size: .82rem; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #4d7de0; margin-top: 6px; flex-shrink: 0; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3><?php echo e($program->name); ?></h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <?php if($program->classification === 'recurring' && in_array($program->scope, ['global', 'church'])): ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('services.checkin', ['service' => $program->id])); ?>">
                <i class="icofont icofont-qr-code"></i> Service Check-in
            </a>
        </li>
        <?php elseif(Route::has('program-attendance.capture')): ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('program-attendance.capture', $program->id)); ?>">
                <i class="icofont icofont-check-circled"></i> Take Attendance
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a class="btn btn-outline-secondary" href="<?php echo e(route('programs.checkin-poster', $program->id)); ?>" target="_blank">
                <i class="icofont icofont-print"></i> Check-in QR Poster
            </a>
        </li>
        <?php if($program->classification === 'special'): ?>
        <li>
            <?php $smsCount = $registrations->where('registration_status', 'registered')->count(); ?>
            <form method="POST" action="<?php echo e(route('program-sms.remind', $program->id)); ?>"
                  onsubmit="return confirm('Send the reminder SMS to <?php echo e($smsCount); ?> registered <?php echo e($smsCount === 1 ? 'person' : 'people'); ?>? Each SMS uses credit.')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-success" <?php if($smsCount === 0): echo 'disabled'; endif; ?>>
                    <i class="icofont icofont-ui-message"></i> Send Reminder SMS
                </button>
            </form>
        </li>
        <?php endif; ?>
        <?php if($program->access_type === 'paid'): ?>
        <li>
            <a class="btn btn-outline-warning" href="<?php echo e(route('program-payments.index')); ?>">
                <i class="icofont icofont-money"></i> Payments to Confirm
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('programs.index')); ?>">
                <i class="icofont icofont-listing-box"></i> All Programs
            </a>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item"><a href="<?php echo e(route('programs.index')); ?>">Programs</a></li>
    <li class="breadcrumb-item active"><?php echo e($program->name); ?></li>
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

<div class="row mb-3">
    <div class="col-12">
        <div class="card prog-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="badge-pill badge-status-<?php echo e($program->status); ?>"><?php echo e(ucfirst($program->status)); ?></span>
                    <span class="badge-pill badge-class-<?php echo e($program->classification); ?>"><?php echo e(ucfirst($program->classification)); ?></span>
                    <span class="badge-pill badge-access-<?php echo e($program->access_type); ?>"><?php echo e($program->access_type === 'free' ? 'FREE' : 'PAID'); ?></span>
                    <p class="text-muted mb-0 mt-2">
                        <i class="icofont icofont-location-pin"></i> <?php echo e($program->location ?? '—'); ?>

                        <?php if($program->classification === 'special'): ?>
                            &middot; <i class="icofont icofont-calendar"></i> <?php echo e(optional($program->start_date)->format('d M Y')); ?>

                            <?php if($program->end_date && !$program->end_date->equalTo($program->start_date)): ?> &ndash; <?php echo e($program->end_date->format('d M Y')); ?> <?php endif; ?>
                        <?php else: ?>
                            &middot; <i class="icofont icofont-refresh"></i> <?php echo e(ucfirst($program->recurrence_frequency ?? '')); ?>

                            <?php if($program->recurrence_days): ?> (<?php echo e(collect($program->recurrence_days)->map(fn($d)=>ucfirst($d))->implode(', ')); ?>) <?php endif; ?>
                        <?php endif; ?>
                        <?php if($program->access_type === 'paid'): ?>
                            &middot; <?php echo e($program->accessLabel()); ?>

                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-people"></i></div>
                <div><p class="prog-stat-value"><?php echo e($attendanceStats['registered']); ?></p><p class="prog-stat-label">Registered</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value"><?php echo e($attendanceStats['attended']); ?></p><p class="prog-stat-label">Attended</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-5"><i class="icofont icofont-close-circled"></i></div>
                <div><p class="prog-stat-value"><?php echo e($attendanceStats['absent']); ?></p><p class="prog-stat-label">Absent</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-4"><i class="icofont icofont-bullseye"></i></div>
                <div><p class="prog-stat-value"><?php echo e($attendanceStats['rate']); ?>%</p><p class="prog-stat-label">Attendance Rate</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-badge"></i></div>
                <div><p class="prog-stat-value"><?php echo e($attendanceStats['new_souls']); ?></p><p class="prog-stat-label">New Souls</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-money-bag"></i></div>
                <div>
                    <p class="prog-stat-value"><?php echo e($program->currency); ?> <?php echo e(number_format($revenue)); ?></p>
                    <p class="prog-stat-label">Collected <?php if($outstanding > 0): ?>&middot; <?php echo e(number_format($outstanding)); ?> due <?php endif; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card prog-card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="programTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview">Overview</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-registrations">Registrations</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-occurrences">Occurrences</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">Activity</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="tab-overview">
                        <?php if($program->description): ?>
                        <p><?php echo e($program->description); ?></p>
                        <?php else: ?>
                        <p class="text-muted">No description provided.</p>
                        <?php endif; ?>
                        <table class="table prog-table align-middle">
                            <tr><td class="text-muted" width="220">Organizer</td><td><?php echo e($program->organizer ?? '—'); ?></td></tr>
                            <tr><td class="text-muted">Contact Phone</td><td><?php if($program->contact_phone): ?><a href="tel:<?php echo e($program->contact_phone); ?>"><?php echo e($program->contact_phone); ?></a><?php else: ?> — <?php endif; ?></td></tr>
                            <tr><td class="text-muted"><?php echo e($program->scope === 'church' && count($program->churchIds()) > 1 ? 'Churches' : 'Church'); ?></td><td><?php echo e(in_array($program->scope, ['global', 'church']) ? $program->churchNames() : '—'); ?></td></tr>
                            <tr><td class="text-muted">Department</td><td><?php echo e(optional($program->department)->name ?? '—'); ?></td></tr>
                            <tr><td class="text-muted">Cell Group</td><td><?php echo e(optional($program->cellGroup)->name ?? '—'); ?></td></tr>
                            <tr><td class="text-muted">QR/Barcode Check-in</td><td><?php echo e($program->qrAvailable() ? 'Enabled (per session)' : 'Not enabled'); ?></td></tr>
                            <tr>
                                <td class="text-muted">Sessions (each day)</td>
                                <td>
                                    <?php $__empty_1 = true; $__currentLoopData = $program->sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div><strong><?php echo e($session->name); ?></strong> &middot; <?php echo e($session->timeRange()); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if($program->access_type === 'paid'): ?>
                            <tr>
                                <td class="text-muted">Price per Group</td>
                                <td>
                                    <?php $__currentLoopData = $program->designationPrices->sortBy('designation_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div><?php echo e(ucwords(optional($price->designation)->name)); ?>: <strong><?php echo e($program->currency); ?> <?php echo e(number_format($price->amount)); ?></strong></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <small class="text-muted">Most senior group applies; no group (e.g. first-time visitors) = free.</small>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="tab-registrations">
                        <?php if($registrations->count()): ?>
                        <div class="table-responsive">
                        <table class="table prog-table align-middle">
                        <thead><tr><th>Reference</th><th>Member</th><th>Status</th><th>Payment</th><?php if($program->access_type === 'paid'): ?><th class="text-end">Due</th><th class="text-end">Paid</th><th class="text-end">Balance</th><?php endif; ?><th>Registered</th><?php if($program->access_type === 'paid'): ?><th></th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="fw-semibold"><?php echo e($reg->registration_reference); ?></td>
                            <td><?php echo e(optional($reg->member)->first_name); ?> <?php echo e(optional($reg->member)->last_name); ?></td>
                            <td><span class="badge-pill badge-status-<?php echo e($reg->registration_status); ?>"><?php echo e(ucfirst($reg->registration_status)); ?></span></td>
                            <td><span class="badge-pill badge-payment-<?php echo e($reg->payment_status); ?>"><?php echo e($reg->paymentLabel()); ?></span></td>
                            <?php if($program->access_type === 'paid'): ?>
                            <td class="text-end">
                                <?php echo e(number_format($reg->amount_due ?? 0)); ?>

                                <?php if($reg->pricedDesignation): ?><div class="text-muted small"><?php echo e(ucwords($reg->pricedDesignation->name)); ?></div><?php endif; ?>
                            </td>
                            <td class="text-end"><?php echo e(number_format($reg->totalPaid())); ?></td>
                            <td class="text-end fw-semibold"><?php echo e(number_format($reg->balance())); ?></td>
                            <?php endif; ?>
                            <td><?php echo e(optional($reg->registered_at)->format('d M Y')); ?></td>
                            <?php if($program->access_type === 'paid'): ?>
                            <td class="text-end">
                                <?php if($reg->pendingPaymentsTotal() > 0): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo e($reg->id); ?>">
                                    <i class="icofont icofont-eye"></i> Review Proof
                                </button>
                                <?php elseif(!$reg->isSettled() && $reg->registration_status === 'registered'): ?>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo e($reg->id); ?>">
                                    <i class="icofont icofont-money"></i> Record Payment
                                </button>
                                <?php elseif($reg->payments->count()): ?>
                                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo e($reg->id); ?>">
                                    <i class="icofont icofont-listing-box"></i> Payments
                                </button>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        </table>
                        </div>
                        <?php else: ?>
                        <div class="prog-empty"><i class="icofont icofont-people"></i><p class="mb-0">No registrations yet.</p></div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="tab-occurrences">
                        <?php if($occurrences->count()): ?>
                        <div class="table-responsive">
                        <table class="table prog-table align-middle">
                        <thead><tr><th>Date</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $occurrences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $occ): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($occ->occurrence_date->format('d M Y')); ?></td>
                            <td><span class="badge-pill badge-status-<?php echo e($occ->status === 'completed' ? 'completed' : 'active'); ?>"><?php echo e(ucfirst($occ->status)); ?></span></td>
                            <td class="text-end">
                                <?php if(Route::has('program-attendance.capture')): ?>
                                <a href="<?php echo e(route('program-attendance.capture', $program->id)); ?>?date=<?php echo e($occ->occurrence_date->format('Y-m-d')); ?>" class="btn btn-sm btn-light">
                                    <i class="icofont icofont-eye"></i> View Attendance
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        </table>
                        </div>
                        <?php else: ?>
                        <div class="prog-empty"><i class="icofont icofont-calendar"></i><p class="mb-0">No occurrences recorded yet.</p></div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="tab-activity">
                        <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div>
                                <?php if($a->action === 'sms.reminder_sent'): ?>
                                    <span>Reminder SMS sent to <?php echo e($a->new_values['sent'] ?? 0); ?> of <?php echo e(($a->new_values['recipients'] ?? 0)); ?> people<?php echo e(!empty($a->new_values['failed']) ? ' (' . $a->new_values['failed'] . ' failed)' : ''); ?><?php echo e(!empty($a->new_values['no_phone']) ? ', ' . $a->new_values['no_phone'] . ' without a phone number' : ''); ?></span>
                                <?php else: ?>
                                <span><?php echo e(str_replace('_',' ',str_replace('program.','',$a->action))); ?></span>
                                <?php endif; ?>
                                by <strong><?php echo e(optional($a->actor)->first_name ?? 'System'); ?></strong>
                                <br><small class="text-muted"><?php echo e($a->created_at->diffForHumans()); ?></small>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0">No activity recorded yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>


<?php if($program->access_type === 'paid'): ?>
<?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if($reg->payments->count() || (!$reg->isSettled() && $reg->registration_status === 'registered')): ?>
<div class="modal fade" id="paymentModal<?php echo e($reg->id); ?>" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content">
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="icofont icofont-money"></i> Payments &middot; <?php echo e($reg->registration_reference); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <p class="mb-3">
            <strong><?php echo e(optional($reg->member)->first_name); ?> <?php echo e(optional($reg->member)->last_name); ?></strong>
            <?php if($reg->pricedDesignation): ?> &middot; <?php echo e(ucwords($reg->pricedDesignation->name)); ?> <?php endif; ?>
            <br>
            <span class="text-muted">Due <?php echo e($program->currency); ?> <?php echo e(number_format($reg->amount_due)); ?>

                &middot; confirmed <?php echo e(number_format($reg->totalPaid())); ?>

                <?php if($reg->pendingPaymentsTotal() > 0): ?> &middot; awaiting <?php echo e(number_format($reg->pendingPaymentsTotal())); ?> <?php endif; ?>
                &middot; balance <strong><?php echo e(number_format($reg->balance())); ?></strong></span>
        </p>

        <?php echo $__env->make('portal.programs.partials.payment-list', [
            'payments' => $reg->payments,
            'currency' => $program->currency,
            'canReview' => true,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php if($reg->registration_status === 'registered' && $reg->payableAmount() > 0): ?>
        <hr>
        <p class="modal-section-label mb-2">Record a Payment</p>
        <?php echo $__env->make('portal.programs.partials.payment-form', [
            'action' => route('program-payments.store', $reg->id),
            'maxAmount' => $reg->payableAmount(),
            'currency' => $program->currency,
            'paymentMethods' => $paymentMethods,
            'proofRequired' => false,
            'idPrefix' => 'pay' . $reg->id,
            'submitLabel' => 'Record Payment',
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
    </div>
</div>
</div>
</div>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/show.blade.php ENDPATH**/ ?>