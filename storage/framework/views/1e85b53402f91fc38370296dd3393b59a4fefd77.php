<?php $__env->startSection('title', 'New Invitees'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .inv-meta { font-size: .78rem; color: #6b7280; }
    .step-pill { display: inline-block; font-size: .7rem; font-weight: 600; border-radius: 20px; padding: 2px 9px; margin: 1px 2px 1px 0; }
    .step-yes { background: #e6f7ee; color: #0f9d58; }
    .step-no { background: #eef0f3; color: #6b7280; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>New Invitees</h3>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item">Church Setup</li>
    <li class="breadcrumb-item active">New Invitees</li>
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

<div class="row">
    <?php $__currentLoopData = [['New invitees', $stats['total'], 'bg-3', 'icofont-heart-alt'], ['Not assigned yet', $stats['unassigned'], 'bg-4', 'icofont-warning'], ['Did foundation classes', $stats['foundation'], 'bg-1', 'icofont-graduate-alt'], ['Baptized', $stats['baptized'], 'bg-6', 'icofont-water-drop']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $bg, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card prog-stat-card"><div class="stat-body">
                <div class="prog-stat-icon <?php echo e($bg); ?>"><i class="icofont <?php echo e($icon); ?>"></i></div>
                <div><p class="prog-stat-value"><?php echo e($value); ?></p><p class="prog-stat-label"><?php echo e($label); ?></p></div>
            </div></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<form method="GET" class="prog-filter-bar row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">Search</label><input name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Name, phone or area"></div>
    <div class="col-md-3">
        <label class="form-label">Church</label>
        <select name="church" class="form-select">
            <option value="">All my churches</option>
            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if((int) request('church') === $c->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Follow-up</label>
        <select name="status" class="form-select">
            <option value="">Any</option>
            <?php $__currentLoopData = \App\Http\Controllers\API\Church\InviteeController::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>" <?php if(request('status') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Assigned</label>
        <select name="assigned" class="form-select">
            <option value="">Any</option>
            <option value="no" <?php if(request('assigned') === 'no'): echo 'selected'; endif; ?>>Not assigned yet</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100"><i class="icofont icofont-filter"></i> Filter</button></div>
</form>

<div class="card prog-card">
<div class="card-body">
<?php if($invitees->isEmpty()): ?>
    <div class="prog-empty"><i class="icofont icofont-heart-alt"></i>No new invitees here yet. They appear when first-time visitors are recorded at a service or program.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table prog-table">
    <thead><tr><th>Invitee</th><th>First visit</th><th>Follow-up</th><th>Church (assign)</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $invitees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td>
                <strong><?php echo e(trim($invitee->first_name . ' ' . $invitee->last_name)); ?></strong>
                <div class="inv-meta">
                    <i class="icofont icofont-phone"></i> <?php echo e($invitee->phone ?: '—'); ?>

                    <?php if($invitee->email): ?><br><i class="icofont icofont-email"></i> <?php echo e($invitee->email); ?><?php endif; ?>
                    <br><i class="icofont icofont-location-pin"></i> <?php echo e($invitee->location ?: 'Area not given'); ?>

                </div>
            </td>
            <td>
                <?php echo e(optional($invitee->first_visit_date)->format('d M Y') ?? '—'); ?>

                <div class="inv-meta"><?php echo e(optional($invitee->firstVisitProgram)->name); ?></div>
                <?php if($invitee->invitedByMember || $invitee->invited_by): ?>
                    <div class="inv-meta">Invited by <?php echo e($invitee->invitedByMember ? trim($invitee->invitedByMember->first_name . ' ' . $invitee->invitedByMember->last_name) : $invitee->invited_by); ?></div>
                <?php endif; ?>
            </td>
            <td>
                <span class="badge-pill badge-status-<?php echo e($invitee->follow_up_status ?? 'new'); ?>"><?php echo e(\App\Http\Controllers\API\Church\InviteeController::STATUSES[$invitee->follow_up_status ?? 'new'] ?? ucfirst($invitee->follow_up_status)); ?></span>
                <div class="mt-1">
                    <span class="step-pill <?php echo e($invitee->foundation_clases === 'yes' ? 'step-yes' : 'step-no'); ?>">Foundation <?php echo e($invitee->foundation_clases === 'yes' ? '✓' : '—'); ?></span>
                    <span class="step-pill <?php echo e($invitee->baptism_status === 'yes' ? 'step-yes' : 'step-no'); ?>">Baptism <?php echo e($invitee->baptism_status === 'yes' ? '✓' : '—'); ?></span>
                </div>
            </td>
            <td><?php echo $__env->make('portal.churches.invitees.partials.assign', ['invitee' => $invitee, 'occurrence' => null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></td>
            <td class="text-end">
                <div class="d-flex gap-1 justify-content-end flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#followUp<?php echo e($invitee->id); ?>">Update</button>
                    <form method="POST" action="<?php echo e(route('invitees.make-member', $invitee->id)); ?>" onsubmit="return confirm('Make <?php echo e(addslashes($invitee->first_name)); ?> a member of <?php echo e(addslashes(optional($invitee->church)->name)); ?>?')">
                        <?php echo csrf_field(); ?> <button class="btn btn-sm btn-success">Make member</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
</div>
<?php echo e($invitees->links()); ?>

<?php endif; ?>
</div>
</div>

<?php $__currentLoopData = $invitees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="followUp<?php echo e($invitee->id); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="<?php echo e(route('invitees.update', $invitee->id)); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title">Follow-up: <?php echo e(trim($invitee->first_name . ' ' . $invitee->last_name)); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Follow-up status</label>
                        <select name="follow_up_status" class="form-select">
                            <?php $__currentLoopData = \App\Http\Controllers\API\Church\InviteeController::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php if(($invitee->follow_up_status ?? 'new') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Lives in (area)</label><input name="location" class="form-control" value="<?php echo e($invitee->location); ?>"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?php echo e($invitee->phone); ?>"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo e($invitee->email); ?>"></div>
                    <div class="col-md-6">
                        <label class="form-label">Foundation classes</label>
                        <select name="foundation_clases" class="form-select">
                            <option value="">Not yet</option>
                            <option value="yes" <?php if($invitee->foundation_clases === 'yes'): echo 'selected'; endif; ?>>Yes - completed</option>
                            <option value="no" <?php if($invitee->foundation_clases === 'no'): echo 'selected'; endif; ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Foundation classes date</label><input type="date" name="foundation_clases_date" class="form-control" value="<?php echo e(optional($invitee->foundation_clases_date)->format('Y-m-d')); ?>"></div>
                    <div class="col-md-6">
                        <label class="form-label">Baptism</label>
                        <select name="baptism_status" class="form-select">
                            <option value="">Not yet</option>
                            <option value="yes" <?php if($invitee->baptism_status === 'yes'): echo 'selected'; endif; ?>>Yes - baptized</option>
                            <option value="no" <?php if($invitee->baptism_status === 'no'): echo 'selected'; endif; ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Baptism date</label><input type="date" name="baptism_date" class="form-control" value="<?php echo e(optional($invitee->baptism_date)->format('Y-m-d')); ?>"></div>
                    <div class="col-12">
                        <label class="form-label">Add a note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. Visited at home, will attend Sunday"></textarea>
                        <?php if($invitee->notes): ?><div class="inv-meta mt-2" style="white-space:pre-line;"><?php echo e($invitee->notes); ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/churches/invitees/index.blade.php ENDPATH**/ ?>