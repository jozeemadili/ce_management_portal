<div>

    <?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <p class="modal-section-label">Department Details</p>

    <div class="alert alert-info d-flex align-items-center gap-2">
        <i class="icofont icofont-building-alt fs-5"></i>
        <div>
            <strong><?php echo e(strtoupper($church->name)); ?></strong><br>
            <small>Departments created here will automatically belong to your church</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Department Name</label>
            <input type="text" class="form-control" wire:model="name">
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control" wire:model="description">
        </div>
    </div>

    <hr class="my-3">
    <p class="modal-section-label"><i class="icofont icofont-users"></i> Department Members</p>

    <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="row g-2 align-items-center mb-2">

        <div class="col-md-6">
            <select class="form-control" wire:model="members.<?php echo e($index); ?>.member_id">
                <option value="">-- Select Member --</option>
                <?php $__currentLoopData = $churchMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m->id); ?>">
                        <?php echo e($m->first_name); ?> <?php echo e($m->last_name); ?> (<?php echo e($m->phone); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="col-md-4">
            <select class="form-control" wire:model="members.<?php echo e($index); ?>.role">
                <option value="">-- Role --</option>
                <option value="DEPARTMENT_HEAD">Department Head</option>
                <option value="ASSISTANT_HEAD">Assistant Head</option>
                <option value="member">Member</option>
            </select>
        </div>

        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100"
                    wire:click="removeMember(<?php echo e($index); ?>)" title="Remove row">
                <i class="icofont icofont-minus-circle"></i>
            </button>
        </div>

    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php $__errorArgs = ['members'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <small class="text-danger d-block mt-1"><?php echo e($message); ?></small>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" wire:click="addMember">
        <i class="icofont icofont-plus-circle"></i> Add Member
    </button>

    <hr class="my-3">

    <button type="button" class="btn btn-primary" wire:click="save">
        <i class="icofont icofont-check-circled"></i> Save Department
    </button>

</div>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/livewire/department/create-department.blade.php ENDPATH**/ ?>