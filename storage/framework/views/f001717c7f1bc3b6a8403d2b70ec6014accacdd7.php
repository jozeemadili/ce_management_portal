<div class="container-fluid">
    <div class="page-header">
      <div class="row">
        <div class="col-lg-6">
          <?php echo e($breadcrumb_title ?? ''); ?>

          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(Config('custom.constants.solution.name')); ?></a></li>
              <?php echo e($slot ?? ''); ?>

          </ol>
        </div>
        <div class="col-lg-6">
          <!-- Bookmark Start-->
          <div class="bookmark">
            <ul>
               <?php echo e($breadcrumb_action_buttons ?? ''); ?>

              
            </ul>
          </div>
          <!-- Bookmark Ends-->
        </div>
      </div>
    </div>
</div><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/components/breadcrumb.blade.php ENDPATH**/ ?>