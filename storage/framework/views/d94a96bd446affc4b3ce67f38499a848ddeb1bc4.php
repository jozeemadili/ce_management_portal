<?php $__env->startSection('title', 'Programs Open for Registration'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/select2.css')); ?>">
<style>
    .register-church-hint { font-size: .78rem; }
    .register-mode-group .btn { flex: 1; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Programs Open for Registration</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('my-programs.index')); ?>">
                <i class="icofont icofont-listing-box"></i> People I've Invited
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item active">Browse Programs</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($programs->count()): ?>
<div class="row">
    <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $alreadyRegistered = $myRegisteredIds->contains($program->id); ?>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="program-card">
            <div class="program-card-banner" <?php if($program->banner_path): ?> style="background-image:url('<?php echo e(asset('storage/'.$program->banner_path)); ?>');background-size:cover;background-position:center;" <?php endif; ?>>
                <?php if(!$program->banner_path): ?><i class="icofont icofont-calendar"></i><?php endif; ?>
            </div>
            <div class="program-card-body">
                <h5 class="program-card-title"><?php echo e($program->name); ?></h5>
                <p class="program-card-desc"><?php echo e($program->description ? \Illuminate\Support\Str::limit($program->description, 90) : 'No description.'); ?></p>
                <div class="program-card-meta">
                    <span><i class="icofont icofont-location-pin"></i> <?php echo e($program->location ?? '—'); ?></span>
                </div>
                <div class="program-card-meta mt-1">
                    <span><i class="icofont icofont-calendar"></i>
                        <?php echo e(optional($program->start_date)->format('d M Y') ?? '—'); ?>

                        <?php if($program->end_date && !$program->end_date->equalTo($program->start_date)): ?>
                            &ndash; <?php echo e($program->end_date->format('d M Y')); ?>

                        <?php endif; ?>
                    </span>
                </div>
                <?php if($program->start_time || $program->end_time): ?>
                <div class="program-card-meta mt-1">
                    <span><i class="icofont icofont-clock-time"></i> Daily
                        <?php if($program->start_time): ?><?php echo e(\Illuminate\Support\Carbon::parse($program->start_time)->format('H:i')); ?><?php endif; ?>
                        <?php if($program->start_time && $program->end_time): ?> &ndash; <?php endif; ?>
                        <?php if($program->end_time): ?><?php echo e(\Illuminate\Support\Carbon::parse($program->end_time)->format('H:i')); ?><?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                <span class="badge-pill badge-access-<?php echo e($program->access_type); ?> mt-2 d-inline-block">
                    <?php echo e($program->isFree() ? 'FREE' : $program->currency . ' ' . number_format($program->registration_fee)); ?>

                </span>

                <?php if($alreadyRegistered): ?>
                <p class="text-success small mb-1 mt-2"><i class="icofont icofont-check-circled"></i> You're already registered</p>
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal<?php echo e($program->id); ?>">
                    <i class="icofont icofont-plus-circle"></i> Register Someone Else
                </button>
                <?php elseif($currentMember): ?>
                
                <form method="POST" action="<?php echo e(route('my-programs.register', $program->id)); ?>" class="mt-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="member_id" value="<?php echo e($currentMember->id); ?>">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="icofont icofont-plus-circle"></i> Register
                    </button>
                </form>
                <?php else: ?>
                <button type="button" class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#registerModal<?php echo e($program->id); ?>">
                    <i class="icofont icofont-plus-circle"></i> Register
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $alreadyRegistered = $myRegisteredIds->contains($program->id); ?>
<div class="modal fade" id="registerModal<?php echo e($program->id); ?>">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<form method="POST" action="<?php echo e(route('my-programs.register', $program->id)); ?>" class="program-register-form">
<?php echo csrf_field(); ?>
<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Register for <?php echo e($program->name); ?></h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <p class="text-muted small">
        <?php if($alreadyRegistered): ?>
            You're already registered for this program &mdash; register someone else below.
        <?php else: ?>
            Register yourself, or anyone else attending.
        <?php endif; ?>
    </p>

    <label class="form-label">Church</label>
    <select name="church_id" class="form-control register-church-select" required>
        <option value="">-- Select Church --</option>
        <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($church->id); ?>" <?php if(optional($currentMember)->church_id == $church->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($church->name)); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <p class="text-muted mt-1 mb-3 register-church-hint"></p>

    <div class="btn-group register-mode-group w-100 mb-3" role="group">
        <input type="radio" class="btn-check register-mode-radio" name="mode" id="modeExisting<?php echo e($program->id); ?>" value="existing" checked>
        <label class="btn btn-outline-primary btn-sm" for="modeExisting<?php echo e($program->id); ?>">Existing Church Member</label>
        <input type="radio" class="btn-check register-mode-radio" name="mode" id="modeNew<?php echo e($program->id); ?>" value="new">
        <label class="btn btn-outline-primary btn-sm" for="modeNew<?php echo e($program->id); ?>">First-Time Visitor</label>
    </div>

    <div class="register-existing-wrap">
        <label class="form-label">Search Member</label>
        <select class="form-control register-member-select" name="member_id" style="width:100%">
            <option value="">-- Type to search --</option>
        </select>
    </div>

    <div class="register-new-wrap" style="display:none;">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control register-new-input" value="<?php echo e($alreadyRegistered ? '' : optional($currentMember)->first_name); ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control register-new-input" value="<?php echo e($alreadyRegistered ? '' : optional($currentMember)->last_name); ?>" disabled>
            </div>
            <div class="col-md-12 mt-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control register-new-input" value="<?php echo e($alreadyRegistered ? '' : optional($currentMember)->phone); ?>" disabled>
            </div>
        </div>
        <p class="text-muted mt-2 mb-0 register-new-hint" style="font-size:.78rem;"></p>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">Register</button>
</div>
</form>
</div>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php else: ?>
<div class="card prog-card"><div class="card-body prog-empty">
    <i class="icofont icofont-calendar"></i>
    <p class="mb-0">No programs are currently open for registration.</p>
</div></div>
<?php endif; ?>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/select2/select2.full.min.js')); ?>"></script>
<script>
(function () {
    var $ = window.jQuery;
    if (!$) return;

    function updateChurchHint($form) {
        var $sel = $form.find('.register-church-select');
        var text = $sel.find('option:selected').text();
        var hasChurch = !!$sel.val();
        $form.find('.register-church-hint').text(hasChurch ? 'Searching members within ' + text + '.' : '');
        $form.find('.register-new-hint').text(hasChurch ? 'This new soul will belong to ' + text + '.' : 'Select a church above first.');
    }

    function applyMode($form) {
        var mode = $form.find('.register-mode-radio:checked').val();
        var $memberSelect = $form.find('.register-member-select');
        var $newInputs = $form.find('.register-new-input');

        if (mode === 'existing') {
            $form.find('.register-existing-wrap').show();
            $form.find('.register-new-wrap').hide();
            $memberSelect.prop('disabled', false);
            $newInputs.prop('disabled', true);
        } else {
            $form.find('.register-existing-wrap').hide();
            $form.find('.register-new-wrap').show();
            $memberSelect.prop('disabled', true);
            if ($memberSelect.hasClass('select2-hidden-accessible')) {
                $memberSelect.val(null).trigger('change');
            }
            $newInputs.prop('disabled', false);
        }
    }

    $(document).on('change', '.register-mode-radio', function () {
        applyMode($(this).closest('form'));
    });

    $(document).on('change', '.register-church-select', function () {
        var $form = $(this).closest('form');
        updateChurchHint($form);
        var $memberSelect = $form.find('.register-member-select');
        if ($memberSelect.hasClass('select2-hidden-accessible')) {
            $memberSelect.val(null).trigger('change');
        }
    });

    if ($.fn.select2) {
        $('.register-member-select').each(function () {
            var $el = $(this);
            var $modal = $el.closest('.modal');
            var $form = $el.closest('form');
            $el.select2({
                placeholder: 'Search by name or phone...',
                minimumInputLength: 2,
                width: '100%',
                dropdownParent: $modal,
                ajax: {
                    url: '<?php echo e(route('my-programs.search-members')); ?>',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return { q: params.term, church_id: $form.find('.register-church-select').val() };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                }
            });
        });
    }

    $(document).on('shown.bs.modal', '.modal', function () {
        var $form = $(this).find('form.program-register-form');
        if (!$form.length) return;
        updateChurchHint($form);
        applyMode($form);
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/registrations/browse.blade.php ENDPATH**/ ?>