@extends('admin.authentication.master')

{{-- Public self check-in from a program's QR poster.
     step: identify (phone/email) | new (name + gender for a new soul) | done (welcome) --}}

@section('title'){{ $program->name }} - Check-in
 | {{Config('custom.constants.solution.name')}}
@endsection

@push('css')
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
</style>
@endpush

@section('content')
@php
    $base = url('/checkin/' . $program->checkin_token . ($church ? '/' . $church->id : ''));
@endphp
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="{{ asset('assets/images/login/lbg.png') }}" alt="" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <div class="theme-form login-form">
							<div class="checkin-hero">
								<img src="{{ asset('assets/images/logo/LW-LOGO.png') }}" alt="logo" style="width: 26%; border-radius: 50%;" />
								<div class="checkin-program">{{ $program->name }}</div>
								@if($church)<div class="checkin-church">{{ ucwords(mb_strtolower($church->name)) }}</div>@endif
								@if($program->is_training)<div class="badge bg-warning text-dark mt-1">TRAINING</div>@endif
							</div>

							@if($step === 'done')
								<div class="checkin-hero mt-3">
									@if($result['ok'])
										<div class="checkin-big">{{ $result['already'] ? '👋' : '🎉' }}</div>
										@if($result['already'])
											<div class="checkin-welcome">Welcome back, {{ $firstName }}!</div>
											<p class="checkin-note">You were already checked in{{ $result['time'] ? ' at ' . $result['time'] : '' }}. Enjoy the {{ $program->classification === 'special' ? 'program' : 'service' }}!</p>
										@elseif($isNew)
											<div class="checkin-welcome">Welcome, {{ $firstName }}!</div>
											<p class="checkin-note">We are so glad you are here with us today. You are checked in &mdash; God bless you, and feel at home!</p>
										@else
											<div class="checkin-welcome">Welcome, {{ $firstName }}!</div>
											<p class="checkin-note">You are checked in{{ $result['time'] ? ' at ' . $result['time'] : '' }}. We are glad to see you &mdash; God bless you!</p>
										@endif
									@else
										<div class="checkin-big">⚠️</div>
										<div class="checkin-welcome" style="font-size:1.2rem;">{{ $firstName ? 'Hello, ' . $firstName . '.' : 'Hello.' }}</div>
										<p class="checkin-note">{{ $result['message'] }}</p>
									@endif
									<a href="{{ $base }}" class="btn btn-light w-100 mt-2">Check in someone else</a>
								</div>

							@elseif(!$status['open'])
								<div class="checkin-hero mt-3">
									<div class="checkin-big">🕒</div>
									<p class="checkin-note">{{ $status['message'] }}</p>
								</div>
								@if(!empty($details))
									<div class="checkin-details">
										@if($program->description)<p class="checkin-note mb-2" style="white-space:pre-line;">{{ $program->description }}</p>@endif
										@foreach($details as $label => $value)
											<div class="detail-row">
												<span class="detail-label">{{ $label }}</span>
												@if($label === 'Contact phone')
													<a href="tel:{{ preg_replace('/[^0-9+]/', '', $value) }}" class="detail-value">{{ $value }}</a>
												@else
													<span class="detail-value">{{ $value }}</span>
												@endif
											</div>
										@endforeach
									</div>
								@endif

							@elseif($step === 'new')
								<form method="post" action="{{ $base }}/new" class="mt-3">
									@csrf
									<input type="hidden" name="identifier" value="{{ $identifier }}">
									<h5 class="mb-1">Welcome! You're new here &#128522;</h5>
									<p class="checkin-note" style="font-size:.9rem;">We couldn't find <strong>{{ $identifier }}</strong>. Tell us your name so we can welcome you.</p>
									<div class="form-group">
										<label>First name</label>
										<input class="form-control" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" autofocus />
									</div>
									<div class="form-group">
										<label>Last name</label>
										<input class="form-control" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" />
									</div>
									<div class="form-group">
										<label>Gender</label>
										<div class="gender-choice">
											<label><input type="radio" name="gender" value="male" required @checked(old('gender') === 'male')><span>Male</span></label>
											<label><input type="radio" name="gender" value="female" @checked(old('gender') === 'female')><span>Female</span></label>
										</div>
									</div>
									@if($errors->any())<div class="text-danger mb-2">{{ $errors->first() }}</div>@endif
									<button style="width:100%" class="btn btn-primary btn-block mt-2" type="submit">Check me in</button>
									<a href="{{ $base }}" class="btn btn-link w-100">That's not right &mdash; go back</a>
								</form>

							@else
								<form method="post" action="{{ $base }}" class="mt-3">
									@csrf
									<p class="checkin-note text-center">Enter your phone number or email to check in.</p>
									<div class="form-group">
										<label>Phone number or email</label>
										<input class="form-control" name="identifier" value="{{ old('identifier') }}" required autocomplete="tel" placeholder="e.g. 0712345678 or name@example.com" />
										@error('identifier')<span class="text-danger txt-secondary"> - {{ $message }}</span>@enderror
									</div>
									<button style="width:100%" class="btn btn-primary btn-block mt-2" type="submit">Continue</button>
								</form>
							@endif
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
@endsection
