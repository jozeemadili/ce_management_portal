
<form method="POST" action="<?php echo e(route('invitees.assign', $invitee->id)); ?>" class="assign-form">
    <?php echo csrf_field(); ?>
    <?php if(!empty($occurrence)): ?><input type="hidden" name="occurrence_id" value="<?php echo e($occurrence->id); ?>"><?php endif; ?>
    <div class="d-flex gap-1 flex-wrap">
        <select name="church_id" class="form-select form-select-sm" style="min-width:170px;max-width:240px;" required>
            <?php $__currentLoopData = $allChurches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if($c->id === $invitee->church_id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?><?php echo e($c->physical_location ? ' - ' . \Illuminate\Support\Str::limit($c->physical_location, 25) : ''); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input name="note" class="form-control form-control-sm" style="max-width:170px;" placeholder="Note (optional)">
        <button class="btn btn-sm btn-primary"><?php echo e($invitee->assignments->isEmpty() ? 'Assign' : 'Re-assign'); ?></button>
    </div>
</form>
<?php if($invitee->assignments->isNotEmpty()): ?>
    <details class="mt-1">
        <summary class="text-muted" style="font-size:.75rem;cursor:pointer;">History (<?php echo e($invitee->assignments->count()); ?>)</summary>
        <ul class="list-unstyled mb-0 mt-1" style="font-size:.75rem;">
            <?php $__currentLoopData = $invitee->assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="mb-1">
                    <strong><?php echo e(optional($a->created_at)->format('d M Y H:i')); ?></strong>:
                    <?php echo e(optional($a->fromChurch)->name ?? '—'); ?> &rarr; <strong><?php echo e(optional($a->toChurch)->name); ?></strong>
                    by <?php echo e(optional($a->assignedBy)->first_name ?? 'System'); ?>

                    <?php if($a->note): ?><br><em>&ldquo;<?php echo e($a->note); ?>&rdquo;</em><?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </details>
<?php else: ?>
    <div class="text-warning" style="font-size:.75rem;">Not assigned yet</div>
<?php endif; ?>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/churches/invitees/partials/assign.blade.php ENDPATH**/ ?>