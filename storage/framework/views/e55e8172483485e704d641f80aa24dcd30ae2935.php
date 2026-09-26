<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; color: #1c1c1e; font-size: 12px; }

    .header { border-bottom: 3px solid #2e5aac; padding-bottom: 14px; margin-bottom: 18px; }
    .header table { width: 100%; }
    .header .logo-cell { width: 60px; }
    .header .logo-cell img { width: 50px; height: 50px; object-fit: contain; }
    .header h1 { font-size: 18px; color: #2e5aac; margin: 0 0 4px; }
    .header p { margin: 0; color: #6b7280; font-size: 11px; }

    table.details { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.details td { padding: 6px 8px; border-bottom: 1px solid #eef1f6; font-size: 11px; }
    table.details td.label { color: #6b7280; width: 25%; }
    table.details td.value { font-weight: bold; }

    .summary-row { width: 100%; margin-bottom: 16px; }
    .summary-box {
        display: inline-block; width: 18%; text-align: center; padding: 10px 4px;
        background: #f0f3f9; border-radius: 6px; margin-right: 1%;
    }
    .summary-box .num { font-size: 18px; font-weight: bold; color: #2e5aac; }
    .summary-box .lbl { font-size: 9px; color: #6b7280; text-transform: uppercase; }

    table.attendees { width: 100%; border-collapse: collapse; }
    table.attendees th {
        background: #f0f3f9; color: #6b7280; font-size: 9px; text-transform: uppercase;
        text-align: left; padding: 6px 8px;
    }
    table.attendees td { padding: 5px 8px; font-size: 11px; border-bottom: 1px solid #eef1f6; }

    .footer { margin-top: 20px; font-size: 9px; color: #9aa2b1; text-align: center; }
</style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <?php if($logo): ?>
                <td class="logo-cell"><img src="<?php echo e($logo); ?>"></td>
                <?php endif; ?>
                <td>
                    <h1>Attendance Report</h1>
                    <p><?php echo e($program->name); ?></p>
                </td>
            </tr>
        </table>
    </div>

    <table class="details">
        <tr>
            <td class="label">Date</td>
            <td class="value"><?php echo e($occurrence->occurrence_date->format('d M Y')); ?></td>
            <td class="label">Location</td>
            <td class="value"><?php echo e($program->location ?? '—'); ?></td>
        </tr>
        <tr>
            <td class="label">Category</td>
            <td class="value"><?php echo e(ucfirst(str_replace('_',' ',$program->category))); ?></td>
            <td class="label">Classification</td>
            <td class="value"><?php echo e(ucfirst($program->classification)); ?></td>
        </tr>
        <tr>
            <td class="label">Organizer</td>
            <td class="value"><?php echo e($program->organizer ?? '—'); ?></td>
            <td class="label">Time</td>
            <td class="value"><?php echo e($occurrence->start_time ?? '—'); ?> - <?php echo e($occurrence->end_time ?? '—'); ?></td>
        </tr>
    </table>

    <div class="summary-row">
        <div class="summary-box"><div class="num"><?php echo e($summary['total']); ?></div><div class="lbl">Total</div></div>
        <div class="summary-box"><div class="num"><?php echo e($summary['present']); ?></div><div class="lbl">Present</div></div>
        <div class="summary-box"><div class="num"><?php echo e($summary['absent']); ?></div><div class="lbl">Absent</div></div>
        <div class="summary-box"><div class="num"><?php echo e($summary['late']); ?></div><div class="lbl">Late</div></div>
        <div class="summary-box"><div class="num"><?php echo e($summary['excused']); ?></div><div class="lbl">Excused</div></div>
    </div>

    <table class="attendees">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Session</th>
                <th>Type</th>
                <th>Church</th>
                <th>Status</th>
                <th>Method</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($att->attendeeName()); ?></td>
                <td><?php echo e(optional($att->session)->name ?? '—'); ?><?php if($att->checked_in_at): ?> (<?php echo e($att->checked_in_at->format('H:i')); ?>)<?php endif; ?></td>
                <td><?php echo e($att->isNewSoul() ? 'Visitor' : 'Member'); ?></td>
                <td><?php echo e(optional(optional($att->member)->church)->name ?? '—'); ?></td>
                <td><?php echo e(ucfirst($att->attendance_status)); ?></td>
                <td><?php echo e($att->check_in_method === 'qr' ? 'QR Scan' : 'Manual'); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7">No attendees recorded for this date.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Generated <?php echo e(now()->format('d M Y, H:i')); ?>

    </div>

</body>
</html>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/pdf/attendance-report.blade.php ENDPATH**/ ?>