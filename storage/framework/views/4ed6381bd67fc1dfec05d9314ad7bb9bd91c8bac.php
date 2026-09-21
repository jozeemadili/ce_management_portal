<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startPush('css'); ?>
<style>
    .events-carousel { border-radius: 16px; overflow: hidden; box-shadow: 0 2px 14px rgba(46,90,172,.1); }
    .events-carousel .carousel-item { height: 340px; position: relative; }
    .events-slide-bg {
        position: absolute; inset: 0; background-size: cover; background-position: center;
        background: linear-gradient(135deg,#2e5aac,#4d7de0);
    }
    .events-slide-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(10,20,42,.15) 0%, rgba(8,16,34,.82) 100%);
    }
    .events-slide-content {
        position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column;
        justify-content: flex-end; padding: 28px 32px; color: #fff;
    }
    .events-slide-content h4 { margin: 0 0 6px; font-weight: 700; }
    .events-slide-content p { margin: 0 0 14px; opacity: .9; font-size: .88rem; }
    .events-countdown { display: flex; gap: 10px; margin-bottom: 16px; }
    .events-countdown .unit { background: rgba(255,255,255,.12); border-radius: 10px; padding: 8px 14px; text-align: center; min-width: 64px; }
    .events-countdown .unit .num { font-size: 1.3rem; font-weight: 700; line-height: 1; }
    .events-countdown .unit .lbl { font-size: .68rem; text-transform: uppercase; opacity: .8; margin-top: 2px; }
    .events-carousel .carousel-indicators { margin-bottom: 4px; }
    .events-empty { border-radius: 16px; padding: 60px 20px; text-align: center; color: #9aa2b1; background: #f7f9fc; }
    .events-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #c8cedb; }

    .pledge-summary-card { border: none; border-radius: 16px; box-shadow: 0 2px 14px rgba(46,90,172,.08); height: 100%; }
    .pledge-summary-stat { text-align: center; padding: 10px 4px; }
    .pledge-summary-stat .val { font-size: 1.15rem; font-weight: 700; color: #2e5aac; }
    .pledge-summary-stat .lbl { font-size: .72rem; color: #8a92a6; text-transform: uppercase; letter-spacing: .03em; }
    .pledge-recent-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f2f7; font-size: .84rem; }
    .pledge-recent-row:last-child { border-bottom: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid dashboard-default-sec">

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="icofont icofont-calendar"></i> Coming Events</h5>
            <a href="<?php echo e(route('my-programs.browse')); ?>" class="btn btn-sm btn-outline-primary">
                <i class="icofont icofont-listing-box"></i> Browse All Programs
            </a>
        </div>

        <?php if($upcomingPrograms->count()): ?>
        <div id="eventsCarousel" class="carousel slide events-carousel" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                <?php $__currentLoopData = $upcomingPrograms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" data-bs-target="#eventsCarousel" data-bs-slide-to="<?php echo e($i); ?>" class="<?php echo e($i === 0 ? 'active' : ''); ?>"></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="carousel-inner">
                <?php $__currentLoopData = $upcomingPrograms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $targetIso = optional($program->start_date)->format('Y-m-d') . 'T' . ($program->start_time ? \Illuminate\Support\Carbon::parse($program->start_time)->format('H:i:s') : '00:00:00');
                ?>
                <div class="carousel-item <?php echo e($i === 0 ? 'active' : ''); ?>">
                    <div class="events-slide-bg" <?php if($program->banner_path): ?> style="background-image:url('<?php echo e(asset('storage/'.$program->banner_path)); ?>');" <?php endif; ?>></div>
                    <div class="events-slide-overlay"></div>
                    <div class="events-slide-content">
                        <h4><?php echo e($program->name); ?></h4>
                        <p><i class="icofont icofont-location-pin"></i> <?php echo e($program->location ?? '—'); ?>

                            &middot; <i class="icofont icofont-calendar"></i> <?php echo e(optional($program->start_date)->format('d M Y')); ?>

                            <?php if($program->start_time): ?> , <?php echo e(\Illuminate\Support\Carbon::parse($program->start_time)->format('H:i')); ?><?php endif; ?>
                        </p>
                        <div class="events-countdown" data-countdown-target="<?php echo e($targetIso); ?>">
                            <div class="unit"><div class="num days">--</div><div class="lbl">Days</div></div>
                            <div class="unit"><div class="num hours">--</div><div class="lbl">Hours</div></div>
                            <div class="unit"><div class="num minutes">--</div><div class="lbl">Mins</div></div>
                            <div class="unit"><div class="num seconds">--</div><div class="lbl">Secs</div></div>
                        </div>
                        <div>
                            <a href="<?php echo e(route('my-programs.browse')); ?>" class="btn btn-primary btn-sm">
                                <i class="icofont icofont-plus-circle"></i> Register
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <?php else: ?>
        <div class="events-empty">
            <i class="icofont icofont-calendar"></i>
            <p class="mb-0">No upcoming events scheduled right now.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if($pledgeSummary): ?>
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card pledge-summary-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="icofont icofont-gift"></i> My Pledge Summary</h6>
                    <a href="<?php echo e(route('my-pledges.index')); ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="row">
                    <div class="col-3 pledge-summary-stat">
                        <div class="val"><?php echo e($pledgeSummary['count']); ?></div>
                        <div class="lbl">Pledges</div>
                    </div>
                    <div class="col-3 pledge-summary-stat">
                        <div class="val"><?php echo e(number_format($pledgeSummary['total_pledged'])); ?></div>
                        <div class="lbl">Pledged</div>
                    </div>
                    <div class="col-3 pledge-summary-stat">
                        <div class="val"><?php echo e(number_format($pledgeSummary['total_fulfilled'])); ?></div>
                        <div class="lbl">Fulfilled</div>
                    </div>
                    <div class="col-3 pledge-summary-stat">
                        <div class="val"><?php echo e(number_format($pledgeSummary['outstanding'])); ?></div>
                        <div class="lbl">Outstanding</div>
                    </div>
                </div>

                <?php if($pledgeSummary['recent']->count()): ?>
                <hr class="my-3">
                <p class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;">Recent Pledges</p>
                <?php $__currentLoopData = $pledgeSummary['recent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pledge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pledge-recent-row">
                    <span><?php echo e(optional($pledge->campaign)->name); ?></span>
                    <strong><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->amount, 2)); ?></strong>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card pledge-summary-card">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center h-100">
                <i class="icofont icofont-plus-circle" style="font-size:36px;color:#2e5aac;"></i>
                <h6 class="mt-2 mb-1">Make a New Pledge</h6>
                <p class="text-muted mb-3" style="font-size:.82rem;">Support an active campaign today.</p>
                <a href="<?php echo e(route('my-pledges.browse')); ?>" class="btn btn-primary btn-sm w-100">Browse Campaigns</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        document.querySelectorAll('[data-countdown-target]').forEach(function (el) {
            var target = new Date(el.getAttribute('data-countdown-target')).getTime();
            var now = new Date().getTime();
            var diff = target - now;

            var days = 0, hours = 0, minutes = 0, seconds = 0;
            if (diff > 0) {
                days = Math.floor(diff / (1000 * 60 * 60 * 24));
                hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                seconds = Math.floor((diff % (1000 * 60)) / 1000);
            }

            el.querySelector('.days').textContent = days;
            el.querySelector('.hours').textContent = pad(hours);
            el.querySelector('.minutes').textContent = pad(minutes);
            el.querySelector('.seconds').textContent = pad(seconds);
        });
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/admin/dashboard/home.blade.php ENDPATH**/ ?>