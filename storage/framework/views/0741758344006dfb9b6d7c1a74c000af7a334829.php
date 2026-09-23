<?php $__env->startSection('title'); ?>Login
 | <?php echo e(Config('custom.constants.solution.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="<?php echo e(asset('assets/images/login/lbg.png')); ?>" alt="looginpage" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <form class="theme-form login-form" method="post" action="/portal/auth">
							<?php echo csrf_field(); ?>
							<center>
								<img 
								  src="<?php echo e(asset('assets/images/logo/LW-LOGO.png')); ?>" 
								  alt="oda" 
								  style="width: 40%; border-radius: 50%;" 
								/>
							  </center>
							  
	                        <h6><?php echo e(App\TIRAClient\Scripts\Classes\Utils::timeGreeting()); ?> !</h6>
	                        <div class="form-group">
	                            <label>Username</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-email"></i></span>
	                                <input class="form-control" type="text" name="email" required placeholder="Enter your username ..." />
	                            </div>
								<?php if($errors->has('email')): ?>
									<span class="text-danger txt-secondary"> - <?php echo e($errors->first('email')); ?></span>
								<?php endif; ?>
	                        </div>
	                        <div class="form-group">
	                            <label>Password</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-lock"></i></span>
	                                <input class="form-control" type="password" name="password" required placeholder="Enter your password ..." />
	                                <div class="show-hide"><span class="show"> </span></div>
	                            </div>
								<?php if($errors->has('password')): ?>
									<span class="text-danger txt-secondary"> - <?php echo e($errors->first('password')); ?></span>
								<?php endif; ?>
	                        </div>

							<br />

	                        <div class="form-group">
	                            <div class="checkbox">
	                                <input id="checkbox1" type="checkbox" name="remember" />
	                                <label class="text-muted" for="checkbox1">Remember me</label>
								</div>
	                            <a class="link" href="<?php echo e(route('forget-password')); ?>">Forgot password?</a>
	                        </div>
	                        <div class="form-group"><button style="width:100%" class="btn btn-primary btn-block" type="submit">Sign in</button></div>
	                        
							<?php if($message = Session::get('error')): ?>
							<div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-danger"></i>
								<?php echo e($message); ?>

                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                       		</div>
							<?php endif; ?>

							<?php if($message = Session::get('success')): ?>
							<div class="alert alert-success outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-success"></i>
								<?php echo e($message); ?>

                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                       		</div>
							<?php endif; ?>

							<br /><br />

							<div class="login-social-title">
							<!-- <h5>&copy; <?php echo e(Config('custom.constants.solution.name')); ?> <?php echo e(Config('custom.constants.solution.version')); ?></h5> -->
							<h5>&copy;<?php echo e(Config('custom.constants.solution.port_uat')); ?>  <?php echo e(Config('custom.constants.solution.name')); ?> <?php echo e(Config('custom.constants.solution.version')); ?></h5>
	                        </div>
	                        <p>How to use it ?<a class="ms-2" href="<?php echo e(route('how-to-use')); ?>" target="_blank">Read User Manual</a></p>
	                    </form>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>

    <?php $__env->startPush('scripts'); ?>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.authentication.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/admin/authentication/login.blade.php ENDPATH**/ ?>