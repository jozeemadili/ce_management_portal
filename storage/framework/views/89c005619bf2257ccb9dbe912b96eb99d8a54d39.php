<?php $__env->startSection('title'); ?><?php echo e($title); ?>

 | <?php echo e(Config('custom.constants.solution.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="<?php echo e(asset('assets/images/login/lbg.png')); ?>" alt="looginpage" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <form class="theme-form login-form" method="post" action="<?php echo e($action); ?>">
							<?php echo csrf_field(); ?>
							<center>
								<img src="<?php echo e(asset('assets/images/logo/LW-LOGO.png')); ?>" alt="logo" style="width: 40%; border-radius: 50%;" />
							</center>

	                        <h4 class="mt-3"><?php echo e($title); ?></h4>
	                        <p class="text-muted"><?php echo e($intro); ?></p>

							<?php if($mode === 'forgot'): ?>
	                        <div class="form-group">
	                            <label>Email</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-email"></i></span>
	                                <input class="form-control" type="email" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" placeholder="name@example.com" />
	                            </div>
								<?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger txt-secondary"> - <?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
	                        </div>
							<?php else: ?>
	                        <div class="form-group">
	                            <label>New Password</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-lock"></i></span>
	                                <input class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters, letters and numbers" />
	                            </div>
								<?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger txt-secondary"> - <?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
	                        </div>
	                        <div class="form-group">
	                            <label>Confirm New Password</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-lock"></i></span>
	                                <input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" />
	                            </div>
	                        </div>
							<?php endif; ?>

	                        <div class="form-group mt-3">
								<button style="width:100%" class="btn btn-primary btn-block" type="submit"><?php echo e($button); ?></button>
							</div>

							<?php if($message = Session::get('error')): ?>
							<div class="alert alert-danger outline alert-dismissible fade show" role="alert">
								<i class="icon-info-alt txt-danger"></i> <?php echo e($message); ?>

								<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							<?php endif; ?>

							<p class="mt-3">
								<?php if($mode === 'first-change'): ?>
									<a href="<?php echo e(route('logout')); ?>">Log out</a>
								<?php else: ?>
									<a href="<?php echo e(route('login')); ?>">Back to login</a>
								<?php endif; ?>
							</p>
	                    </form>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.authentication.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/admin/authentication/password.blade.php ENDPATH**/ ?>