<?php $__env->startSection('title', 'Service Report'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .rep-count { background: #f7f9fc; border-radius: 12px; padding: 14px; text-align: center; }
    .rep-count .n { font-size: 1.6rem; font-weight: 800; line-height: 1.1; }
    .rep-count .l { font-size: .72rem; color: #8a92a6; text-transform: uppercase; letter-spacing: .03em; }
    .rep-closed { background: #e6f7ee; color: #0f9d58; border-radius: 10px; padding: 10px 14px; }
    .rep-open { background: #fff4e5; color: #92400e; border-radius: 10px; padding: 10px 14px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Service Report</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li><a class="btn btn-outline-primary" href="<?php echo e(route('services.dashboard', ['church' => $occurrence->church_id])); ?>"><i class="icofont icofont-chart-bar-graph"></i> Dashboard</a></li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Service Report</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if($errors->any()): ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e($error); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo e(session('success')); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card prog-card mb-3">
<div class="card-body">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-0"><?php echo e($occurrence->program->name); ?></h4>
            <div class="text-muted">
                <?php echo e(strtoupper($occurrence->church->name)); ?> &middot; <?php echo e($occurrence->occurrence_date->format('l d M Y')); ?>

                &middot; <?php echo e(substr($occurrence->start_time, 0, 5)); ?>&ndash;<?php echo e(substr($occurrence->end_time, 0, 5)); ?>

                <?php if($occurrence->program->is_training): ?><span class="badge-pill ms-1" style="background:#fff4e5;color:#b45309;">TRAINING</span><?php endif; ?>
            </div>
            <?php if($occurrence->church->physical_location): ?><div class="text-muted" style="font-size:.85rem;"><i class="icofont icofont-location-pin"></i> <?php echo e($occurrence->church->physical_location); ?></div><?php endif; ?>
        </div>
        <div>
            <?php if($occurrence->isReportClosed()): ?>
                <div class="rep-closed">
                    <i class="icofont icofont-lock"></i> Report closed <?php echo e($occurrence->report_closed_at->format('d M Y H:i')); ?>

                    by <?php echo e(optional($occurrence->reportClosedBy)->first_name ?? '—'); ?>

                    <form method="POST" action="<?php echo e(route('services.report.reopen', $occurrence->id)); ?>" class="d-inline" onsubmit="return confirm('Reopen this report? The numbers will be live again until you close it.')">
                        <?php echo csrf_field(); ?> <button class="btn btn-sm btn-link p-0 ms-2">Reopen</button>
                    </form>
                </div>
            <?php elseif(!$occurrence->isClosed()): ?>
                <div class="rep-open"><i class="icofont icofont-clock-time"></i> Service still open for check-in &mdash; close the report after it ends.</div>
            <?php else: ?>
                <?php $unassigned = $inviteeList->filter(fn ($m) => $m->assignments->isEmpty())->count(); ?>
                <form method="POST" action="<?php echo e(route('services.report.close', $occurrence->id)); ?>"
                      onsubmit="return confirm('<?php echo e($unassigned ? $unassigned . ' new invitee(s) are not assigned to a church yet. ' : ''); ?>Close the report and freeze these numbers?')">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-success"><i class="icofont icofont-lock"></i> Close report</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-2">
        <?php $__currentLoopData = [['Attended', $counts['attended'] ?? 0], ['On time', $counts['present'] ?? 0], ['Late', $counts['late'] ?? 0], ['Absent', $occurrence->isClosed() ? ($counts['absent'] ?? 0) : '—'], ['New souls', $counts['new_souls'] ?? 0]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6 col-md"><div class="rep-count"><div class="n"><?php echo e($value); ?></div><div class="l"><?php echo e($label); ?></div></div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php if($occurrence->isReportClosed() && !empty($counts['invitees_by_church'])): ?>
        <p class="text-muted mt-2 mb-0" style="font-size:.85rem;">
            New invitees by church:
            <?php $__currentLoopData = $counts['invitees_by_church']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <strong><?php echo e($name); ?></strong> <?php echo e($n); ?><?php if(!$loop->last): ?>, <?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </p>
    <?php endif; ?>
</div>
</div>

<div class="card prog-card mb-3">
<div class="card-body">
    <p class="modal-section-label">New invitees (<?php echo e($inviteeList->count()); ?>)</p>
    <p class="text-muted" style="font-size:.85rem;">
        First-time visitors at this service. Assign each one to the church nearest where they live &mdash; that church follows
        them up under <a href="<?php echo e(route('invitees.index')); ?>">Church Setup &rarr; New Invitees</a>. Every assignment is kept in the history.
    </p>
    <?php if($inviteeList->isEmpty()): ?>
        <div class="prog-empty py-3"><i class="icofont icofont-heart-alt"></i>No new invitees at this service.</div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th>Name</th><th>Contact</th><th>Lives in</th><th>Invited by</th><th>Church (assign)</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $inviteeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <strong><?php echo e(trim($invitee->first_name . ' ' . $invitee->last_name)); ?></strong>
                    <div class="text-muted" style="font-size:.75rem;"><?php echo e(ucfirst($invitee->gender ?? '')); ?><?php echo e($invitee->member_type === 'member' ? ' · now a member' : ''); ?></div>
                </td>
                <td style="font-size:.85rem;"><?php echo e($invitee->phone ?: '—'); ?><br><?php echo e($invitee->email ?: ''); ?></td>
                <td><?php echo e($invitee->location ?: '—'); ?></td>
                <td><?php echo e(optional($invitee->invitedByMember)->first_name ? trim($invitee->invitedByMember->first_name . ' ' . $invitee->invitedByMember->last_name) : ($invitee->invited_by ?: '—')); ?></td>
                <td><?php echo $__env->make('portal.churches.invitees.partials.assign', ['invitee' => $invitee, 'occurrence' => $occurrence], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</div>

<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Check-ins (<?php echo e($checkIns->count()); ?>)</p>
    <?php if($checkIns->isEmpty()): ?>
        <div class="prog-empty py-3"><i class="icofont icofont-users-alt-4"></i>Nobody checked in.</div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th>Time</th><th>Name</th><th>Status</th><th>Program &amp; date</th><th>Lives in</th><th>Phone</th><th>Email</th><th>Method</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $checkIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e(optional($a->checked_in_at)->format('H:i')); ?></td>
                <td><?php echo e(trim(optional($a->member)->first_name . ' ' . optional($a->member)->last_name)); ?>

                    <?php if(optional($a->member)->member_type === 'new_soul'): ?><span class="badge-pill badge-status-new ms-1">New soul</span><?php endif; ?></td>
                <td><span class="badge-pill badge-status-<?php echo e($a->attendance_status); ?>"><?php echo e(ucfirst($a->attendance_status)); ?></span></td>
                <td><?php echo e($occurrence->program->name); ?>, <?php echo e($occurrence->occurrence_date->format('d M Y')); ?></td>
                <td><?php echo e(optional($a->member)->location ?: '—'); ?></td>
                <td><?php echo e(optional($a->member)->phone ?: '—'); ?></td>
                <td><?php echo e(optional($a->member)->email ?: '—'); ?></td>
                <td><?php echo e($a->check_in_method === 'qr' ? 'QR' : 'Manual'); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/report.blade.php ENDPATH**/ ?>