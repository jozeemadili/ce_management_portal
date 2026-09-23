<?php $__env->startSection('title', 'Attendance Report'); ?>

<?php $__env->startPush('css'); ?>
<?php echo $__env->make('portal.programs.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Attendance Report</h3>
    <?php $__env->endSlot(); ?>
    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <a class="btn btn-success" href="<?php echo e(route('program-reports.attendance.export', request()->query())); ?>">
                <i class="icofont icofont-download"></i> Export Excel
            </a>
        </li>
    <?php $__env->endSlot(); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('program-reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Attendance</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="card prog-card mb-3">
    <div class="card-body">
        <form method="GET" class="prog-filter-bar row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Program</label>
                <select name="program_id" class="form-control" id="report_program_select">
                    <option value="">-- All --</option>
                    <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php if(request('program_id')==$p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All --</option>
                    <?php $__currentLoopData = ['present'=>'Present','absent'=>'Absent','late'=>'Late','excused'=>'Excused']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php if(request('status')===$val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="icofont icofont-search"></i> Filter</button>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="downloadPdfForProgram()" title="Printable PDF for selected program (most recent date)">
                    <i class="icofont icofont-file-pdf"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card prog-card">
    <div class="card-body">
        <?php if($attendances->count()): ?>
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Program</th><th>Date</th><th>Attendee</th><th>Type</th><th>Church</th><th>Status</th><th>Method</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(optional($att->program)->name); ?></td>
            <td><?php echo e(optional(optional($att->occurrence)->occurrence_date)->format('d M Y')); ?></td>
            <td class="fw-semibold"><?php echo e($att->attendeeName()); ?></td>
            <td><?php echo e($att->isNewSoul() ? 'Visitor' : 'Member'); ?></td>
            <td><?php echo e(optional(optional($att->member)->church)->name ?? '—'); ?></td>
            <td><span class="badge-pill badge-status-<?php echo e($att->attendance_status); ?>"><?php echo e(ucfirst($att->attendance_status)); ?></span></td>
            <td><span class="badge-pill badge-method-<?php echo e($att->check_in_method); ?>"><?php echo e($att->check_in_method === 'qr' ? 'QR Scan' : 'Manual'); ?></span></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        </table>
        </div>
        <?php echo e($attendances->links()); ?>

        <?php else: ?>
        <div class="prog-empty"><i class="icofont icofont-check-circled"></i><p class="mb-0">No attendance records match these filters.</p></div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function downloadPdfForProgram() {
    var select = document.getElementById('report_program_select');
    if (!select.value) {
        alert('Select a program first to download its printable attendance PDF.');
        return;
    }
    var url = '<?php echo e(route('program-reports.pdf-attendance', ['program' => '__ID__'])); ?>'.replace('__ID__', select.value);
    window.location.href = url;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/reports/attendance.blade.php ENDPATH**/ ?>