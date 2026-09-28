@extends('admin.authentication.master')

@section('title')Share your testimony
 | {{Config('custom.constants.solution.name')}}
@endsection

@section('content')
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="{{ asset('assets/images/login/lbg.png') }}" alt="" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <form class="theme-form login-form" method="post" action="{{ route('feedback.store', $followup->token) }}">
							@csrf
							<center>
								<img src="{{ asset('assets/images/logo/LW-LOGO.png') }}" alt="logo" style="width: 34%; border-radius: 50%;" />
							</center>

							@if(session('success') || $followup->submitted_at)
								<h4 class="mt-3">Thank you, {{ ucfirst(strtolower($followup->member->first_name)) }}!</h4>
								<p class="text-muted">We have received your message. God bless you &mdash; we look forward to seeing you again.</p>
								@if($followup->feedback)
									<div class="alert alert-light" style="white-space: pre-wrap;">{{ $followup->feedback }}</div>
								@endif
							@else
								<h4 class="mt-3">Welcome, {{ ucfirst(strtolower($followup->member->first_name)) }}!</h4>
								<p class="text-muted">
									Thank you for worshipping with us
									@if($followup->occurrence)
										at {{ ucwords(strtolower(optional($followup->occurrence->church)->name)) }}
										({{ $followup->occurrence->program->name }}, {{ $followup->occurrence->occurrence_date->format('d M Y') }})
									@endif.
									Share your testimony or what blessed you &mdash; we would love to hear from you.
								</p>
								<div class="form-group">
									<label>Your testimony / feedback</label>
									<textarea class="form-control" name="feedback" rows="6" maxlength="3000" required placeholder="What blessed you today?">{{ old('feedback') }}</textarea>
									@error('feedback')<span class="text-danger txt-secondary"> - {{ $message }}</span>@enderror
								</div>
								<div class="form-group mt-3">
									<button style="width:100%" class="btn btn-primary btn-block" type="submit">Send</button>
								</div>
							@endif
	                    </form>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
@endsection
