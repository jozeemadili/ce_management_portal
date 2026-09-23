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
        <?php if(Route::has('program-attendance.capture')): ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('program-attendance.capture', $program->id)); ?>">
                <i class="icofont icofont-check-circled"></i> Take Attendance
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

                        <?php else: ?>
                            &middot; <i class="icofont icofont-refresh"></i> <?php echo e(ucfirst($program->recurrence_frequency ?? '')); ?>

                            <?php if($program->recurrence_days): ?> (<?php echo e(collect($program->recurrence_days)->map(fn($d)=>ucfirst($d))->implode(', ')); ?>) <?php endif; ?>
                        <?php endif; ?>
                        <?php if($program->access_type === 'paid'): ?>
                            &middot; <?php echo e($program->currency); ?> <?php echo e(number_format($program->registration_fee)); ?>

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
                <div><p class="prog-stat-value"><?php echo e($program->currency); ?> <?php echo e(number_format($revenue)); ?></p><p class="prog-stat-label">Revenue</p></div>
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
                            <tr><td class="text-muted">Church</td><td><?php echo e(optional($program->church)->name ?? ($program->scope === 'global' ? 'Global' : '—')); ?></td></tr>
                            <tr><td class="text-muted">Department</td><td><?php echo e(optional($program->department)->name ?? '—'); ?></td></tr>
                            <tr><td class="text-muted">Cell Group</td><td><?php echo e(optional($program->cellGroup)->name ?? '—'); ?></td></tr>
                            <tr><td class="text-muted">QR/Barcode Check-in</td><td><?php echo e($program->qrAvailable() ? 'Enabled' : 'Not enabled'); ?></td></tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="tab-registrations">
                        <?php if($registrations->count()): ?>
                        <div class="table-responsive">
                        <table class="table prog-table align-middle">
                        <thead><tr><th>Reference</th><th>Member</th><th>Status</th><th>Payment</th><th>Registered</th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="fw-semibold"><?php echo e($reg->registration_reference); ?></td>
                            <td><?php echo e(optional($reg->member)->first_name); ?> <?php echo e(optional($reg->member)->last_name); ?></td>
                            <td><span class="badge-pill badge-status-<?php echo e($reg->registration_status); ?>"><?php echo e(ucfirst($reg->registration_status)); ?></span></td>
                            <td><span class="badge-pill badge-payment-<?php echo e($reg->payment_status); ?>"><?php echo e(ucfirst($reg->payment_status)); ?></span></td>
                            <td><?php echo e(optional($reg->registered_at)->format('d M Y')); ?></td>
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
                                <span><?php echo e(str_replace('_',' ',str_replace('program.','',$a->action))); ?></span>
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/show.blade.php ENDPATH**/ ?>