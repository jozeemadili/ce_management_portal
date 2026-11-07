<?php $__env->startSection('title'); ?><?php echo e($program->name); ?> - Check-in
 | <?php echo e(Config('custom.constants.solution.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
<style>
    .checkin-hero { text-align: center; }
    .checkin-program { font-weight: 700; font-size: 1.15rem; margin: 10px 0 2px; }
    .checkin-church { color: #6b7280; font-size: .9rem; }
    .checkin-big { font-size: 3.2rem; line-height: 1; margin: 12px 0 6px; }
    .checkin-welcome { font-size: 1.5rem; font-weight: 800; margin: 6px 0; }
    .checkin-note { color: #4b5563; }
    .checkin-details { background: #f7f9fc; border-radius: 12px; padding: 12px 14px; margin-top: 8px; }
    .detail-row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px dashed #e3e8f0; font-size: .9rem; }
    .detail-row:last-child { border-bottom: 0; }
    .detail-label { color: #6b7280; white-space: nowrap; }
    .detail-value { font-weight: 600; text-align: right; }
    .gender-choice { display: flex; gap: 10px; }
    .gender-choice label { flex: 1; border: 1px solid #d5dde8; border-radius: 10px; padding: 12px; text-align: center; cursor: pointer; font-weight: 600; }
    .gender-choice input { display: none; }
    .gender-choice input:checked + span { color: #2e5aac; }
    .gender-choice label:has(input:checked) { border-color: #2e5aac; background: #eef4ff; }
    .brought-box { border-top: 1px dashed #e3e8f0; padding-top: 12px; }
    .brought-row { background: #f7f9fc; border-radius: 12px; padding: 10px 12px; margin-bottom: 10px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var toggle = document.getElementById('broughtToggle');
    var form = document.getElementById('broughtForm');
    if (!toggle || !form) return;
    var rows = document.getElementById('broughtRows');
    var tpl = document.getElementById('broughtRowTpl');

    function renumber() {
        rows.querySelectorAll('.brought-row').forEach(function (row, i) {
            row.querySelector('.brought-num').textContent = 'Person ' + (i + 1);
            row.querySelector('.brought-remove').style.display = i === 0 ? 'none' : '';
            row.querySelectorAll('[data-name]').forEach(function (input) {
                input.name = 'people[' + i + '][' + input.dataset.name + ']';
            });
        });
    }
    function addRow() {
        rows.appendChild(tpl.content.cloneNode(true));
        renumber();
    }
    toggle.addEventListener('click', function () {
        form.style.display = '';
        toggle.style.display = 'none';
        if (!rows.children.length) addRow();
        rows.querySelector('input').focus();
    });
    document.getElementById('broughtAdd').addEventListener('click', addRow);
    rows.addEventListener('click', function (e) {
        if (e.target.classList.contains('brought-remove')) {
            e.target.closest('.brought-row').remove();
            renumber();
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $base = url('/checkin/' . $program->checkin_token . ($church ? '/' . $church->id : ''));
?>
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="<?php echo e(asset('assets/images/login/lbg.png')); ?>" alt="" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <div class="theme-form login-form">
							<div class="checkin-hero">
								<img src="<?php echo e(asset('assets/images/logo/LW-LOGO.png')); ?>" alt="logo" style="width: 26%; border-radius: 50%;" />
								<div class="checkin-program"><?php echo e($program->name); ?></div>
								<?php if($church): ?><div class="checkin-church"><?php echo e(ucwords(mb_strtolower($church->name))); ?></div><?php endif; ?>
								<?php if($program->is_training): ?><div class="badge bg-warning text-dark mt-1">TRAINING</div><?php endif; ?>
							</div>

							<?php if($step === 'done'): ?>
								<div class="checkin-hero mt-3">
									<?php if($result['ok']): ?>
										<div class="checkin-big"><?php echo e($result['already'] ? '👋' : '🎉'); ?></div>
										<?php if($result['already']): ?>
											<div class="checkin-welcome">Welcome back, <?php echo e($firstName); ?>!</div>
											<p class="checkin-note">You were already checked in<?php echo e($result['time'] ? ' at ' . $result['time'] : ''); ?>. Enjoy the <?php echo e($program->classification === 'special' ? 'program' : 'service'); ?>!</p>
										<?php elseif($isNew): ?>
											<div class="checkin-welcome">Welcome, <?php echo e($firstName); ?>!</div>
											<p class="checkin-note">We are so glad you are here with us today. You are checked in &mdash; God bless you, and feel at home!</p>
										<?php else: ?>
											<div class="checkin-welcome">Welcome, <?php echo e($firstName); ?>!</div>
											<p class="checkin-note">You are checked in<?php echo e($result['time'] ? ' at ' . $result['time'] : ''); ?>. We are glad to see you &mdash; God bless you!</p>
										<?php endif; ?>
									<?php else: ?>
										<div class="checkin-big">⚠️</div>
										<div class="checkin-welcome" style="font-size:1.2rem;"><?php echo e($firstName ? 'Hello, ' . $firstName . '.' : 'Hello.'); ?></div>
										<p class="checkin-note"><?php echo e($result['message']); ?></p>
									<?php endif; ?>
								</div>

								<?php if(!empty($broughtToken)): ?>
									<div class="brought-box mt-3">
										<button type="button" class="btn btn-outline-success w-100" id="broughtToggle">🙌 Did you come with a new soul?</button>
										<form method="post" action="<?php echo e($base); ?>/brought" id="broughtForm" class="mt-3" style="display:none;">
											<?php echo csrf_field(); ?>
											<input type="hidden" name="by" value="<?php echo e($broughtToken); ?>">
											<p class="checkin-note" style="font-size:.9rem;">Add each person you came with &mdash; we'll check them in and welcome them as your invitees.</p>
											<div id="broughtRows"></div>
											<button type="button" class="btn btn-link p-0 mb-2" id="broughtAdd">+ Add another person</button>
											<button class="btn btn-success w-100" type="submit">Check them in</button>
										</form>
									</div>
									<template id="broughtRowTpl">
										<div class="brought-row">
											<div class="d-flex justify-content-between align-items-center mb-1">
												<strong class="brought-num" style="font-size:.85rem;"></strong>
												<button type="button" class="btn btn-sm btn-link text-danger p-0 brought-remove">Remove</button>
											</div>
											<input class="form-control mb-2" data-name="first_name" placeholder="First name *" required>
											<input class="form-control mb-2" data-name="last_name" placeholder="Last name">
											<input class="form-control mb-2" data-name="phone" placeholder="Phone number" inputmode="tel">
											<input class="form-control mb-2" data-name="location" placeholder="Where they live (area)">
											<select class="form-select mb-2" data-name="gender">
												<option value="">Gender</option><option value="male">Male</option><option value="female">Female</option>
											</select>
										</div>
									</template>
								<?php endif; ?>
								<a href="<?php echo e($base); ?>" class="btn btn-light w-100 mt-3">Check in someone else</a>

							<?php elseif($step === 'brought'): ?>
								<div class="checkin-hero mt-3">
									<?php if(!empty($result['checked_in'])): ?>
										<div class="checkin-big">🎉</div>
										<div class="checkin-welcome">Thank you, <?php echo e($firstName); ?>!</div>
										<p class="checkin-note">
											<?php echo e(collect($result['checked_in'])->join(', ', ' and ')); ?>

											<?php echo e(count($result['checked_in']) === 1 ? 'is' : 'are'); ?> checked in &mdash; welcome! We are so glad you brought them.
										</p>
									<?php endif; ?>
									<?php if(!$result['ok']): ?>
										<div class="checkin-big">⚠️</div>
										<p class="checkin-note"><?php echo e($result['message']); ?></p>
									<?php endif; ?>
									<?php $__currentLoopData = $result['problems'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $problem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<p class="checkin-note text-warning"><?php echo e($problem); ?></p>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</div>

								<?php if(!empty($broughtToken)): ?>
									<div class="brought-box mt-3">
										<button type="button" class="btn btn-outline-success w-100" id="broughtToggle">🙌 Add more people you came with</button>
										<form method="post" action="<?php echo e($base); ?>/brought" id="broughtForm" class="mt-3" style="display:none;">
											<?php echo csrf_field(); ?>
											<input type="hidden" name="by" value="<?php echo e($broughtToken); ?>">
											<p class="checkin-note" style="font-size:.9rem;">Add each person you came with &mdash; we'll check them in and welcome them as your invitees.</p>
											<div id="broughtRows"></div>
											<button type="button" class="btn btn-link p-0 mb-2" id="broughtAdd">+ Add another person</button>
											<button class="btn btn-success w-100" type="submit">Check them in</button>
										</form>
									</div>
									<template id="broughtRowTpl">
										<div class="brought-row">
											<div class="d-flex justify-content-between align-items-center mb-1">
												<strong class="brought-num" style="font-size:.85rem;"></strong>
												<button type="button" class="btn btn-sm btn-link text-danger p-0 brought-remove">Remove</button>
											</div>
											<input class="form-control mb-2" data-name="first_name" placeholder="First name *" required>
											<input class="form-control mb-2" data-name="last_name" placeholder="Last name">
											<input class="form-control mb-2" data-name="phone" placeholder="Phone number" inputmode="tel">
											<input class="form-control mb-2" data-name="location" placeholder="Where they live (area)">
											<select class="form-select mb-2" data-name="gender">
												<option value="">Gender</option><option value="male">Male</option><option value="female">Female</option>
											</select>
										</div>
									</template>
								<?php endif; ?>
								<a href="<?php echo e($base); ?>" class="btn btn-light w-100 mt-3">Check in someone else</a>

							<?php elseif(!$status['open']): ?>
								<div class="checkin-hero mt-3">
									<div class="checkin-big">🕒</div>
									<p class="checkin-note"><?php echo e($status['message']); ?></p>
								</div>
								<?php if(!empty($details)): ?>
									<div class="checkin-details">
										<?php if($program->description): ?><p class="checkin-note mb-2" style="white-space:pre-line;"><?php echo e($program->description); ?></p><?php endif; ?>
										<?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<div class="detail-row">
												<span class="detail-label"><?php echo e($label); ?></span>
												<?php if($label === 'Contact phone'): ?>
													<a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $value)); ?>" class="detail-value"><?php echo e($value); ?></a>
												<?php else: ?>
													<span class="detail-value"><?php echo e($value); ?></span>
												<?php endif; ?>
											</div>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</div>
								<?php endif; ?>

							<?php elseif($step === 'new'): ?>
								<form method="post" action="<?php echo e($base); ?>/new" class="mt-3">
									<?php echo csrf_field(); ?>
									<input type="hidden" name="identifier" value="<?php echo e($identifier); ?>">
									<h5 class="mb-1">Welcome! You're new here &#128522;</h5>
									<p class="checkin-note" style="font-size:.9rem;">We couldn't find <strong><?php echo e($identifier); ?></strong>. Tell us your name so we can welcome you.</p>
									<div class="form-group">
										<label>First name</label>
										<input class="form-control" name="first_name" value="<?php echo e(old('first_name')); ?>" required autocomplete="given-name" autofocus />
									</div>
									<div class="form-group">
										<label>Last name</label>
										<input class="form-control" name="last_name" value="<?php echo e(old('last_name')); ?>" autocomplete="family-name" />
									</div>
									<div class="form-group">
										<label>Where do you live? <span class="text-muted">(area)</span></label>
										<input class="form-control" name="location" value="<?php echo e(old('location')); ?>" placeholder="e.g. Mbezi Beach" autocomplete="address-level2" />
									</div>
									<?php if(str_contains($identifier, '@')): ?>
										<div class="form-group">
											<label>Phone number <span class="text-muted">(optional)</span></label>
											<input class="form-control" name="phone" value="<?php echo e(old('phone')); ?>" placeholder="e.g. 0712345678" autocomplete="tel" />
										</div>
									<?php else: ?>
										<div class="form-group">
											<label>Email <span class="text-muted">(optional)</span></label>
											<input type="email" class="form-control" name="email" value="<?php echo e(old('email')); ?>" autocomplete="email" />
										</div>
									<?php endif; ?>
									<div class="form-group">
										<label>Who invited you? <span class="text-muted">(optional)</span></label>
										<input class="form-control" name="invited_by" value="<?php echo e(old('invited_by')); ?>" placeholder="Their phone number or name" />
										<small class="text-muted">Their phone number lets us thank the right person.</small>
									</div>
									<div class="form-group">
										<label>Gender</label>
										<div class="gender-choice">
											<label><input type="radio" name="gender" value="male" required <?php if(old('gender') === 'male'): echo 'checked'; endif; ?>><span>Male</span></label>
											<label><input type="radio" name="gender" value="female" <?php if(old('gender') === 'female'): echo 'checked'; endif; ?>><span>Female</span></label>
										</div>
									</div>
									<?php if($errors->any()): ?><div class="text-danger mb-2"><?php echo e($errors->first()); ?></div><?php endif; ?>
									<button style="width:100%" class="btn btn-primary btn-block mt-2" type="submit">Check me in</button>
									<a href="<?php echo e($base); ?>" class="btn btn-link w-100">That's not right &mdash; go back</a>
								</form>

							<?php else: ?>
								<form method="post" action="<?php echo e($base); ?>" class="mt-3">
									<?php echo csrf_field(); ?>
									<p class="checkin-note text-center">Enter your phone number or email to check in.</p>
									<div class="form-group">
										<label>Phone number or email</label>
										<input class="form-control" name="identifier" value="<?php echo e(old('identifier')); ?>" required autocomplete="tel" placeholder="e.g. 0712345678 or name@example.com" />
										<?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger txt-secondary"> - <?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
									</div>
									<button style="width:100%" class="btn btn-primary btn-block mt-2" type="submit">Continue</button>
								</form>
							<?php endif; ?>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.authentication.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/resources/views/guest/checkin.blade.php ENDPATH**/ ?>