<header class="main-nav">
    <div class="sidebar-user text-center">
        
        <img class="img-50 rounded-circle" src="<?php echo e(asset('assets/images/dashboard/1.png')); ?>" alt="" />
        <a href="<?php echo e(Route('home')); ?>"> <h6 class="mt-3 f-14 f-w-600"><?php echo e(ucfirst(Auth::user()->first_name)); ?></h6></a>
        <p class="mb-1 font-roboto">
            <?php $__empty_1 = true; $__currentLoopData = Auth::user()->member?->member_roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <span class="badge bg-info me-1">
                    <?php echo e($role->member_designation->name); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <span class="text-muted">No designation assigned</span>
            <?php endif; ?>
        </p>
        
        <p class="mb-0 font-roboto"> <small><?php echo e(Auth::user()->Company->name); ?></small></p>
        <!-- <p class="mb-0 font-roboto"><small><?php echo e(substr(strtoupper(Auth::user()->company->name), 0, 34)); ?></small></p> -->
    </div>
    <nav>
        <div class="main-navbar">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="mainnav">
                <ul class="nav-menu custom-scrollbar">
                    <li class="back-btn">
                        <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                    </li>
                    <li class="dropdown">
                        <a class="nav-link menu-title <?php echo e((request()->is('v1/dashboard') || request()->is('v1/summary')) ? 'active' : ''); ?>" href="javascript:void(0)"><i data-feather="bar-chart"></i><span>Summary</span></a>
                        <ul class="nav-submenu menu-content" style="display: <?php echo e((request()->is('v1/dashboard') || request()->is('v1/summary')) ? 'block' : ''); ?>;">
                            <li><a href="<?php echo e(route('home')); ?>" class="<?php echo e(routeActive('home')); ?>"> - Summary</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link menu-title <?php echo e((request()->is('v1/security/*')) ? 'active' : ''); ?>" href="javascript:void(0)"><i data-feather="settings"></i><span>Church Setup</span></a>
                        <ul class="nav-submenu menu-content" style="display: <?php echo e((request()->is('v1')) ? 'block' : ''); ?>;">
                            <li><a href="<?php echo e(route('churches-management')); ?>" class="<?php echo e(routeActive('churches-management')); ?>"> - Churches</a></li>
                            <li><a href="<?php echo e(route('churches.tree')); ?>" class="<?php echo e(routeActive('churches.tree')); ?>"> - Churches tree</a></li>
                            <li><a href="<?php echo e(route('member.management')); ?>" class="<?php echo e(routeActive('member.management')); ?>"> - Churches Member</a></li>
                            <li><a href="<?php echo e(route('cell.management')); ?>" class="<?php echo e(routeActive('cell.management')); ?>"> - Cell Management</a></li>
                            <li><a href="<?php echo e(route('department.management')); ?>" class="<?php echo e(routeActive('department.management')); ?>"> - Department Management</a></li>


                            
                            
                        </ul>
                    </li>   

                    <li class="dropdown">
                        <a class="nav-link menu-title <?php echo e((request()->is('v1/pledges/*')) ? 'active' : ''); ?>" href="javascript:void(0)"><i data-feather="gift"></i><span>Pledges</span></a>
                        <ul class="nav-submenu menu-content" style="display: <?php echo e((request()->is('v1/pledges/*')) ? 'block' : ''); ?>;">
                            <li><a href="<?php echo e(route('pledges.dashboard')); ?>" class="<?php echo e(routeActive('pledges.dashboard')); ?>"> - Dashboard</a></li>
                            <li><a href="<?php echo e(route('pledge-campaigns.index')); ?>" class="<?php echo e(routeActive('pledge-campaigns.index')); ?>"> - Campaigns</a></li>
                            <li><a href="<?php echo e(route('my-pledges.browse')); ?>" class="<?php echo e(routeActive('my-pledges.browse')); ?>"> - My Pledges</a></li>
                            <li><a href="<?php echo e(route('pledge-management.index')); ?>" class="<?php echo e(routeActive('pledge-management.index')); ?>"> - Record / Manage Pledges</a></li>
                            <li><a href="<?php echo e(route('pledge-contributions.index')); ?>" class="<?php echo e(routeActive('pledge-contributions.index')); ?>"> - Contributions / Fulfillment</a></li>
                            <li><a href="<?php echo e(route('pledge-live.select')); ?>" class="<?php echo e(routeActive('pledge-live.select')); ?>"> - Live Presentation</a></li>
                            <li><a href="<?php echo e(route('pledge-reports.index')); ?>" class="<?php echo e(routeActive('pledge-reports.index')); ?>"> - Reports</a></li>
                            <li><a href="<?php echo e(route('pledge-settings.index')); ?>" class="<?php echo e(routeActive('pledge-settings.index')); ?>"> - Settings</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link menu-title <?php echo e((request()->is('v1/programs*') || request()->is('v1/new-souls*') || request()->is('v1/program-reports*') || request()->is('v1/programs-dashboard') || request()->is('v1/programs-settings')) ? 'active' : ''); ?>" href="javascript:void(0)"><i data-feather="calendar"></i><span>Programs & Attendance</span></a>
                        <ul class="nav-submenu menu-content" style="display: <?php echo e((request()->is('v1/programs*') || request()->is('v1/new-souls*') || request()->is('v1/program-reports*') || request()->is('v1/programs-dashboard') || request()->is('v1/programs-settings')) ? 'block' : ''); ?>;">
                            <li><a href="<?php echo e(route('programs.dashboard')); ?>" class="<?php echo e(routeActive('programs.dashboard')); ?>"> - Dashboard</a></li>
                            <li><a href="<?php echo e(route('programs.index')); ?>" class="<?php echo e(routeActive('programs.index')); ?>"> - Programs</a></li>
                            <li><a href="<?php echo e(route('programs.index')); ?>?classification=recurring" class="<?php echo e(request()->is('v1/programs') && request('classification')==='recurring' ? 'active' : ''); ?>"> - Recurring Services</a></li>
                            <li><a href="<?php echo e(route('program-reports.attendance')); ?>" class="<?php echo e(routeActive('program-reports.attendance')); ?>"> - Attendance</a></li>
                            <li><a href="<?php echo e(route('my-programs.browse')); ?>" class="<?php echo e(routeActive('my-programs.browse')); ?>"> - My Registrations</a></li>
                            <li><a href="<?php echo e(route('new-souls.index')); ?>" class="<?php echo e(routeActive('new-souls.index')); ?>"> - New Souls</a></li>
                            <li><a href="<?php echo e(route('program-reports.index')); ?>" class="<?php echo e(routeActive('program-reports.index')); ?>"> - Reports</a></li>
                            <li><a href="<?php echo e(route('program-settings.index')); ?>" class="<?php echo e(routeActive('program-settings.index')); ?>"> - Settings</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link menu-title <?php echo e((request()->is('v1/security/*')) ? 'active' : ''); ?>" href="javascript:void(0)"><i data-feather="settings"></i><span>Security & Settings</span></a>
                        <ul class="nav-submenu menu-content" style="display: <?php echo e((request()->is('v1/security/*')) ? 'block' : ''); ?>;">
                            <li><a href="<?php echo e(route('security-user-profile')); ?>" class="<?php echo e(routeActive('security-user-profile')); ?>"> - Your Profile</a></li>
                            <?php if(Auth::user()->role == 'ADMIN'): ?>
                                <li><a href="<?php echo e(route('portal-users')); ?>" class="<?php echo e(routeActive('portal-users')); ?>"> - System Users</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>   
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </div>  
    </nav>
</header>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/layouts/admin/partials/sidebar.blade.php ENDPATH**/ ?>