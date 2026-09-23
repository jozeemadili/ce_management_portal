<?php $__env->startSection('title', 'Programs'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Programs</h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-success" href="<?php echo e(route('programs.export', request()->query())); ?>">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProgramModal">
                New Program <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item">Programs &amp; Attendance</li>
    <li class="breadcrumb-item active">Programs</li>
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
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['total']); ?></p><p class="prog-stat-label">Total Programs</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['active']); ?></p><p class="prog-stat-label">Active</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-refresh"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['recurring']); ?></p><p class="prog-stat-label">Recurring</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-ticket"></i></div>
                <div><p class="prog-stat-value"><?php echo e($stats['special']); ?></p><p class="prog-stat-label">Special Events</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card prog-card">
<div class="card-body">

<form method="GET" action="<?php echo e(route('programs.index')); ?>" class="prog-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-3">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Program name">
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Classification</label>
        <select name="classification" class="form-control">
            <option value="">All</option>
            <option value="recurring" <?php if(request('classification')=='recurring'): echo 'selected'; endif; ?>>Recurring</option>
            <option value="special" <?php if(request('classification')=='special'): echo 'selected'; endif; ?>>Special</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All</option>
            <?php $__currentLoopData = ['draft','active','completed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php if(request('status')==$s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label mb-1">Access</label>
        <select name="access_type" class="form-control">
            <option value="">All</option>
            <option value="free" <?php if(request('access_type')=='free'): echo 'selected'; endif; ?>>Free</option>
            <option value="paid" <?php if(request('access_type')=='paid'): echo 'selected'; endif; ?>>Paid</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i></button>
        <?php if(request()->anyFilled(['q','classification','status','access_type'])): ?>
        <a href="<?php echo e(route('programs.index')); ?>" class="btn btn-outline-secondary"><i class="icofont icofont-refresh"></i></a>
        <?php endif; ?>
    </div>
</div>
</form>

<?php if($programs->count()): ?>
<div class="row">
<?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card program-card">
        <div class="program-card-banner" <?php if($program->banner_path): ?> style="background-image:url('<?php echo e(asset('storage/'.$program->banner_path)); ?>');background-size:cover;background-position:center;" <?php endif; ?>>
            <?php if (! ($program->banner_path)): ?>
                <i class="icofont icofont-<?php echo e($program->classification === 'recurring' ? 'refresh' : 'ticket'); ?>"></i>
            <?php endif; ?>
        </div>
        <div class="program-card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="program-card-title"><?php echo e($program->name); ?></h5>
                <span class="badge-pill badge-access-<?php echo e($program->access_type); ?>"><?php echo e($program->access_type === 'free' ? 'FREE' : 'PAID'); ?></span>
            </div>
            <div class="d-flex gap-2">
                <span class="badge-pill badge-class-<?php echo e($program->classification); ?>"><?php echo e(ucfirst($program->classification)); ?></span>
                <span class="badge-pill badge-status-<?php echo e($program->status); ?>"><?php echo e(ucfirst($program->status)); ?></span>
            </div>
            <p class="program-card-desc"><?php echo e(\Illuminate\Support\Str::limit($program->description ?? '', 80) ?: 'No description provided.'); ?></p>

            <div class="program-card-meta">
                <span><i class="icofont icofont-location-pin"></i> <?php echo e($program->location ?? '—'); ?></span>
                <span><i class="icofont icofont-people"></i> <?php echo e($program->registrations_count ?? 0); ?> reg.</span>
            </div>
            <?php if($program->classification === 'recurring'): ?>
            <div class="program-card-meta">
                <span><i class="icofont icofont-refresh"></i> <?php echo e(ucfirst($program->recurrence_frequency ?? '—')); ?><?php if($program->recurrence_days): ?> &middot; <?php echo e(collect($program->recurrence_days)->map(fn($d)=>ucfirst($d))->implode(', ')); ?> <?php endif; ?></span>
            </div>
            <?php else: ?>
            <div class="program-card-meta">
                <span><i class="icofont icofont-calendar"></i> <?php echo e(optional($program->start_date)->format('d M Y')); ?></span>
                <?php if($program->access_type === 'paid'): ?>
                <span><?php echo e($program->currency); ?> <?php echo e(number_format($program->registration_fee)); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-2">
                <a href="<?php echo e(route('programs.show', $program->id)); ?>" class="btn btn-primary btn-sm flex-fill">
                    <i class="icofont icofont-eye"></i> View
                </a>
                <button class="btn btn-outline-secondary btn-sm edit-program-btn"
                    data-bs-toggle="modal" data-bs-target="#editProgramModal"
                    data-id="<?php echo e($program->id); ?>"
                    data-name="<?php echo e($program->name); ?>"
                    data-description="<?php echo e($program->description); ?>"
                    data-category="<?php echo e($program->category); ?>"
                    data-classification="<?php echo e($program->classification); ?>"
                    data-scope="<?php echo e($program->scope); ?>"
                    data-church="<?php echo e($program->church_id); ?>"
                    data-department="<?php echo e($program->department_id); ?>"
                    data-cell="<?php echo e($program->cell_group_id); ?>"
                    data-organizer="<?php echo e($program->organizer); ?>"
                    data-location="<?php echo e($program->location); ?>"
                    data-start-date="<?php echo e(optional($program->start_date)->format('Y-m-d')); ?>"
                    data-end-date="<?php echo e(optional($program->end_date)->format('Y-m-d')); ?>"
                    data-start-time="<?php echo e($program->start_time); ?>"
                    data-end-time="<?php echo e($program->end_time); ?>"
                    data-frequency="<?php echo e($program->recurrence_frequency); ?>"
                    data-days='<?php echo json_encode($program->recurrence_days ?? [], 15, 512) ?>'
                    data-access="<?php echo e($program->access_type); ?>"
                    data-fee="<?php echo e($program->registration_fee); ?>"
                    data-currency="<?php echo e($program->currency); ?>"
                    data-status="<?php echo e($program->status); ?>"
                    data-qr="<?php echo e($program->qr_enabled ? 1 : 0); ?>"
                    data-banner="<?php echo e($program->banner_path ? asset('storage/'.$program->banner_path) : ''); ?>"
                    title="Edit">
                    <i class="icofont icofont-edit"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php echo e($programs->links()); ?>

<?php else: ?>
<div class="prog-empty">
    <i class="icofont icofont-calendar"></i>
    <p class="mb-0">No programs found <?php if(request()->anyFilled(['q','classification','status','access_type'])): ?> for the selected filters <?php endif; ?>.</p>
</div>
<?php endif; ?>

</div>
</div>
</div>
</div>

</div>

<?php echo $__env->make('portal.programs.partials.program-form-modal', ['modalId' => 'newProgramModal', 'formAction' => route('programs.store'), 'mode' => 'new'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('portal.programs.partials.program-form-modal', ['modalId' => 'editProgramModal', 'formAction' => '', 'mode' => 'edit'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function progToggle(triggerId, wrapId, testFn) {
    const trigger = document.getElementById(triggerId);
    const wrap = document.getElementById(wrapId);
    function update() { wrap.style.display = testFn(trigger.value) ? '' : 'none'; }
    trigger.addEventListener('change', update);
    update();
}

['new', 'edit'].forEach(prefix => {
    progToggle(`${prefix}_classification`, `${prefix}_recurring_wrap`, v => v === 'recurring');
    progToggle(`${prefix}_classification`, `${prefix}_special_wrap`, v => v === 'special');
    progToggle(`${prefix}_frequency`, `${prefix}_days_wrap`, v => v === 'weekly');
    progToggle(`${prefix}_access`, `${prefix}_fee_wrap`, v => v === 'paid');
    progToggle(`${prefix}_access`, `${prefix}_currency_wrap2`, v => v === 'paid');
    progToggle(`${prefix}_scope`, `${prefix}_church_wrap`, v => v === 'church');
    progToggle(`${prefix}_scope`, `${prefix}_department_wrap`, v => v === 'department');
    progToggle(`${prefix}_scope`, `${prefix}_cell_wrap`, v => v === 'cell');
});

document.querySelectorAll('.edit-program-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        document.getElementById('editProgramForm').action = `/v1/programs/${id}/update`;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_description').value = this.dataset.description || '';
        document.getElementById('edit_category').value = this.dataset.category;
        document.getElementById('edit_classification').value = this.dataset.classification;
        document.getElementById('edit_scope').value = this.dataset.scope;
        document.getElementById('edit_church').value = this.dataset.church || '';
        document.getElementById('edit_department').value = this.dataset.department || '';
        document.getElementById('edit_cell').value = this.dataset.cell || '';
        document.getElementById('edit_organizer').value = this.dataset.organizer || '';
        document.getElementById('edit_location').value = this.dataset.location || '';
        document.getElementById('edit_start_date').value = this.dataset.startDate || '';
        document.getElementById('edit_end_date').value = this.dataset.endDate || '';
        document.getElementById('edit_start_time').value = this.dataset.startTime || '';
        document.getElementById('edit_end_time').value = this.dataset.endTime || '';
        document.getElementById('edit_frequency').value = this.dataset.frequency || '';
        document.getElementById('edit_access').value = this.dataset.access;
        document.getElementById('edit_fee').value = this.dataset.fee;
        document.getElementById('edit_currency').value = this.dataset.currency;
        document.getElementById('edit_status').value = this.dataset.status;
        document.getElementById('edit_qr').checked = this.dataset.qr === '1';

        let days = [];
        try { days = JSON.parse(this.dataset.days || '[]'); } catch (e) {}
        document.querySelectorAll('.edit-day-check').forEach(cb => { cb.checked = days.includes(cb.value); });

        // Trigger visibility toggles
        ['classification', 'frequency', 'access', 'scope'].forEach(f => {
            document.getElementById(`edit_${f}`).dispatchEvent(new Event('change'));
        });

        const previewWrap = document.getElementById('edit_banner_preview_wrap');
        const preview = document.getElementById('edit_banner_preview');
        if (this.dataset.banner) {
            preview.src = this.dataset.banner;
            previewWrap.style.display = '';
        } else {
            previewWrap.style.display = 'none';
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/index.blade.php ENDPATH**/ ?>