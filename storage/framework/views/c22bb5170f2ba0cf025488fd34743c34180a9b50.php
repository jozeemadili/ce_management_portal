<div class="page-main-header">
  <div class="main-header-right row m-0">
    <div class="main-header-left">
      <div class="logo-wrapper"><a href="<?php echo e(route('home')); ?>"><img class="img-fluid" src="<?php echo e(asset('assets/images/logo/psp.png')); ?>" alt=""></a></div>
      <div class="dark-logo-wrapper"><a href="<?php echo e(route('home')); ?>"><img class="img-fluid" src="<?php echo e(asset('assets/images/logo/pp.png')); ?>" alt=""></a></div>
      <div class="toggle-sidebar"><i class="status_toggle middle" data-feather="align-center" id="sidebar-toggle">    </i></div>
    </div>
    <div class="left-menu-header col">
      <ul>
        <li>
          <div class="form-inline search-form">
            <div class="search-bg">
            <?php if(Auth::user()->role == 'Insurer Admin' || Auth::user()->role == 'Insurer Staff' || Auth::user()->role == 'Agent Admin' || Auth::user()->role == 'Agent Staff' || Auth::user()->role == 'Broker Admin' || Auth::user()->role == 'Broker Staff' || Auth::user()->role == 'Bank Assurance Admin' || Auth::user()->role == 'Bank Assurance Staff'): ?>
             <button class="btn btn-sm btn-outline-primary"  data-bs-toggle="modal" data-bs-target="#newModalMotor">Motor <i class="icofont icofont-plus-circle"></i></button>
                  &nbsp;&nbsp;
                 <button class="btn btn-sm btn-outline-primary"  data-bs-toggle="modal" data-bs-target="#newModalNonMotor">Others <i class="icofont icofont-plus-circle"></i></button>
            <?php endif; ?>
          </div>
          </div>
          
        </li>
      </ul>
    </div>
    <div class="nav-right col pull-right right-menu p-0">
      <ul class="nav-menus">
        <li><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i data-feather="maximize"></i></a></li>
        
        <li class="onhover-dropdown p-0">
          <a class="btn btn-primary-light" href="<?php echo e(route('logout')); ?>"><i data-feather="log-out"></i>Log out</a>
        </li>
      </ul>
    </div>
    <div class="d-lg-none mobile-toggle pull-right w-auto"><i data-feather="more-horizontal"></i></div>
  </div>
</div>


 



<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/layouts/admin/partials/header.blade.php ENDPATH**/ ?>