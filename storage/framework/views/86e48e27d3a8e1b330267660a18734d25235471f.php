<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in QR - <?php echo e($program->name); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Montserrat, Arial, sans-serif; background: #eef1f6; color: #1f2937; }
        .toolbar { max-width: 760px; margin: 16px auto; padding: 0 16px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; }
        .toolbar a, .toolbar button { font: inherit; border: 1px solid #c9d2e0; background: #fff; border-radius: 8px; padding: 8px 14px; cursor: pointer; color: #1f2937; text-decoration: none; }
        .toolbar button.primary { background: #2e5aac; border-color: #2e5aac; color: #fff; }
        .toolbar select { font: inherit; padding: 8px; border-radius: 8px; border: 1px solid #c9d2e0; }
        .poster { max-width: 760px; margin: 0 auto 32px; background: #fff; border-radius: 18px; padding: 40px 32px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .logo { width: 110px; border-radius: 50%; }
        h1 { font-size: 2.2rem; margin: 14px 0 4px; }
        .church { font-size: 1.3rem; color: #4b5563; margin-bottom: 6px; }
        .scan { font-size: 1.7rem; font-weight: 800; color: #2e5aac; margin: 18px 0 14px; }
        .qr svg { width: 420px; height: 420px; max-width: 100%; }
        .steps { display: flex; justify-content: center; gap: 22px; margin-top: 18px; font-size: 1.05rem; flex-wrap: wrap; }
        .steps b { display: inline-flex; width: 30px; height: 30px; border-radius: 50%; background: #2e5aac; color: #fff; align-items: center; justify-content: center; margin-right: 6px; }
        .url { margin-top: 18px; font-size: .8rem; color: #9aa2b1; word-break: break-all; }
        .training { display: inline-block; background: #fff4e5; color: #b45309; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .poster { box-shadow: none; margin: 0 auto; padding: 10mm; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="<?php echo e(route('programs.show', $program->id)); ?>">&larr; Back</a>
        <?php if($isService && $churches->count() > 1): ?>
            <form method="GET">
                <label for="church">Church:</label>
                <select name="church" id="church" onchange="this.form.submit()">
                    <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" <?php if($church && $c->id === $church->id): echo 'selected'; endif; ?>><?php echo e(strtoupper($c->name)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
        <?php endif; ?>
        <button class="primary" onclick="window.print()">Print poster</button>
    </div>

    <div class="poster">
        <img class="logo" src="<?php echo e(asset('assets/images/logo/LW-LOGO.png')); ?>" alt="">
        <h1><?php echo e($program->name); ?></h1>
        <?php if($church): ?><div class="church"><?php echo e(ucwords(mb_strtolower($church->name))); ?></div><?php endif; ?>
        <?php if($program->is_training): ?><div class="training">TRAINING</div><?php endif; ?>
        <div class="scan">Scan to check in</div>
        <div class="qr"><?php echo $qrSvg; ?></div>
        <div class="steps">
            <span><b>1</b>Scan with your phone camera</span>
            <span><b>2</b>Enter your phone number</span>
            <span><b>3</b>You're checked in!</span>
        </div>
        <div class="url"><?php echo e($url); ?></div>
    </div>
</body>
</html>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/portal/programs/checkin-poster.blade.php ENDPATH**/ ?>