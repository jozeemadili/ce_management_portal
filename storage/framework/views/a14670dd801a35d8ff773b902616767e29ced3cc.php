<?php $__env->startSection('title'); ?>
<?php echo e(ucfirst(str_replace('-',' ',Route::currentRouteName()))); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('css'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/date-picker.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
  <?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
      <h3><?php echo e(ucfirst(str_replace('-',' ',Route::currentRouteName()))); ?></h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
    <?php $__env->endSlot(); ?>
    
    <li class="breadcrumb-item"><?php echo e(ucfirst(explode('-', Route::currentRouteName())[0])); ?></li>
    <li class="breadcrumb-item active"><?php echo e(ucfirst(explode('-', Route::currentRouteName())[1])); ?></li>
  <?php echo $__env->renderComponent(); ?>
  
  <div class="container-fluid">
	    <div class="edit-profile">
	        <div class="row">
	            <div class="col-xl-5">
	                <div class="card">
	                    <div class="card-body">
	                        <form action="<?php echo e(Route('security-user-change_password')); ?>" method="post">
                            <?php echo csrf_field(); ?>
	                            <div class="row mb-2">
	                                <div class="profile-title">
	                                    <div class="media">
	                                        <img class="img-70 rounded-circle" alt="" src="<?php echo e(asset('assets/images/dashboard/1.png')); ?>">
	                                        <div class="media-body">
	                                            <h3 class="mb-1 f-20 txt-primary"><?php echo e(strtoupper(Auth::user()->first_name)); ?></h3>
	                                            <p class="f-12"><?php echo e(strtoupper(Auth::user()->role)); ?></p>
	                                        </div>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input disabled class="form-control" placeholder="<?php echo e(strtoupper(Auth::user()->title)); ?>">
                              </div>
	                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input disabled class="form-control" placeholder="<?php echo e(strtolower(Auth::user()->email)); ?>">
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input disabled class="form-control" placeholder="+255<?php echo e((Auth::user()->mobile)); ?>">
                              </div>
                              
                              <br /><br />

                              <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                                <i class="icon-info-alt txt-danger"></i>
                                    <?php echo e($error); ?>

                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                                </div>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

	                            <div class="mb-3">
	                                <label class="form-label">Update Your Password To Proceed</label>
	                                <input class="form-control" type="password" required name="new_password" placeholder="Enter New Password ...">
	                            </div>
	                            <div class="form-footer">
	                                <button class="btn btn-outline-primary btn-block">Change Password</button>
	                            </div>
	                        </form>
	                    </div>
	                </div>
	            </div>
	            <div class="col-xl-7">
                <div class="card">
                  <div class="card-body">

                    <div class="row">
                      <div class="profile-title">
                          <div class="media">
                              <div class="media-body">
                                  <h3 class="mb-1 f-20 txt-primary">My Profile</h3>
                                  <p class="f-12">Currently Logged in user profile</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  
	               <div class="table-responsive">
                    <table class="table table-sm">
                      <tr>
                        <th>Full Name</th>
                        <td><?php echo e(strtoupper(Auth::user()->first_name)); ?> <?php echo e(strtoupper(Auth::user()->middle_name)); ?> <?php echo e(strtoupper(Auth::user()->last_name)); ?></td>
                      </tr>
                      
                      <tr>
                        <th>Beleiver Of</th>
                        <td><?php echo e(strtoupper(Auth::user()->company->name)); ?></td>
                      </tr>
                      
                      <tr>
                        <th>Email</th>
                        <td><?php echo e((Auth::user()->email)); ?></td>
                      </tr>
                      <tr>
                        <th>Phone Number</th>
                        <td>+255<?php echo e((Auth::user()->mobile)); ?></td>
                      </tr>
                      <!-- <tr>
                        <th>Job Title</th>
                        <td><?php echo e(strtoupper(Auth::user()->title)); ?></td>
                      </tr> -->
                      <tr>
                        <th>System Role</th>
                        <td>
                          <p class="mb-1 font-roboto">
                            <?php $__empty_1 = true; $__currentLoopData = Auth::user()->member?->member_roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <span class="badge bg-info me-1">
                                    <?php echo e($role->member_designation->name); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">No designation assigned</span>
                            <?php endif; ?>
                        </p>
                        </td>
                      </tr>
<?php
    $member = \App\Models\Member::where('user_id', auth()->id())->first();
?>

<tr>
  <th>Foundation Class</th>
  <td>
      <?php if($member && $member->foundation_clases === 'yes'): ?>
          <span class="badge bg-success me-1">
              Completed | <?php echo e(optional($member->foundation_clases_date)->format('d-m-Y')); ?>

          </span>
      <?php else: ?>
          <span class="badge bg-secondary me-1">
              Not Completed
          </span>
      <?php endif; ?>
  </td>
</tr>

<tr>
  <th>Foundation Class</th>
  <td>
      <?php if($member && $member->foundation_clases === 'yes'): ?>
          <span class="badge bg-success me-1">
              Completed | <?php echo e(optional($member->foundation_clases_date)->format('d-m-Y')); ?>

          </span>
      <?php else: ?>
          <span class="badge bg-secondary me-1">
              Not Completed
          </span>
      <?php endif; ?>
  </td>
</tr>

<tr>
  <th>Marriage Status</th>
  <td>
      <?php if($member && $member->marriage_status === 'married'): ?>
          <span class="badge bg-success me-1">
              Married | <?php echo e(optional($member->marriage_dates)->format('d-m-Y')); ?>

          </span>
      <?php else: ?>
          <span class="badge bg-secondary me-1">
              Not Married
          </span>
      <?php endif; ?>
  </td>
</tr>

                      <tr>
                        <th>System Registration Date</th>
                        <td><?php echo e(Auth::user()->created_at->format('d M Y, H:i:s')); ?> | <i><?php echo e(Auth::user()->created_at->diffForHumans()); ?></i></td>
                      </tr>
                      <tr>
                        <th>Status</th>
                        <td><?php echo e(strtoupper(Auth::user()->status)); ?></td>
                      </tr>
                    </table>
                 </div>
                  </div>
                </div>
	            </div>
	        </div>
	</div>
  </div>

  
  <?php $__env->startPush('scripts'); ?>
  <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/admin/security/profile.blade.php ENDPATH**/ ?>