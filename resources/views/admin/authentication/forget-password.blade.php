@extends('admin.authentication.master')

@section('title')Login
 | {{Config('custom.constants.solution.name')}}
@endsection

@push('css')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/sweetalert2.css')}}">
@endpush

@section('content')
    <section>
	    <div class="container-fluid">
	        <div class="row">
	            <div class="col-xl-5"><img class="bg-img-cover bg-center" src="{{ asset('assets/images/login/lbg.png') }}" alt="looginpage" /></div>
	            <div class="col-xl-7 p-0">
	                <div class="login-card">
	                    <div class="theme-form login-form">
							<center><img width="50%" src="{{ asset('assets/images/logo/pp.png') }}" alt="policypro" /></center>
							<div>
								@livewire('components.auth.resetpassword')
							</div>
							<div class="login-social-title" style="margin-top: 180px;">
							<h5>&copy; {{Config('custom.constants.solution.name')}} {{Config('custom.constants.solution.version')}}</h5>
	                        </div>
	                        <p>Have Password ?<a class="ms-2" href="{{ route('/') }}">Sigin here</a></p>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>

    @push('scripts')
	<script src="{{asset('assets/js/sweet-alert/sweetalert.min.js')}}"></script>
	<script src="{{asset('assets/js/sweet-alert/sweetalert.min.js')}}"></script>
	<script>
	
	  window.addEventListener('swal:modal', event => { 
		  swal({
			title: event.detail.message,
			text: event.detail.text,
			icon: event.detail.type,
			buttons:false,
			customClass:'swal-wide'
		  });
	  });
		
	  window.addEventListener('swal:confirm', event => { 
		  swal({
			title: event.detail.message,
			text: event.detail.text,
			icon: event.detail.type,
			buttons: true,
			dangerMode: true,
		  })
		  .then((willDelete) => {
			if (willDelete) {
			  window.livewire.emit('remove');
			}
		  });
	  });
	   </script>
    @endpush

@endsection
