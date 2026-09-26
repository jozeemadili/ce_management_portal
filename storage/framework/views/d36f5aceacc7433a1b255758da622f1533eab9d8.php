
<?php $canReview = $canReview ?? false; ?>
<?php if($payments->count()): ?>
<div class="table-responsive">
<table class="table table-sm align-middle mb-0">
    <thead class="table-light">
        <tr><th>Date</th><th class="text-end">Amount</th><th>Method / Reference</th><th>Status</th><th>Proof</th><?php if($canReview): ?><th></th><?php endif; ?></tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $payments->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($pay->payment_date->format('d M Y')); ?></td>
            <td class="text-end fw-semibold"><?php echo e($currency); ?> <?php echo e(number_format($pay->amount)); ?></td>
            <td>
                <?php echo e($pay->payment_method ?? '—'); ?>

                <?php if($pay->payment_reference): ?><div class="text-muted small"><?php echo e($pay->payment_reference); ?></div><?php endif; ?>
            </td>
            <td>
                <span class="badge-pill <?php echo e($pay->status === 'confirmed' ? 'badge-payment-paid' : ($pay->status === 'pending' ? 'badge-payment-pending' : 'badge-payment-failed')); ?>"><?php echo e($pay->statusLabel()); ?></span>
                <?php if($pay->status === 'rejected' && $pay->review_note): ?><div class="text-danger small mt-1"><?php echo e($pay->review_note); ?></div><?php endif; ?>
            </td>
            <td>
                <?php if($pay->proof_path): ?>
                    <a href="<?php echo e(route('program-payments.proof', $pay->id)); ?>" target="_blank" rel="noopener"><i class="icofont icofont-attachment"></i> View</a>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <?php if($canReview): ?>
            <td class="text-end" style="min-width: 210px;">
                <?php if($pay->isPending()): ?>
                <form method="POST" action="<?php echo e(route('program-payments.confirm', $pay->id)); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-sm btn-success"><i class="icofont icofont-check"></i> Confirm</button>
                </form>
                <form method="POST" action="<?php echo e(route('program-payments.reject', $pay->id)); ?>" class="d-inline-flex gap-1 mt-1">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="review_note" class="form-control form-control-sm" placeholder="Reason" required style="width: 110px;">
                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                </form>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
</div>
<?php else: ?>
<p class="text-muted mb-0">No payments yet.</p>
<?php endif; ?>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/partials/payment-list.blade.php ENDPATH**/ ?>