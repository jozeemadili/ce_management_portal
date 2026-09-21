<?php $__env->startSection('title', 'Pledges Dashboard'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Pledges Dashboard</h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('pledge-campaigns.index')); ?>">
                <i class="icofont icofont-bullseye"></i> Campaigns
            </a>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Dashboard</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-2"><i class="icofont icofont-flag"></i></div>
                <div><p class="pledge-stat-value"><?php echo e($stats['active_campaigns']); ?></p><p class="pledge-stat-label">Active Campaigns</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-1"><i class="icofont icofont-coins"></i></div>
                <div><p class="pledge-stat-value"><?php echo e(number_format($stats['total_pledged'])); ?></p><p class="pledge-stat-label">Total Pledged</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-6"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="pledge-stat-value"><?php echo e(number_format($stats['total_fulfilled'])); ?></p><p class="pledge-stat-label">Total Fulfilled</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-5"><i class="icofont icofont-warning"></i></div>
                <div><p class="pledge-stat-value"><?php echo e(number_format($stats['total_outstanding'])); ?></p><p class="pledge-stat-label">Total Outstanding</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-3"><i class="icofont icofont-people"></i></div>
                <div><p class="pledge-stat-value"><?php echo e($stats['total_pledgers']); ?></p><p class="pledge-stat-label">Total Pledgers</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-4"><i class="icofont icofont-clock-time"></i></div>
                <div><p class="pledge-stat-value"><?php echo e($stats['today_pledges']); ?></p><p class="pledge-stat-label">Today's Pledges</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-7"><i class="icofont icofont-money-bag"></i></div>
                <div><p class="pledge-stat-value"><?php echo e(number_format($stats['today_amount'])); ?></p><p class="pledge-stat-label">Today's Pledged Amount</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Pledge Growth (last 30 days)</p>
                <canvas id="growthChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Fulfilled vs Outstanding</p>
                <canvas id="fulfillmentChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Pledgers Over Time</p>
                <canvas id="pledgersChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Campaign Performance</p>
                <canvas id="performanceChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card pledge-card">
            <div class="card-body">
                <p class="modal-section-label mb-2">Recent Pledges</p>
                <?php if($recentPledges->count()): ?>
                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead>
                <tr><th>Reference</th><th>Campaign</th><th>Pledger</th><th class="text-end">Amount</th><th>Status</th><th>When</th></tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $recentPledges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($p->pledge_reference); ?></td>
                    <td><?php echo e(optional($p->campaign)->name); ?></td>
                    <td><?php echo e($p->displayName()); ?></td>
                    <td class="text-end"><?php echo e(optional($p->campaign)->currency); ?> <?php echo e(number_format($p->amount, 2)); ?></td>
                    <td><span class="badge-pill badge-status-<?php echo e($p->status); ?>"><?php echo e(ucfirst(str_replace('_',' ',$p->status))); ?></span></td>
                    <td><?php echo e($p->created_at->diffForHumans()); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="pledge-empty">
                    <i class="icofont icofont-listing-box"></i>
                    <p class="mb-0">No pledges recorded yet.</p>
                </div>
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
const growthLabels = <?php echo json_encode($growth->keys(), 15, 512) ?>;
const growthData = <?php echo json_encode($growth->values(), 15, 512) ?>;
const pledgersLabels = <?php echo json_encode($pledgersOverTime->keys(), 15, 512) ?>;
const pledgersData = <?php echo json_encode($pledgersOverTime->values(), 15, 512) ?>;
const perfLabels = <?php echo json_encode($campaignPerformance->pluck('name'), 15, 512) ?>;
const perfData = <?php echo json_encode($campaignPerformance->pluck('percent'), 15, 512) ?>;

new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: { labels: growthLabels, datasets: [{ label: 'Pledged', data: growthData, borderColor: '#2e5aac', backgroundColor: 'rgba(46,90,172,0.1)', tension: 0.35, fill: true }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('fulfillmentChart'), {
    type: 'doughnut',
    data: {
        labels: ['Fulfilled', 'Outstanding'],
        datasets: [{ data: [<?php echo e($totalFulfilled); ?>, <?php echo e(max(0, $totalPledged - $totalFulfilled)); ?>], backgroundColor: ['#1fa971', '#e04b4b'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('pledgersChart'), {
    type: 'bar',
    data: { labels: pledgersLabels, datasets: [{ label: 'Pledgers', data: pledgersData, backgroundColor: '#a855f7' }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('performanceChart'), {
    type: 'bar',
    data: { labels: perfLabels, datasets: [{ label: 'Completion %', data: perfData, backgroundColor: '#4d7de0' }] },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 100 } } }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/pledges/dashboard.blade.php ENDPATH**/ ?>