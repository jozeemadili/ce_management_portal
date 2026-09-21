<?php if(filled(config('livewire-powergrid.plugins.flatpickr.css'))): ?>
<link rel="stylesheet" href="<?php echo e(config('livewire-powergrid.plugins.flatpickr.css')); ?>">
<?php endif; ?>

<?php if(isBootstrap5() && filled(config('livewire-powergrid.plugins.bootstrap-select.css'))): ?>
    <link rel="stylesheet" href="<?php echo e(config('livewire-powergrid.plugins.bootstrap-select.css')); ?>" crossorigin="anonymous"/>
<?php endif; ?>

<?php if(isset($cssPath)): ?>
    <style><?php echo file_get_contents($cssPath); ?></style>
<?php endif; ?>

<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/vendor/livewire-powergrid/assets/styles.blade.php ENDPATH**/ ?>