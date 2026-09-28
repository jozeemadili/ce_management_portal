<?php $__env->startSection('title', 'Service Times'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .times-table th, .times-table td { vertical-align: middle; }
    .time-pair { display: flex; gap: 4px; align-items: center; min-width: 210px; }
    .time-pair input { max-width: 105px; }
    .time-pair input.is-custom { border-color: #4d7de0; background: #eef4ff; }
    .default-time { font-size: .75rem; color: #8a92a6; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Service Times</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('services.checkin')); ?>">
                <i class="icofont icofont-qr-code"></i> Service Check-in
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">Service Times</li>
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

<div class="card prog-card">
<div class="card-body">

<?php if($services->isEmpty()): ?>
    <div class="prog-empty">
        <i class="icofont icofont-building-alt"></i>
        No church services yet. Create a <strong>recurring</strong> program (e.g. "Sunday Service") with scope
        <strong>Global</strong> (every church) or <strong>Church</strong>, frequency <strong>Weekly</strong>, its day(s)
        and a default start/end time, then set each church's own time here.
        <div class="mt-3"><a href="<?php echo e(route('programs.index', ['classification' => 'recurring'])); ?>" class="btn btn-primary btn-sm">Go to Recurring Services</a></div>
    </div>
<?php else: ?>
    <p class="text-muted" style="font-size:.85rem;">
        Each church uses the service's <strong>default time</strong> unless you set its own here. Leave a church's boxes
        empty to use the default. Check-in opens <?php echo e(\App\Services\ChurchServices::OPEN_BEFORE_MINUTES); ?> minutes before the start,
        arriving more than <?php echo e(\App\Services\ChurchServices::LATE_AFTER_MINUTES); ?> minutes after the start counts as <strong>late</strong>,
        and members not checked in by the end are marked <strong>absent</strong>.
    </p>

    <form method="POST" action="<?php echo e(route('services.times.save')); ?>">
        <?php echo csrf_field(); ?>
        <div class="table-responsive">
        <table class="table prog-table times-table">
            <thead>
                <tr>
                    <th>Church</th>
                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th>
                            <?php echo e($service->name); ?><br>
                            <span class="default-time">
                                <?php echo e($service->recurrence_frequency === 'daily' ? 'Every day' : collect($service->recurrence_days)->map(fn ($d) => ucfirst($d))->implode(', ')); ?>

                                &middot; default <?php echo e(substr($service->start_time, 0, 5)); ?>&ndash;<?php echo e(substr($service->end_time, 0, 5)); ?>

                            </span>
                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e(strtoupper($church->name)); ?></strong></td>
                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $o = $overrides->get($service->id . '-' . $church->id); ?>
                        <td>
                            <?php if($service->scope === 'church' && (int) $service->church_id !== (int) $church->id): ?>
                                <span class="text-muted">&mdash;</span>
                            <?php else: ?>
                                <div class="time-pair">
                                    <input type="time" class="form-control form-control-sm <?php echo e($o ? 'is-custom' : ''); ?>"
                                           name="times[<?php echo e($service->id); ?>][<?php echo e($church->id); ?>][start]"
                                           value="<?php echo e($o ? substr($o->start_time, 0, 5) : ''); ?>"
                                           placeholder="<?php echo e(substr($service->start_time, 0, 5)); ?>"
                                           title="Start (default <?php echo e(substr($service->start_time, 0, 5)); ?>)">
                                    <span>&ndash;</span>
                                    <input type="time" class="form-control form-control-sm <?php echo e($o ? 'is-custom' : ''); ?>"
                                           name="times[<?php echo e($service->id); ?>][<?php echo e($church->id); ?>][end]"
                                           value="<?php echo e($o ? substr($o->end_time, 0, 5) : ''); ?>"
                                           placeholder="<?php echo e(substr($service->end_time, 0, 5)); ?>"
                                           title="End (default <?php echo e(substr($service->end_time, 0, 5)); ?>)">
                                </div>
                                <?php if (! ($o)): ?><span class="default-time">Default</span><?php endif; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
        <button class="btn btn-primary"><i class="icofont icofont-save"></i> Save Times</button>
    </form>
<?php endif; ?>

</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/times.blade.php ENDPATH**/ ?>