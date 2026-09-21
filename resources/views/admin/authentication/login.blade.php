@extends('admin.authentication.master')

@section('title')Login
 | {{Config('custom.constants.solution.name')}}
@endsection

@push('css')
@endpush

@section('content')
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="{{ asset('assets/images/login/lbg.png') }}" alt="looginpage" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <form class="theme-form login-form" method="post" action="/portal/auth">
							@csrf
							<center>
								<img 
								  src="{{ asset('assets/images/logo/LW-LOGO.png') }}" 
								  alt="oda" 
								  style="width: 40%; border-radius: 50%;" 
								/>
							  </center>
							  
	                        <h6>{{ App\TIRAClient\Scripts\Classes\Utils::timeGreeting() }} !</h6>
	                        <div class="form-group">
	                            <label>Username</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-email"></i></span>
	                                <input class="form-control" type="text" name="email" required placeholder="Enter your username ..." />
	                            </div>
								@if($errors->has('email'))
									<span class="text-danger txt-secondary"> - {{ $errors->first('email') }}</span>
								@endif
	                        </div>
	                        <div class="form-group">
	                            <label>Password</label>
	                            <div class="input-group">
	                                <span class="input-group-text"><i class="icon-lock"></i></span>
	                                <input class="form-control" type="password" name="password" required placeholder="Enter your password ..." />
	                                <div class="show-hide"><span class="show"> </span></div>
	                            </div>
								@if($errors->has('password'))
									<span class="text-danger txt-secondary"> - {{ $errors->first('password') }}</span>
								@endif
	                        </div>

							<br />

	                        <div class="form-group">
	                            <div class="checkbox">
	                                <input id="checkbox1" type="checkbox" name="remember" />
	                                <label class="text-muted" for="checkbox1">Remember me</label>
								</div>
	                            <a class="link" href="{{ route('forget-password') }}">Forgot password?</a>
	                        </div>
	                        <div class="form-group"><button style="width:100%" class="btn btn-primary btn-block" type="submit">Sign in</button></div>
	                        
							@if($message = Session::get('error'))
							<div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-danger"></i>
								{{ $message }}
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                       		</div>
							@endif

							@if($message = Session::get('success'))
							<div class="alert alert-success outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-success"></i>
								{{ $message }}
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                       		</div>
							@endif

							<br /><br />

							<div class="login-social-title">
							<!-- <h5>&copy; {{Config('custom.constants.solution.name')}} {{Config('custom.constants.solution.version')}}</h5> -->
							<h5>&copy;{{Config('custom.constants.solution.port_uat')}}  {{Config('custom.constants.solution.name')}} {{Config('custom.constants.solution.version')}}</h5>
	                        </div>
	                        <p>How to use it ?<a class="ms-2" href="{{ route('how-to-use') }}" target="_blank">Read User Manual</a></p>
	                    </form>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>

    @push('scripts')
    @endpush

@endsection
