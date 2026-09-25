<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="PolicyPro" />
        <meta name="keywords" content="PolicyPro Administration Portal" />
        <meta name="author" content="pixelstrap" />
        <link rel="icon" href="<?php echo e(asset('assets/images/favicon.png')); ?>" type="image/x-icon" />
        <link rel="shortcut icon" href="<?php echo e(asset('assets/images/favicon.png')); ?>" type="image/x-icon" />
        <title><?php echo $__env->yieldContent('title'); ?></title>
        <!-- Google font-->
        <?php if ($__env->exists('admin.authentication.partials.css')) echo $__env->make('admin.authentication.partials.css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo \Livewire\Livewire::styles(); ?>

    </head>
    <body>
        <!-- Loader starts-->
        <div class="loader-wrapper">
            <div class="theme-loader">
                <div class="loader-p"></div>
            </div>
        </div>
        <!-- Loader ends-->
        <!-- error page start //-->
        <?php echo $__env->yieldContent('content'); ?>
        <!-- error page end //-->
        <!-- latest jquery-->
        <?php if ($__env->exists('admin.authentication.partials.js')) echo $__env->make('admin.authentication.partials.js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo \Livewire\Livewire::scripts(); ?>

    </body>
</html>



<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/admin/authentication/master.blade.php ENDPATH**/ ?>