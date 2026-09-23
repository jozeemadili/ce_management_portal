<?php
    $children = $byParent->get($church->id, collect());
    $pastor = optional($church->current_head)->member;
    $accent = $designationColors[$church->designation_id] ?? '#2E5AAC';
?>
<li>
    <div class="org-card" data-church-id="<?php echo e($church->id); ?>" style="border-top-color: <?php echo e($accent); ?>">
        <div class="org-card-name"><?php echo e(strtoupper($church->name)); ?></div>
        <span class="org-card-badge" style="color: <?php echo e($accent); ?>; background: <?php echo e($accent); ?>1a">
            <?php echo e(strtoupper($church->church_designation->name ?? '')); ?>

        </span>
        <div class="org-card-head">
            <i class="icofont icofont-crown"></i>
            <?php if($pastor): ?>
                <?php echo e($pastor->first_name); ?> <?php echo e($pastor->last_name); ?>

            <?php else: ?>
                <span class="org-card-nohead">No head assigned</span>
            <?php endif; ?>
        </div>
        <div class="org-card-meta">
            <span><i class="icofont icofont-location-pin"></i> <?php echo e(\Illuminate\Support\Str::limit($church->physical_location, 20)); ?></span>
            <span><i class="icofont icofont-people"></i> <?php echo e($church->members_count ?? 0); ?></span>
        </div>
    </div>

    <?php if($children->count()): ?>
        <ul>
            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('portal.churches.partials.tree-node', ['church' => $child, 'byParent' => $byParent, 'designationColors' => $designationColors], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</li>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/churches/partials/tree-node.blade.php ENDPATH**/ ?>