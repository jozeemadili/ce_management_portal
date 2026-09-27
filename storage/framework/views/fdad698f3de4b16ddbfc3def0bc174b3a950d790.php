<?php $__env->startSection('title', 'Contributions / Fulfillment'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.pledges.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .pledge-search-results {
        border: 1px solid #e2e6ee; border-radius: 8px; max-height: 220px; overflow-y: auto;
        margin-top: 4px; display: none; background: #fff; position: relative; z-index: 5;
    }
    .pledge-search-results .item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f2f7; }
    .pledge-search-results .item:last-child { border-bottom: none; }
    .pledge-search-results .item:hover { background: #f7f9fc; }
    .selected-pledge-chip {
        display: none; align-items: center; gap: 8px; background: #eef2ff; color: #4338ca;
        padding: 8px 12px; border-radius: 8px; margin-top: 8px; font-size: .85rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Contributions / Fulfillment</h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-outline-success" href="<?php echo e(route('pledge-contributions.export', request()->query())); ?>">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordContributionModal">
                <i class="icofont icofont-plus-circle"></i> Record Contribution
            </button>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Contributions</li>
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

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo e(session('success')); ?>

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-2"><i class="icofont icofont-money"></i></div>
                <div>
                    <p class="pledge-stat-value"><?php echo e(number_format($total)); ?></p>
                    <p class="pledge-stat-label">Total Contributions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card pledge-card">
<div class="card-body">

<form method="GET" action="<?php echo e(route('pledge-contributions.index')); ?>" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-8">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Pledge reference, member, or payment reference">
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i></button>
        <?php if(request()->filled('q')): ?>
        <a href="<?php echo e(route('pledge-contributions.index')); ?>" class="btn btn-outline-secondary"><i class="icofont icofont-refresh"></i></a>
        <?php endif; ?>
    </div>
</div>
</form>

<?php if($contributions->count()): ?>
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>Pledge Ref</th>
    <th>Member</th>
    <th>Church</th>
    <th>Campaign</th>
    <th class="text-end">Amount</th>
    <th>Date</th>
    <th>Method</th>
    <th>Recorded By</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $contributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
    <td class="fw-semibold"><?php echo e(optional($c->pledge)->pledge_reference); ?></td>
    <td><?php echo e(optional(optional($c->pledge)->member)->first_name); ?> <?php echo e(optional(optional($c->pledge)->member)->last_name); ?></td>
    <td><?php echo e(optional(optional(optional($c->pledge)->member)->church)->name ?? '—'); ?></td>
    <td><?php echo e(optional(optional($c->pledge)->campaign)->name); ?></td>
    <td class="text-end"><?php echo e(optional(optional($c->pledge)->campaign)->currency); ?> <?php echo e(number_format($c->amount, 2)); ?></td>
    <td><?php echo e(optional($c->payment_date)->format('d M Y')); ?></td>
    <td><?php echo e($c->payment_method ?? '—'); ?></td>
    <td><?php echo e(optional($c->recorder)->first_name ?? '—'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<?php echo e($contributions->links()); ?>

</div>
<?php else: ?>
<div class="pledge-empty">
    <i class="icofont icofont-money"></i>
    <p class="mb-0">No contributions recorded yet <?php if(request()->filled('q')): ?> for this search <?php endif; ?>.</p>
</div>
<?php endif; ?>

</div>
</div>
</div>
</div>

</div>


<div class="modal fade" id="recordContributionModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="<?php echo e(route('pledge-contributions.store')); ?>">
<?php echo csrf_field(); ?>
<input type="hidden" name="pledge_id" id="contrib_pledge_id" required>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Record Contribution</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Find Pledge</p>
<div class="position-relative">
    <input type="text" class="form-control" id="pledge_search_input" autocomplete="off"
        placeholder="Search by pledge reference or member name...">
    <div class="pledge-search-results" id="pledge_search_results"></div>
</div>
<div class="selected-pledge-chip" id="selected_pledge_chip">
    <i class="icofont icofont-listing-box"></i>
    <span id="selected_pledge_label"></span>
    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="clear_selected_pledge">Change</button>
</div>

<hr class="my-3">
<p class="modal-section-label">Contribution Details</p>

<label class="form-label">Amount</label>
<input type="number" step="0.01" min="1" name="amount" class="form-control" required>

<label class="form-label mt-3">Payment Date</label>
<input type="date" name="payment_date" class="form-control" value="<?php echo e(now()->format('Y-m-d')); ?>" required>

<div class="row">
    <div class="col-md-6 mt-3">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-control">
            <option value="">-- Select --</option>
            <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($m->name); ?>"><?php echo e($m->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Payment Reference</label>
        <input type="text" name="payment_reference" class="form-control">
    </div>
</div>

<label class="form-label mt-3">Notes (optional)</label>
<textarea name="notes" class="form-control" rows="2"></textarea>
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="recordContributionSubmit" disabled>Record Contribution</button>
</div>

</form>
</div>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const input = document.getElementById('pledge_search_input');
    const results = document.getElementById('pledge_search_results');
    const chip = document.getElementById('selected_pledge_chip');
    const labelEl = document.getElementById('selected_pledge_label');
    const hiddenId = document.getElementById('contrib_pledge_id');
    const submitBtn = document.getElementById('recordContributionSubmit');
    const clearBtn = document.getElementById('clear_selected_pledge');
    let debounceTimer = null;

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (q.length < 2) { results.style.display = 'none'; return; }

        debounceTimer = setTimeout(function () {
            fetch(`<?php echo e(route('pledge-contributions.search-pledges')); ?>?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    results.innerHTML = '';
                    if (!data.length) {
                        results.innerHTML = '<div class="item text-muted">No open pledges found</div>';
                    } else {
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'item';
                            div.innerHTML = `<strong>${p.reference}</strong> &middot; ${p.member}<br><small class="text-muted">${p.campaign} &middot; Outstanding: ${p.currency} ${Number(p.outstanding).toLocaleString()}</small>`;
                            div.addEventListener('click', function () {
                                hiddenId.value = p.id;
                                labelEl.textContent = `${p.reference} - ${p.member}`;
                                chip.style.display = 'flex';
                                input.style.display = 'none';
                                results.style.display = 'none';
                                submitBtn.disabled = false;
                            });
                            results.appendChild(div);
                        });
                    }
                    results.style.display = 'block';
                });
        }, 300);
    });

    clearBtn.addEventListener('click', function () {
        hiddenId.value = '';
        chip.style.display = 'none';
        input.style.display = '';
        input.value = '';
        submitBtn.disabled = true;
    });

    document.getElementById('recordContributionModal').addEventListener('hidden.bs.modal', function () {
        hiddenId.value = '';
        chip.style.display = 'none';
        input.style.display = '';
        input.value = '';
        results.style.display = 'none';
        submitBtn.disabled = true;
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/pledges/contributions/index.blade.php ENDPATH**/ ?>