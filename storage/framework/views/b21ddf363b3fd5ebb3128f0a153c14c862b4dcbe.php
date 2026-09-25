<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; color: #1c1c1e; font-size: 13px; }

    .banner { width: 100%; height: 110px; overflow: hidden; margin-bottom: 16px; border-radius: 6px; }
    .banner img { width: 100%; }

    .header { border-bottom: 3px solid #2e5aac; padding-bottom: 14px; margin-bottom: 20px; }
    .header table { width: 100%; }
    .header .logo-cell { width: 60px; }
    .header .logo-cell img { width: 50px; height: 50px; object-fit: contain; }
    .header h1 { font-size: 20px; color: #2e5aac; margin: 0 0 4px; }
    .header p { margin: 0; color: #6b7280; font-size: 12px; }

    .ref-box {
        background: #f0f3f9; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;
        text-align: center;
    }
    .ref-box .ref { font-size: 22px; font-weight: bold; letter-spacing: 1px; color: #2e5aac; }
    .ref-box .status { font-size: 11px; text-transform: uppercase; color: #6b7280; margin-top: 4px; }

    table.details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.details td { padding: 8px 10px; border-bottom: 1px solid #eef1f6; font-size: 12px; }
    table.details td.label { color: #6b7280; width: 40%; }
    table.details td.value { font-weight: bold; }

    .section-label {
        font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #9aa2b1;
        font-weight: bold; margin: 18px 0 8px;
    }

    table.fulfillment { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.fulfillment th {
        background: #f0f3f9; color: #6b7280; font-size: 10px; text-transform: uppercase;
        text-align: left; padding: 6px 8px;
    }
    table.fulfillment td { padding: 6px 8px; font-size: 12px; border-bottom: 1px solid #eef1f6; }
    .no-fulfillment { color: #9aa2b1; font-size: 12px; font-style: italic; }

    .qr-section { text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px dashed #c7d3da; }
    .qr-section p { font-size: 11px; color: #6b7280; margin-top: 8px; }

    .footer { margin-top: 30px; font-size: 10px; color: #9aa2b1; text-align: center; }
</style>
</head>
<body>

    <?php if($banner): ?>
    <div class="banner">
        <img src="<?php echo e($banner); ?>">
    </div>
    <?php endif; ?>

    <div class="header">
        <table>
            <tr>
                <?php if($logo): ?>
                <td class="logo-cell"><img src="<?php echo e($logo); ?>"></td>
                <?php endif; ?>
                <td>
                    <h1>Pledge Certificate</h1>
                    <p><?php echo e(optional($pledge->campaign)->name); ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="ref-box">
        <div class="ref"><?php echo e($pledge->pledge_reference); ?></div>
        <div class="status"><?php echo e(ucfirst(str_replace('_', ' ', $pledge->status))); ?></div>
    </div>

    <table class="details">
        <tr>
            <td class="label">Member</td>
            <td class="value"><?php echo e(optional($pledge->member)->first_name); ?> <?php echo e(optional($pledge->member)->last_name); ?></td>
        </tr>
        <tr>
            <td class="label">Church</td>
            <td class="value"><?php echo e(optional(optional($pledge->member)->church)->name ?? '—'); ?></td>
        </tr>
        <tr>
            <td class="label">Campaign</td>
            <td class="value"><?php echo e(optional($pledge->campaign)->name); ?></td>
        </tr>
        <tr>
            <td class="label">Pledged Amount</td>
            <td class="value"><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->amount, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Fulfilled</td>
            <td class="value"><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->totalFulfilled(), 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Outstanding</td>
            <td class="value"><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($pledge->outstanding(), 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Frequency</td>
            <td class="value"><?php echo e(ucfirst(str_replace('_', ' ', $pledge->frequency))); ?></td>
        </tr>
        <tr>
            <td class="label">Pledged On</td>
            <td class="value"><?php echo e(optional($pledge->pledged_at)->format('d M Y')); ?></td>
        </tr>
        <?php if($pledge->source === 'staff'): ?>
        <tr>
            <td class="label">Recorded By</td>
            <td class="value">Church Staff</td>
        </tr>
        <?php endif; ?>
    </table>

    <p class="section-label">Fulfillment History</p>
    <?php if($pledge->contributions->count()): ?>
    <table class="fulfillment">
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $pledge->contributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e(optional($c->payment_date)->format('d M Y')); ?></td>
                <td><?php echo e(optional($pledge->campaign)->currency); ?> <?php echo e(number_format($c->amount, 2)); ?></td>
                <td><?php echo e($c->payment_method ?? '—'); ?></td>
                <td><?php echo e($c->payment_reference ?? '—'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="no-fulfillment">No fulfillment has been recorded against this pledge yet.</p>
    <?php endif; ?>

    <div class="qr-section">
        <img src="<?php echo e($qr); ?>" width="160" height="160">
        <p>Scan this code to view pledge details and record a fulfillment.</p>
    </div>

    <div class="footer">
        Generated <?php echo e(now()->format('d M Y, H:i')); ?>

    </div>

</body>
</html>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/pledges/pdf/pledge.blade.php ENDPATH**/ ?>