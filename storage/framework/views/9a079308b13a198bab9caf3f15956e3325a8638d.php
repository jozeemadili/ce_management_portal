<?php $__env->startSection('title', 'Active Pledge Campaigns'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Active Campaigns</h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-primary" href="<?php echo e(route('my-pledges.index')); ?>">
                <i class="icofont icofont-listing-box"></i> My Pledges
            </a>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Active Campaigns</li>
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

<?php if($campaigns->isEmpty()): ?>
<div class="card pledge-card">
    <div class="card-body">
        <div class="pledge-empty">
            <i class="icofont icofont-bullseye"></i>
            <p class="mb-0">There are no active campaigns available to you right now.</p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
<?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php ($pct = $campaign->progressPercent()); ?>
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card campaign-card">
        <div class="campaign-card-banner" <?php if($campaign->banner_path): ?> style="background-image:url('<?php echo e(asset('storage/' . $campaign->banner_path)); ?>');background-size:cover;background-position:center;" <?php endif; ?>>
            <?php if (! ($campaign->banner_path)): ?>
                <i class="icofont icofont-bullseye"></i>
            <?php endif; ?>
        </div>
        <div class="campaign-card-body">
            <h5 class="campaign-card-title"><?php echo e($campaign->name); ?></h5>
            <p class="campaign-card-desc"><?php echo e(\Illuminate\Support\Str::limit($campaign->description ?? '', 90) ?: 'No description provided.'); ?></p>

            <div class="pledge-progress"><div class="pledge-progress-bar" style="width: <?php echo e($pct); ?>%"></div></div>
            <div class="d-flex justify-content-between">
                <small class="text-muted"><?php echo e($campaign->currency); ?> <?php echo e(number_format($campaign->totalPledged())); ?> raised</small>
                <small class="text-muted"><?php echo e($pct); ?>%</small>
            </div>

            <div class="campaign-card-meta">
                <span><i class="icofont icofont-bullseye"></i> <?php echo e($campaign->currency); ?> <?php echo e(number_format($campaign->target_amount)); ?></span>
                <span><i class="icofont icofont-people"></i> <?php echo e($campaign->pledgersCount()); ?> pledgers</span>
            </div>

            <?php if($campaign->start_date || $campaign->end_date): ?>
            <div class="campaign-card-meta">
                <span><i class="icofont icofont-calendar"></i>
                    <?php echo e(optional($campaign->start_date)->format('d M Y')); ?>

                    <?php if($campaign->end_date): ?> &mdash; <?php echo e($campaign->end_date->format('d M Y')); ?> <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>

            <button class="btn btn-primary mt-2 make-pledge-btn"
                data-bs-toggle="modal" data-bs-target="#makePledgeModal"
                data-campaign-id="<?php echo e($campaign->id); ?>"
                data-campaign-name="<?php echo e($campaign->name); ?>"
                data-currency="<?php echo e($campaign->currency); ?>">
                <i class="icofont icofont-gift"></i> Make a Pledge
            </button>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

</div>


<div class="modal fade" id="makePledgeModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="<?php echo e(route('my-pledges.store')); ?>">
<?php echo csrf_field(); ?>
<input type="hidden" name="campaign_id" id="pledge_campaign_id">

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-gift"></i> Make a Pledge</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p class="mb-3">Campaign: <strong id="pledge_campaign_name"></strong></p>

    <label class="form-label">Pledge Amount (<span id="pledge_currency">TZS</span>)</label>
    <input type="number" step="0.01" min="1" name="amount" class="form-control" required>

    <label class="form-label mt-3">Fulfillment Frequency</label>
    <select name="frequency" class="form-control" required>
        <option value="one_time">One Time</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="custom">Custom</option>
    </select>

    <label class="form-label mt-3">Notes (optional)</label>
    <textarea name="notes" class="form-control" rows="2"></textarea>

    
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary">Submit Pledge</button>
</div>

</form>
</div>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.make-pledge-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('pledge_campaign_id').value = this.dataset.campaignId;
        document.getElementById('pledge_campaign_name').textContent = this.dataset.campaignName;
        document.getElementById('pledge_currency').textContent = this.dataset.currency;
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/pledges/my/browse.blade.php ENDPATH**/ ?>