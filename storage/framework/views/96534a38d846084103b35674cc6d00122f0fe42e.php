<?php $__env->startSection('title', 'Member Titles'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Member Titles</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Setup</li>
    <li class="breadcrumb-item active">Member Titles</li>
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
<div class="col-lg-8 mb-3">
<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Titles</p>
    <p class="text-muted" style="font-size:.85rem;">Shown in the Title dropdown when registering or editing a member, and accepted in the Title column of the Excel upload. Lower order numbers appear first. Switch a title off to hide it without changing members who have it.</p>
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th style="width:90px;">Order</th><th>Title</th><th>Members</th><th>On</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $titles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="4" class="p-0">
                    <form method="POST" action="<?php echo e(route('member-titles.update', $t->id)); ?>" class="d-flex gap-2 align-items-center flex-wrap py-2" id="titleForm<?php echo e($t->id); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="number" name="sort_order" value="<?php echo e($t->sort_order); ?>" class="form-control form-control-sm" style="width:80px;" min="0">
                        <input name="name" value="<?php echo e($t->name); ?>" class="form-control form-control-sm" style="max-width:220px;" required maxlength="50">
                        <span class="text-muted" style="min-width:90px;font-size:.85rem;"><?php echo e($t->members_count); ?> member(s)</span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php if($t->is_active): echo 'checked'; endif; ?>>
                        </div>
                    </form>
                </td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <button class="btn btn-sm btn-primary" form="titleForm<?php echo e($t->id); ?>">Save</button>
                        <form method="POST" action="<?php echo e(route('member-titles.destroy', $t->id)); ?>" onsubmit="return confirm('Delete the title <?php echo e(addslashes($t->name)); ?>?')">
                            <?php echo csrf_field(); ?> <button class="btn btn-sm btn-light text-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    </div>
</div>
</div>
</div>

<div class="col-lg-4 mb-3">
<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Add a title</p>
    <form method="POST" action="<?php echo e(route('member-titles.store')); ?>">
        <?php echo csrf_field(); ?>
        <label class="form-label">Title</label>
        <input name="name" class="form-control mb-2" placeholder="e.g. Minister" required maxlength="50">
        <label class="form-label">Order (optional)</label>
        <input type="number" name="sort_order" class="form-control mb-3" min="0" placeholder="Added at the end">
        <button class="btn btn-primary w-100"><i class="icofont icofont-plus-circle"></i> Add title</button>
    </form>
</div>
</div>
</div>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/churches/titles/index.blade.php ENDPATH**/ ?>