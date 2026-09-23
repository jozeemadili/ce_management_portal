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
                    <h1>Registration Confirmation</h1>
                    <p><?php echo e(optional($registration->program)->name); ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="ref-box">
        <div class="ref"><?php echo e($registration->registration_reference); ?></div>
        <div class="status"><?php echo e(ucfirst($registration->registration_status)); ?></div>
    </div>

    <table class="details">
        <tr>
            <td class="label">Attendee</td>
            <td class="value"><?php echo e(optional($registration->member)->first_name); ?> <?php echo e(optional($registration->member)->last_name); ?></td>
        </tr>
        <tr>
            <td class="label">Church</td>
            <td class="value"><?php echo e(optional(optional($registration->member)->church)->name ?? '—'); ?></td>
        </tr>
        <tr>
            <td class="label">Program</td>
            <td class="value"><?php echo e(optional($registration->program)->name); ?></td>
        </tr>
        <tr>
            <td class="label">Location</td>
            <td class="value"><?php echo e(optional($registration->program)->location ?? '—'); ?></td>
        </tr>
        <tr>
            <td class="label">Start Date</td>
            <td class="value"><?php echo e(optional(optional($registration->program)->start_date)->format('d M Y') ?? '—'); ?></td>
        </tr>
        <tr>
            <td class="label">End Date</td>
            <td class="value"><?php echo e(optional(optional($registration->program)->end_date)->format('d M Y') ?? optional(optional($registration->program)->start_date)->format('d M Y') ?? '—'); ?></td>
        </tr>
        <?php if(optional($registration->program)->start_time || optional($registration->program)->end_time): ?>
        <tr>
            <td class="label">Event Time (Daily)</td>
            <td class="value"><?php if(optional($registration->program)->start_time): ?><?php echo e(\Illuminate\Support\Carbon::parse($registration->program->start_time)->format('H:i')); ?><?php endif; ?> <?php if(optional($registration->program)->start_time && optional($registration->program)->end_time): ?>&ndash;<?php endif; ?> <?php if(optional($registration->program)->end_time): ?><?php echo e(\Illuminate\Support\Carbon::parse($registration->program->end_time)->format('H:i')); ?><?php endif; ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Access</td>
            <td class="value">
                <?php if(optional($registration->program)->isFree()): ?>
                    FREE
                <?php else: ?>
                    PAID &middot; <?php echo e(optional($registration->program)->currency); ?> <?php echo e(number_format(optional($registration->program)->registration_fee, 2)); ?>

                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Payment Status</td>
            <td class="value"><?php echo e(ucfirst($registration->payment_status)); ?></td>
        </tr>
        <tr>
            <td class="label">Registered On</td>
            <td class="value"><?php echo e(optional($registration->registered_at)->format('d M Y')); ?></td>
        </tr>
    </table>

    <div class="qr-section">
        <img src="<?php echo e($qr); ?>" width="160" height="160">
        <p>Scan this code at the venue to view your registration and check in.</p>
    </div>

    <div class="footer">
        Generated <?php echo e(now()->format('d M Y, H:i')); ?>

    </div>

</body>
</html>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/programs/pdf/registration.blade.php ENDPATH**/ ?>