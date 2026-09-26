
<form method="POST" action="<?php echo e($action); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="row g-2">
        <div class="col-sm-6">
            <label class="form-label" for="<?php echo e($idPrefix); ?>_amount">Amount (<?php echo e($currency); ?>)</label>
            <input type="number" step="0.01" min="1" max="<?php echo e($maxAmount); ?>" name="amount" id="<?php echo e($idPrefix); ?>_amount" class="form-control" value="<?php echo e(old('amount', $maxAmount)); ?>" required>
            <small class="text-muted">Full (<?php echo e(number_format($maxAmount)); ?>) or part of it.</small>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="<?php echo e($idPrefix); ?>_date">Payment Date</label>
            <input type="date" name="payment_date" id="<?php echo e($idPrefix); ?>_date" class="form-control" value="<?php echo e(old('payment_date', now()->toDateString())); ?>" max="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="<?php echo e($idPrefix); ?>_method">Method</label>
            <select name="payment_method" id="<?php echo e($idPrefix); ?>_method" class="form-control">
                <option value="">-- Select --</option>
                <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($method->name); ?>" <?php if(old('payment_method') === $method->name): echo 'selected'; endif; ?>><?php echo e($method->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="<?php echo e($idPrefix); ?>_ref">Reference</label>
            <input type="text" name="payment_reference" id="<?php echo e($idPrefix); ?>_ref" class="form-control" value="<?php echo e(old('payment_reference')); ?>" placeholder="e.g. M-Pesa code">
        </div>
        <div class="col-12">
            <label class="form-label" for="<?php echo e($idPrefix); ?>_proof">Proof of Payment <?php if(!$proofRequired): ?><span class="text-muted">(optional)</span><?php endif; ?></label>
            <input type="file" name="proof" id="<?php echo e($idPrefix); ?>_proof" class="form-control" accept="image/*,.pdf" <?php if($proofRequired): ?> required <?php endif; ?>>
            <small class="text-muted">Photo or screenshot of the receipt / M-Pesa message, or a PDF (max 5 MB).</small>
        </div>
        <div class="col-12">
            <label class="form-label" for="<?php echo e($idPrefix); ?>_notes">Notes</label>
            <textarea name="notes" id="<?php echo e($idPrefix); ?>_notes" class="form-control" rows="2"><?php echo e(old('notes')); ?></textarea>
        </div>
    </div>
    <button class="btn btn-success w-100 mt-3"><i class="icofont icofont-money"></i> <?php echo e($submitLabel); ?></button>
</form>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/partials/payment-form.blade.php ENDPATH**/ ?>