<?php $__env->startSection('title', 'Programs Dashboard'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; font-size: .82rem; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #4d7de0; margin-top: 6px; flex-shrink: 0; }
    .today-program-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f2f7; }
    .today-program-row:last-child { border-bottom: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Programs Dashboard</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('programs.index')); ?>">
                <i class="icofont icofont-listing-box"></i> All Programs
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="row mb-3">
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['today_programs']); ?></p><p class="prog-stat-label">Today's Programs</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['live_attendance']); ?></p><p class="prog-stat-label">Present Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-badge"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['new_souls_today']); ?></p><p class="prog-stat-label">New Souls Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-4"><i class="icofont icofont-ticket"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['registrations_today']); ?></p><p class="prog-stat-label">Registrations Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-5"><i class="icofont icofont-refresh"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['active_programs']); ?></p><p class="prog-stat-label">Active Programs</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-listing-box"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['total_programs']); ?></p><p class="prog-stat-label">Total Programs</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="card prog-card">
            <div class="card-body">
                <p class="modal-section-label">7-Day Attendance Trend</p>
                <canvas id="trendChart" height="110"></canvas>
            </div>
        </div>

        <div class="card prog-card mt-3">
            <div class="card-body">
                <p class="modal-section-label">Happening Today</p>
                <?php $__empty_1 = true; $__currentLoopData = $todayPrograms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="today-program-row">
                    <div>
                        <a href="<?php echo e(route('programs.show', $p->id)); ?>" class="fw-semibold text-dark"><?php echo e($p->name); ?></a>
                        <div class="text-muted" style="font-size:.78rem">
                            <i class="icofont icofont-location-pin"></i> <?php echo e($p->location ?? '—'); ?>

                            <?php if($p->start_time): ?> &middot; <?php echo e(\Illuminate\Support\Carbon::parse($p->start_time)->format('H:i')); ?> <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge-pill badge-class-<?php echo e($p->classification); ?>"><?php echo e(ucfirst($p->classification)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">Nothing scheduled today.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-3">
        <div class="card prog-card mb-3">
            <div class="card-body">
                <p class="modal-section-label">Upcoming Programs</p>
                <?php $__empty_1 = true; $__currentLoopData = $upcomingPrograms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="today-program-row">
                    <div>
                        <a href="<?php echo e(route('programs.show', $p->id)); ?>" class="fw-semibold text-dark"><?php echo e($p->name); ?></a>
                        <div class="text-muted" style="font-size:.78rem"><?php echo e(optional($p->start_date)->format('d M Y')); ?></div>
                    </div>
                    <span class="badge-pill badge-access-<?php echo e($p->access_type); ?>"><?php echo e($p->access_type === 'free' ? 'FREE' : 'PAID'); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">No upcoming special programs.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card prog-card">
            <div class="card-body">
                <p class="modal-section-label">Recent Activity</p>
                <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                        <span><?php echo e(str_replace('_',' ',str_replace('.',' ',$a->action))); ?></span>
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/chart/chartjs/chart.min.js')); ?>"></script>
<script>
const trendLabels = <?php echo json_encode($trend->keys(), 15, 512) ?>;
const trendData = <?php echo json_encode($trend->values(), 15, 512) ?>;

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: trendLabels, datasets: [{ label: 'Present', data: trendData, borderColor: '#2e5aac', backgroundColor: 'rgba(46,90,172,0.1)', tension: 0.35, fill: true }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/dashboard.blade.php ENDPATH**/ ?>