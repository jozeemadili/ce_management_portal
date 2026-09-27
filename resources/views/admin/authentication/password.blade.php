@extends('admin.authentication.master')

{{--
    One page for the three password screens:
      first-change - logged in with the initial password, must pick a new one
      forgot       - enter the account email
      reset        - email matched, choose a new password
--}}

@section('title'){{ $title }}
 | {{Config('custom.constants.solution.name')}}
@endsection

@section('content')
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="{{ asset('assets/images/login/lbg.png') }}" alt="looginpage" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <form class="theme-form login-form" method="post" action="{{ $action }}">
							@csrf
							<center>
								<img src="{{ asset('assets/images/logo/LW-LOGO.png') }}" alt="logo" style="width: 40%; border-radius: 50%;" />
							</center>

	                        <h4 class="mt-3">{{ $title }}</h4>
	                        <p class="text-muted">{{ $intro }}</p>

							@if($mode === 'forgot')
	                        <div class="form-group">
	                            <label>Email</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-email"></i></span>
	                                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com" />
	                            </div>
								@error('email')<span class="text-danger txt-secondary"> - {{ $message }}</span>@enderror
	                        </div>
							@else
	                        <div class="form-group">
	                            <label>New Password</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-lock"></i></span>
	                                <input class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters, letters and numbers" />
	                            </div>
								@error('password')<span class="text-danger txt-secondary"> - {{ $message }}</span>@enderror
	                        </div>
	                        <div class="form-group">
	                            <label>Confirm New Password</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-lock"></i></span>
	                                <input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" />
	                            </div>
	                        </div>
							@endif

	                        <div class="form-group mt-3">
								<button style="width:100%" class="btn btn-primary btn-block" type="submit">{{ $button }}</button>
							</div>

							@if($message = Session::get('error'))
							<div class="alert alert-danger outline alert-dismissible fade show" role="alert">
								<i class="icon-info-alt txt-danger"></i> {{ $message }}
								<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif

							<p class="mt-3">
								@if($mode === 'first-change')
									<a href="{{ route('logout') }}">Log out</a>
								@else
									<a href="{{ route('login') }}">Back to login</a>
								@endif
							</p>
	                    </form>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
@endsection
