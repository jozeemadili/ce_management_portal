<?php $__env->startSection('title'); ?>Share your testimony
 | <?php echo e(Config('custom.constants.solution.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="<?php echo e(asset('assets/images/login/lbg.png')); ?>" alt="" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <form class="theme-form login-form" method="post" action="<?php echo e(route('feedback.store', $followup->token)); ?>">
							<?php echo csrf_field(); ?>
							<center>
								<img src="<?php echo e(asset('assets/images/logo/LW-LOGO.png')); ?>" alt="logo" style="width: 34%; border-radius: 50%;" />
							</center>

							<?php if(session('success') || $followup->submitted_at): ?>
								<h4 class="mt-3">Thank you, <?php echo e(ucfirst(strtolower($followup->member->first_name))); ?>!</h4>
								<p class="text-muted">We have received your message. God bless you &mdash; we look forward to seeing you again.</p>
								<?php if($followup->feedback): ?>
									<div class="alert alert-light" style="white-space: pre-wrap;"><?php echo e($followup->feedback); ?></div>
								<?php endif; ?>
							<?php else: ?>
								<h4 class="mt-3">Welcome, <?php echo e(ucfirst(strtolower($followup->member->first_name))); ?>!</h4>
								<p class="text-muted">
									Thank you for worshipping with us
									<?php if($followup->occurrence): ?>
										at <?php echo e(ucwords(strtolower(optional($followup->occurrence->church)->name))); ?>

										(<?php echo e($followup->occurrence->program->name); ?>, <?php echo e($followup->occurrence->occurrence_date->format('d M Y')); ?>)
									<?php endif; ?>.
									Share your testimony or what blessed you &mdash; we would love to hear from you.
								</p>
								<div class="form-group">
									<label>Your testimony / feedback</label>
									<textarea class="form-control" name="feedback" rows="6" maxlength="3000" required placeholder="What blessed you today?"><?php echo e(old('feedback')); ?></textarea>
									<?php $__errorArgs = ['feedback'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger txt-secondary"> - <?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
								</div>
								<div class="form-group mt-3">
									<button style="width:100%" class="btn btn-primary btn-block" type="submit">Send</button>
								</div>
							<?php endif; ?>
	                    </form>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.authentication.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/guest/feedback.blade.php ENDPATH**/ ?>