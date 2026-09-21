@extends('layouts.admin.master')
@section('title')
{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}
@endsection
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/date-picker.css') }}">
@endpush

@section('content')
  <div class="container-fluid">
	    <div class="edit-profile">
	        <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                  <div class="card-body">

                    <div class="row mb-2">
                        <div class="profile-title">
                            <div class="media">
                                <img class="img-90 img-thumbnail" alt="" src="{{'/assets/images/logo/'.strtolower($company->short_form).'.png' }}">
                                <div class="media-body">
                                    <h3 class="mb-1 f-20" style="color:{{ $company->color }};">{{ strtoupper($company->name) }}</h3>
                                    <p class="f-12">{{ strtoupper($company->category) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                  
	               <div class="table-responsive">
                    <form method="post" action="{{ Route('companies-profile-update', ['id' => $company->id]) }}">
                    @csrf
                    <table class="table table-sm">
                      <tr>
                        <th>Company Name</th>
                        <td>{{ strtoupper($company->name) }}</td>
                      </tr>
                      <tr>
                        <th>Short Form</th>
                        <td><input type="text" class="border-0" name="short_form" minlength="3" maxlength="5" value="{{ strtoupper($company->short_form) }}" /></td>
                      </tr>
                      <tr>
                        <th>Category</th>
                        <td>{{ strtoupper($company->category) }}</td>
                      </tr>
                      <tr>
                        <th>Registration No.</th>
                        <td>{{ ($company->registration_number) }}</td>
                      </tr>
                      <tr>
                        <th>Licence No.</th>
                        <td>{{ ($company->license_number) }}</td>
                      </tr>
                      <tr>
                        <th>Company TIN</th>
                        <td>{{ ($company->tin) }}</td>
                      </tr>
                      <tr>
                        <th>Company Code</th>
                        <td>{{ ($company->code) }}</td>
                      </tr>
                      <tr>
                        <th>Sales Point Code</th>
                        <td>{{ strtoupper($company->sale_point_code) }}</td>
                      </tr>
                      <tr>
                        <th>Company Color</th>
                        <td><input type='color' name="color" value='{{ strtoupper($company->color) }}' /></td>
                      </tr>
                      <tr>
                        <th>Contact Person</th>
                        <td><input type="text" class="border-0" name="contact_person" value="{{ strtoupper($company->contact_person) }}" /></td>
                      </tr>
                      <tr>
                        <th>Postal Address</th>
                        <td><input type="text" class="border-0" name="postal_address" value="{{ strtoupper($company->postal_address) }}" /></td>
                      </tr>
                      <tr>
                        <th>Email Address</th>
                        <td><a href="mailto:{{ $company->email_address }}">{{ ($company->email_address) }}</a></td>
                      </tr>
                      <tr>
                        <th>Phone No.</th>
                        <td>+255{{ ($company->phone_number) }}</td>
                      </tr>
                      <tr>
                        <th>Registration Date</th>
                        <td>{{ strtoupper($company->create_at->format('d M Y, H:i:s')) }}</td>
                      </tr>
                      <tr>
                        <th>Status</th>
                        <td>{{ strtoupper($company->status) }}</td>
                      </tr>
                    </table>
                    @foreach ($errors->all() as $error)
                    <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                      <i class="icon-info-alt txt-danger"></i>
                          {{ $error }}
                      </div>
                    @endforeach
                      <br />
                      @if($message = Session::get('success'))
                      <div class="alert alert-success outline alert-dismissible fade show" role="alert">
                      <i class="icofont icofont-check-circled"></i>
                          {!! $message !!}
                       </div>
                      <br />
                      @endif

                      <div class="form-footer">
                          <button type="submit" class="btn btn-outline-primary btn-block">Update Profile</button>
                      </div>
                    </form>
                 </div>
                  </div>
                    </div>
	            </div>
	        </div>
	</div>
  </div>

  
  @push('scripts')
  @endpush

@endsection