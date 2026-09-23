
<div class="modal fade" id="<?php echo e($modalId); ?>">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" <?php if($mode === 'edit'): ?> id="editProgramForm" <?php endif; ?> action="<?php echo e($formAction); ?>" enctype="multipart/form-data">
<?php echo csrf_field(); ?>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">
        <i class="icofont icofont-<?php echo e($mode === 'new' ? 'plus-circle' : 'edit'); ?>"></i>
        <?php echo e($mode === 'new' ? 'Create Program' : 'Edit Program'); ?>

    </h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<p class="modal-section-label">Basic Information</p>
<div class="row">
    <div class="col-md-8">
        <label class="form-label">Program Name</label>
        <input type="text" name="name" id="<?php echo e($mode); ?>_name" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" id="<?php echo e($mode); ?>_category" class="form-control" required>
            <?php $__currentLoopData = ['service'=>'Service','meeting'=>'Meeting','program'=>'Program','event'=>'Event','course'=>'Course','crusade'=>'Crusade','conference'=>'Conference','cell_meeting'=>'Cell Meeting','other'=>'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>"><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Description</label>
        <textarea name="description" id="<?php echo e($mode); ?>_description" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Organizer</label>
        <input type="text" name="organizer" id="<?php echo e($mode); ?>_organizer" class="form-control">
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Location</label>
        <input type="text" name="location" id="<?php echo e($mode); ?>_location" class="form-control">
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Banner / Image</label>
        <?php if($mode === 'edit'): ?>
        <div id="edit_banner_preview_wrap" class="mb-2" style="display:none">
            <img id="edit_banner_preview" src="" alt="Current banner" style="max-height:90px;border-radius:8px;">
        </div>
        <?php endif; ?>
        <input type="file" name="banner" accept="image/*" class="form-control">
        <?php if($mode === 'edit'): ?><small class="text-muted">Leave empty to keep the current image.</small><?php endif; ?>
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Classification &amp; Schedule</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Program Type</label>
        <select name="classification" id="<?php echo e($mode); ?>_classification" class="form-control" required>
            <option value="special">Special Event</option>
            <option value="recurring">Recurring</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" id="<?php echo e($mode); ?>_status" class="form-control" required>
            <option value="draft">Draft</option>
            <option value="active" selected>Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Start Time</label>
        <input type="time" name="start_time" id="<?php echo e($mode); ?>_start_time" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">End Time</label>
        <input type="time" name="end_time" id="<?php echo e($mode); ?>_end_time" class="form-control">
    </div>
</div>

<div class="row mt-2" id="<?php echo e($mode); ?>_recurring_wrap">
    <div class="col-md-4 mt-2">
        <label class="form-label">Repeats</label>
        <select name="recurrence_frequency" id="<?php echo e($mode); ?>_frequency" class="form-control">
            <option value="">-- Select --</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="custom">Custom</option>
        </select>
    </div>
    <div class="col-md-4 mt-2 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="qr_enabled" value="1" id="<?php echo e($mode); ?>_qr">
            <label class="form-check-label" for="<?php echo e($mode); ?>_qr">Enable QR/Barcode Check-in</label>
        </div>
    </div>
    <div class="col-md-12 mt-2" id="<?php echo e($mode); ?>_days_wrap">
        <label class="form-label">Repeat On</label>
        <div class="d-flex flex-wrap gap-3">
            <?php $__currentLoopData = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="form-check">
                <input class="form-check-input <?php echo e($mode); ?>-day-check" type="checkbox" name="recurrence_days[]" value="<?php echo e($day); ?>" id="<?php echo e($mode); ?>_day_<?php echo e($day); ?>">
                <label class="form-check-label" for="<?php echo e($mode); ?>_day_<?php echo e($day); ?>"><?php echo e(ucfirst($day)); ?></label>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<div class="row mt-2" id="<?php echo e($mode); ?>_special_wrap">
    <div class="col-md-6 mt-2">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" id="<?php echo e($mode); ?>_start_date" class="form-control">
    </div>
    <div class="col-md-6 mt-2">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" id="<?php echo e($mode); ?>_end_date" class="form-control">
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Church / Branch Scope</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Scope</label>
        <select name="scope" id="<?php echo e($mode); ?>_scope" class="form-control" required>
            <option value="global">Global</option>
            <option value="church" selected>Specific Church</option>
            <option value="department">Specific Department</option>
            <option value="cell">Specific Cell Group</option>
        </select>
    </div>
    <div class="col-md-8" id="<?php echo e($mode); ?>_church_wrap">
        <label class="form-label">Church / Branch</label>
        <select name="church_id" id="<?php echo e($mode); ?>_church" class="form-control">
            <option value="">-- Select Church --</option>
            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($church->id); ?>"><?php echo e(strtoupper($church->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-8" id="<?php echo e($mode); ?>_department_wrap">
        <label class="form-label">Department</label>
        <select name="department_id" id="<?php echo e($mode); ?>_department" class="form-control">
            <option value="">-- Select Department --</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-8" id="<?php echo e($mode); ?>_cell_wrap">
        <label class="form-label">Cell Group</label>
        <select name="cell_group_id" id="<?php echo e($mode); ?>_cell" class="form-control">
            <option value="">-- Select Cell Group --</option>
            <?php $__currentLoopData = $cellGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($cell->id); ?>"><?php echo e($cell->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Program Access</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Access</label>
        <select name="access_type" id="<?php echo e($mode); ?>_access" class="form-control" required>
            <option value="free">Free</option>
            <option value="paid">Paid</option>
        </select>
    </div>
    <div class="col-md-4" id="<?php echo e($mode); ?>_fee_wrap">
        <label class="form-label">Registration Fee</label>
        <input type="number" step="0.01" min="0" name="registration_fee" id="<?php echo e($mode); ?>_fee" class="form-control">
    </div>
    <div class="col-md-4" id="<?php echo e($mode); ?>_currency_wrap2">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" id="<?php echo e($mode); ?>_currency" class="form-control" value="TZS">
    </div>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary"><?php echo e($mode === 'new' ? 'Create Program' : 'Save Changes'); ?></button>
</div>

</form>
</div>
</div>
</div>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/partials/program-form-modal.blade.php ENDPATH**/ ?>