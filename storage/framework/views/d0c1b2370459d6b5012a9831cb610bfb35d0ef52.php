<div>

    <?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form wire:submit.prevent="save">

        <p class="modal-section-label">Personal Details</p>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" wire:model.defer="first_name">
                <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" wire:model.defer="last_name">
                <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" wire:model.defer="email">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" wire:model.defer="phone">
            </div>
        </div>

        <hr class="my-3">
        <p class="modal-section-label"><i class="icofont icofont-building-alt"></i> Church Assignment</p>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Church</label>
                <select class="form-control" wire:model.defer="church_id">
                    <option value="">-- Select Church --</option>
                    <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($church->id); ?>">
                            <?php echo e(strtoupper($church->name)); ?><?php if(optional($church->current_head)->member): ?> (<?php echo e($church->current_head->member->first_name); ?> <?php echo e($church->current_head->member->last_name); ?>) <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <small class="text-muted">The church's current head/pastor is shown in brackets.</small>
                <?php $__errorArgs = ['church_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger d-block"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <hr class="my-3">
        <p class="modal-section-label"><i class="icofont icofont-badge"></i> Member Designation(s)</p>
        <div class="row">
            <div class="col-md-12 mb-2">
                <small class="text-muted d-block mb-2">A member can hold more than one designation — tick all that apply.</small>
                <div class="row">
                    <?php $__currentLoopData = $designations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="designation_<?php echo e($designation->id); ?>"
                                       value="<?php echo e($designation->id); ?>"
                                       wire:model="designation_ids">
                                <label class="form-check-label" for="designation_<?php echo e($designation->id); ?>">
                                    <?php echo e(ucwords($designation->name)); ?>

                                </label>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php $__errorArgs = ['designation_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger d-block"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <button class="btn btn-primary mt-2">
            <i class="icofont icofont-plus-circle"></i> Save Member
        </button>

    </form>
</div>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/livewire/members/create-member.blade.php ENDPATH**/ ?>