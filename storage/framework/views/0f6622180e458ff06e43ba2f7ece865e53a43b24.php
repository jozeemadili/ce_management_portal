<?php $__env->startSection('title', 'Duplicate Members'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .dup-group { border: 1px solid #e3e8f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; background: #fff; }
    .dup-reason { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; margin-right: 4px; background: #fff4e5; color: #b45309; text-transform: uppercase; }
    .dup-table td, .dup-table th { vertical-align: middle; font-size: .85rem; }
    .dup-table tr.keep-row { background: #eef8f1; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Duplicate Members</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Setup</li>
    <li class="breadcrumb-item active">Duplicate Members</li>
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

<div class="card prog-card mb-3">
<div class="card-body">
    <p class="mb-1"><strong><?php echo e($groups->count()); ?></strong> group(s) of people who may be registered more than once (same name, same phone in any format, or same email).</p>
    <p class="text-muted mb-0" style="font-size:.85rem;">
        For each group choose the record to <strong>keep</strong> (suggested: the one with a login and the most activity), tick the record(s)
        that are the <strong>same person</strong>, and merge. Their phone numbers become extra numbers of the kept record, and their attendance,
        registrations, payments, pledges, roles, cells, departments, invitees and login move to it. A merge cannot be undone, but a snapshot of
        every removed record is kept in the activity log. Different people who only share a name? Use <em>Not the same person</em>.
    </p>
</div>
</div>

<?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gi => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <form method="POST" action="<?php echo e(route('duplicates.merge')); ?>" class="dup-group" data-group
          onsubmit="return confirm('Merge the ticked record(s) into the one you keep? This cannot be undone.')">
        <?php echo csrf_field(); ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <strong>Group <?php echo e($gi + 1); ?></strong> &middot; same
                <?php $__currentLoopData = $group['reasons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="dup-reason"><?php echo e($r); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary"><i class="icofont icofont-ui-copy"></i> Merge ticked into kept</button>
                <button type="submit" class="btn btn-sm btn-light" form="ignore<?php echo e($gi); ?>" onclick="return confirm('Mark these as different people?')">Not the same person</button>
            </div>
        </div>
        <div class="table-responsive">
        <table class="table dup-table mb-0">
            <thead><tr><th>Keep</th><th>Merge</th><th>Name</th><th>Church</th><th>Phones</th><th>Email</th><th>Login</th><th>Activity</th><th>Added</th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $group['members']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mi => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($mi === 0 ? 'keep-row' : ''); ?>">
                    <td><input class="form-check-input keep-radio" type="radio" name="keep_id" value="<?php echo e($m->id); ?>" <?php if($mi === 0): echo 'checked'; endif; ?>></td>
                    <td><input class="form-check-input merge-box" type="checkbox" name="merge_ids[]" value="<?php echo e($m->id); ?>" <?php if($mi === 0): echo 'disabled'; endif; ?>></td>
                    <td>
                        <strong><?php echo e($m->titledName()); ?></strong> <span class="text-muted">#<?php echo e($m->id); ?></span>
                        <div class="text-muted" style="font-size:.75rem;"><?php echo e($m->member_type === 'member' ? 'Member' : 'First-time visitor'); ?><?php echo e($m->location ? ' · ' . $m->location : ''); ?></div>
                    </td>
                    <td><?php echo e(optional($m->church)->name ?? '—'); ?></td>
                    <td><?php echo collect($m->allPhones())->map(fn ($p) => e($p))->implode('<br>') ?: '—'; ?></td>
                    <td><?php echo e($m->email ?: '—'); ?></td>
                    <td><?php echo e($m->user_id ? 'Yes' : '—'); ?></td>
                    <td style="font-size:.78rem;"><?php echo e($m->activity['attended']); ?> attended<br><?php echo e($m->activity['registrations']); ?> reg. &middot; <?php echo e($m->activity['pledges']); ?> pledges</td>
                    <td><?php echo e(optional($m->created_at)->format('d M Y')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
    </form>
    <form method="POST" action="<?php echo e(route('duplicates.ignore')); ?>" id="ignore<?php echo e($gi); ?>" class="d-none">
        <?php echo csrf_field(); ?>
        <?php $__currentLoopData = $group['members']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="member_ids[]" value="<?php echo e($m->id); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </form>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card prog-card"><div class="card-body prog-empty"><i class="icofont icofont-check-circled"></i>No likely duplicates. 🎉</div></div>
<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// The kept record can't also be merged away.
document.querySelectorAll('[data-group]').forEach(function (group) {
    group.addEventListener('change', function (e) {
        if (!e.target.classList.contains('keep-radio')) return;
        group.querySelectorAll('tbody tr').forEach(function (row) {
            var keep = row.querySelector('.keep-radio').checked;
            var box = row.querySelector('.merge-box');
            row.classList.toggle('keep-row', keep);
            box.disabled = keep;
            if (keep) box.checked = false;
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/churches/duplicates/index.blade.php ENDPATH**/ ?>