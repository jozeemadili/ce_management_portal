<?php $__env->startSection('title', 'Church Services'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .svc-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 0; border-bottom: 1px solid #f0f2f7; }
    .svc-row:last-child { border-bottom: 0; }
    .svc-name { font-weight: 700; }
    .svc-meta { font-size: .8rem; color: #6b7280; }
    .problem { background: #fff8eb; border: 1px solid #fde7b8; border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; }
    .setup-row { background: #f7f9fc; border-radius: 12px; padding: 12px; margin-bottom: 10px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Church Services</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li><a class="btn btn-primary" href="<?php echo e(route('services.checkin')); ?>"><i class="icofont icofont-qr-code"></i> Service Check-in</a></li>
        <li><a class="btn btn-outline-primary" href="<?php echo e(route('programs.index', ['classification' => 'recurring'])); ?>"><i class="icofont icofont-plus-circle"></i> New / Edit Service</a></li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Services</li>
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

<div class="row">
<div class="col-lg-7 mb-3">
    <div class="card prog-card h-100">
    <div class="card-body">
        <p class="modal-section-label">Running services</p>
        <p class="text-muted" style="font-size:.85rem;">
            These open for check-in automatically <?php echo e(\App\Services\ChurchServices::OPEN_BEFORE_MINUTES); ?> minutes before they start
            in each church, and mark absentees when they end.
        </p>
        <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="svc-row">
                <div>
                    <div class="svc-name"><?php echo e($service->name); ?></div>
                    <div class="svc-meta">
                        <i class="icofont icofont-calendar"></i>
                        <?php echo e($service->recurrence_frequency === 'daily' ? 'Every day' : collect($service->recurrence_days)->map(fn ($d) => ucfirst($d))->implode(', ')); ?>

                        &middot; default <?php echo e(substr($service->start_time, 0, 5)); ?>&ndash;<?php echo e(substr($service->end_time, 0, 5)); ?>

                        &middot; <?php echo e($service->scope === 'global' ? 'All churches' : 'One church'); ?>

                        <?php if($customTimes->get($service->id)): ?>
                            &middot; <?php echo e($customTimes->get($service->id)); ?> church(es) with their own time
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-light" href="<?php echo e(route('programs.show', $service->id)); ?>">Details</a>
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('services.times')); ?>">Church times</a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="prog-empty py-4">
                <i class="icofont icofont-building-alt"></i>
                No service is running yet. Use <strong>Quick setup</strong>, or fix the ones listed under <strong>Not running</strong>.
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>

<div class="col-lg-5 mb-3">
    <?php if($notRunning->isNotEmpty()): ?>
    <div class="card prog-card mb-3">
    <div class="card-body">
        <p class="modal-section-label text-warning"><i class="icofont icofont-warning"></i> Not running</p>
        <p class="text-muted" style="font-size:.85rem;">These recurring programs don't open for check-in. Fix the reason and they start running.</p>
        <?php $__currentLoopData = $notRunning; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="problem">
                <strong><?php echo e($row->program->name); ?></strong>
                <div style="font-size:.84rem;" class="mb-2"><?php echo e($row->reason); ?></div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if($row->program->status !== 'active' && str_starts_with($row->reason, 'Status')): ?>
                        <form method="POST" action="<?php echo e(route('programs.status', [$row->program->id, 'active'])); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-success">Activate</button>
                        </form>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('programs.index', ['classification' => 'recurring'])); ?>">Edit in Programs</a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    </div>
    <?php endif; ?>

    <div class="card prog-card mb-3" style="border: 2px dashed #f0b429;">
    <div class="card-body">
        <p class="modal-section-label" style="color:#b45309;"><i class="icofont icofont-graduate-alt"></i> Training mode</p>
        <p class="text-muted" style="font-size:.85rem;">
            A <strong>training service</strong> lets ushers practise any day: it is <strong>open for check-in all day</strong>
            (its start time still decides who is <em>late</em>), <strong>never marks anyone absent</strong>, <strong>sends no SMS</strong>
            and is <strong>left out of the dashboard and reports</strong>. <em>Reset</em> clears what was recorded.
        </p>

        <?php $__currentLoopData = $training; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="setup-row">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <strong><?php echo e($t->name); ?></strong>
                        <span class="badge-pill ms-1" style="background:#fff4e5;color:#b45309;">TRAINING</span>
                        <div class="svc-meta">
                            <?php echo e($t->church ? strtoupper($t->church->name) : 'All churches'); ?>

                            &middot; start <?php echo e(substr($t->start_time, 0, 5)); ?> (late after <?php echo e(\Carbon\Carbon::parse($t->start_time)->addMinutes(\App\Services\ChurchServices::LATE_AFTER_MINUTES)->format('H:i')); ?>)
                            <?php if($t->status !== 'active'): ?> &middot; <?php echo e(ucfirst($t->status)); ?> <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <a class="btn btn-sm btn-primary" href="<?php echo e(route('services.checkin', array_filter(['church' => $t->church_id, 'service' => $t->id]))); ?>">Practise check-in</a>
                    <form method="POST" action="<?php echo e(route('services.training.reset', $t->id)); ?>" onsubmit="return confirm('Remove all check-ins and practice new souls recorded in <?php echo e(addslashes($t->name)); ?>?')">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-sm btn-outline-warning">Reset</button>
                    </form>
                    <form method="POST" action="<?php echo e(route('services.training.remove', $t->id)); ?>" onsubmit="return confirm('Delete <?php echo e(addslashes($t->name)); ?> and everything recorded in it?')">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <form method="POST" action="<?php echo e(route('services.training.create')); ?>" class="mt-2">
            <?php echo csrf_field(); ?>
            <div class="row g-2">
                <div class="col-12"><input class="form-control form-control-sm" name="name" value="<?php echo e(old('name', 'Training Service')); ?>" placeholder="Name" required></div>
                <div class="col-12">
                    <select name="church_id" class="form-select form-select-sm">
                        <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php if((int) old('church_id', optional(Auth::user()->member)->church_id) === $c->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <option value="">All churches</option>
                    </select>
                </div>
                <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Starts (for "late")</label><input type="time" class="form-control form-control-sm" name="start" value="<?php echo e(old('start', now()->format('H:00'))); ?>" required></div>
                <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Ends</label><input type="time" class="form-control form-control-sm" name="end" value="<?php echo e(old('end', '23:00')); ?>" required></div>
            </div>
            <button class="btn btn-warning btn-sm w-100 mt-2"><i class="icofont icofont-plus-circle"></i> Create training service</button>
        </form>
    </div>
    </div>

    <?php if($missing): ?>
    <div class="card prog-card">
    <div class="card-body">
        <p class="modal-section-label">Quick setup</p>
        <p class="text-muted" style="font-size:.85rem;">
            Create the weekly services that don't exist yet, held in <strong>every church</strong>. Set the default time;
            churches with a different time set their own under Service Times.
        </p>
        <form method="POST" action="<?php echo e(route('services.setup')); ?>">
            <?php echo csrf_field(); ?>
            <?php $__currentLoopData = $missing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php [$name, $start, $end] = \App\Services\ChurchServices::STANDARD_SERVICES[$day]; ?>
                <div class="setup-row">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="create[]" value="<?php echo e($day); ?>" id="create_<?php echo e($day); ?>" checked>
                        <label class="form-check-label fw-bold" for="create_<?php echo e($day); ?>"><?php echo e(ucfirst($day)); ?></label>
                    </div>
                    <div class="row g-2">
                        <div class="col-12"><input class="form-control form-control-sm" name="name[<?php echo e($day); ?>]" value="<?php echo e(old('name.' . $day, $name)); ?>" placeholder="Name"></div>
                        <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Starts</label><input type="time" class="form-control form-control-sm" name="start[<?php echo e($day); ?>]" value="<?php echo e(old('start.' . $day, $start)); ?>"></div>
                        <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Ends</label><input type="time" class="form-control form-control-sm" name="end[<?php echo e($day); ?>]" value="<?php echo e(old('end.' . $day, $end)); ?>"></div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button class="btn btn-primary w-100"><i class="icofont icofont-plus-circle"></i> Create ticked services</button>
        </form>
    </div>
    </div>
    <?php endif; ?>
</div>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/services/index.blade.php ENDPATH**/ ?>