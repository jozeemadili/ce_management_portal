<?php $__env->startSection('title', 'Live Presentation'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Live Presentation</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Live Presentation</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<?php if($campaigns->isEmpty()): ?>
<div class="card pledge-card">
    <div class="card-body">
        <div class="pledge-empty">
            <i class="icofont icofont-presentation"></i>
            <p class="mb-0">No campaigns have Live Presentation enabled right now. Enable it from the campaign's Edit form.</p>
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
                <i class="icofont icofont-presentation"></i>
            <?php endif; ?>
        </div>
        <div class="campaign-card-body">
            <h5 class="campaign-card-title"><?php echo e($campaign->name); ?></h5>
            <div class="pledge-progress"><div class="pledge-progress-bar" style="width: <?php echo e($pct); ?>%"></div></div>
            <div class="d-flex justify-content-between">
                <small class="text-muted"><?php echo e($campaign->currency); ?> <?php echo e(number_format($campaign->totalPledged())); ?></small>
                <small class="text-muted"><?php echo e($pct); ?>%</small>
            </div>

            <div class="d-flex gap-2 mt-2">
                <a href="<?php echo e(route('pledge-live.present', $campaign->id)); ?>" target="_blank" class="btn btn-primary flex-fill">
                    <i class="icofont icofont-external-link"></i> Present
                </a>
                <button class="btn btn-outline-secondary settings-btn"
                    data-bs-toggle="modal" data-bs-target="#liveSettingsModal"
                    data-id="<?php echo e($campaign->id); ?>"
                    data-name="<?php echo e($campaign->name); ?>"
                    data-amount="<?php echo e($campaign->live_show_amount ? 1 : 0); ?>"
                    data-pledgers="<?php echo e($campaign->live_show_pledgers ? 1 : 0); ?>"
                    data-latest="<?php echo e($campaign->live_show_latest ? 1 : 0); ?>"
                    data-graph="<?php echo e($campaign->live_show_graph ? 1 : 0); ?>"
                    data-target="<?php echo e($campaign->live_show_target ? 1 : 0); ?>"
                    data-mask="<?php echo e($campaign->live_mask_names ? 1 : 0); ?>"
                    title="Presentation Controls">
                    <i class="icofont icofont-gear"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

</div>


<div class="modal fade" id="liveSettingsModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" id="liveSettingsForm" action="">
<?php echo csrf_field(); ?>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-gear"></i> Presentation Controls</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p class="mb-3">Campaign: <strong id="settings_campaign_name"></strong></p>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_amount" value="1" id="s_amount">
        <label class="form-check-label" for="s_amount">Show pledge amount</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_target" value="1" id="s_target">
        <label class="form-check-label" for="s_target">Show target</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_pledgers" value="1" id="s_pledgers">
        <label class="form-check-label" for="s_pledgers">Show pledger count</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_latest" value="1" id="s_latest">
        <label class="form-check-label" for="s_latest">Show latest pledges</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_graph" value="1" id="s_graph">
        <label class="form-check-label" for="s_graph">Show progress graph</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_mask_names" value="1" id="s_mask">
        <label class="form-check-label" for="s_mask">Mask pledger names (e.g. "Member from christ embassy mbezi")</label>
    </div>
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary">Save Controls</button>
</div>

</form>
</div>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.settings-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('liveSettingsForm').action = `/v1/pledges/live/${this.dataset.id}/settings`;
        document.getElementById('settings_campaign_name').textContent = this.dataset.name;
        document.getElementById('s_amount').checked = this.dataset.amount === '1';
        document.getElementById('s_pledgers').checked = this.dataset.pledgers === '1';
        document.getElementById('s_latest').checked = this.dataset.latest === '1';
        document.getElementById('s_graph').checked = this.dataset.graph === '1';
        document.getElementById('s_target').checked = this.dataset.target === '1';
        document.getElementById('s_mask').checked = this.dataset.mask === '1';
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/pledges/live/select.blade.php ENDPATH**/ ?>