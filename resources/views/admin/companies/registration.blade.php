@extends('layouts.admin.master')
@section('title')
{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}
@endsection
@push('css')
@endpush

@section('content')
  @component('components.breadcrumb')
    @slot('breadcrumb_title')
      <h3>{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
      
         <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCompanyModal">New <i class="icofont icofont-plus-circle"></i></button></li>

    @endslot
    
    <li class="breadcrumb-item">{{ucfirst(explode('-', Route::currentRouteName())[0])}}</li>
    <li class="breadcrumb-item active">{{ucfirst(explode('-', Route::currentRouteName())[1])}}</li>
  @endcomponent
  
  <div class="container-fluid">
      <div class="row">
          <div class="col-sm-12">
              <div class="card">

                  <div class="card-body">
                  @foreach ($errors->all() as $error)
                  <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                    <i class="icon-info-alt txt-danger"></i>
                        {{ $error }}
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                    </div>
                  @endforeach
                  
                  @if($message = Session::get('success'))
                    <div class="alert alert-success outline alert-dismissible fade show" role="alert">
                    <i class="icofont icofont-check-circled"></i>
                        {!! $message !!}
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                    </div>
                    <br />
					@endif

                      <p>
                      <div class="table-responsive">
                        @if(count($companies)>0)
						<table class="table table-xs">
							<thead>
								<tr>
									<th scope="col">#</th>
									
                                    <th scope="col">Name</th>
                                    <th scope="col">Category</th>
									<th scope="col">Reg #</th>
                                    <th scope="col">TIN</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Reg At</th>
                                   
                                    <th scope="col">Status</th>
								</tr>
							</thead>
							<tbody>
                                @foreach($companies as $company)
								<tr>
									<th scope="row">{{$loop->index + 1}}.</th>
								
									<td><a href='{{ Route('companies-profile', ['id' => $company->id]) }}'>{{strtoupper($company->name)}}</a></td>
                                    <td>{{$company->category}}</td>
                                    <td>{{$company->registration_number}}</td>
                                    
                                    <td>{{$company->tin}}</td>
                                   
                                    <td><small>{{$company->postal_address}}<br />{{$company->email_address}}</small></td>
                                    <td>{{$company->create_at->format('d/m/y')}}</td>
                                    <td>{{$company->status}}</td>
								</tr>
                                @endforeach
							</tbody>
						</table>
                        <br />
                        {{ $companies->links() }}

                        @else 
                        <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-danger"></i>
								No Records Found yet
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                       	</div>
                        @endif
					</div>
                      </p>
                  </div>
              </div>
          </div>
      </div>
  </div>

 <!-- NEW MODAL START -->
 <div class="modal fade" id="newCompanyModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Company Registration</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/v1/companies/add">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="col-form-label" >Company Name</label>
                                <input class="form-control" type="text" value="{{ old('name') }}" required  name="name">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Short Form</label>
                                <input class="form-control" type="text" minlength="3" maxlength="3" value="{{ old('short_form') }}" required  name="short_form" >
                            </div>
                            <!-- <div class="form-group">
                                <label class="col-form-label" >Company Code</label>
                                <input class="form-control" type="text" value="{{ old('code') }}" required  name="code" >
                            </div> -->
                            <div class="form-group">
                                <label class="col-form-label" >Registration Number</label>
                                <input class="form-control" type="number" minlength="5" value="{{ old('registration_number') }}" required  name="registration_number" >
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Licence Number</label>
                                <input class="form-control" type="number" minlength="5"  value="{{ old('license_number') }}" required  name="license_number">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >TIN</label>
                                <input class="form-control" type="number" minlength="6"  value="{{ old('tin') }}" required  name="tin">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <!-- <div class="form-group">
                                <label class="col-form-label" >SP Code</label>
                                <input class="form-control" type="text" value="{{ old('sale_point_code') }}"  name="sale_point_code">
                            </div> -->
                            <div class="form-group">
                                <label class="col-form-label" >Category</label>
                                <select class="form-select" required value="{{ old('category') }}" name="category">
                                <option value="">--- Choose Category ---</option>    
                                <option>INTERGRATOR</option>
                                <option>SINGLE LINE </option>
								</select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Email Address</label>
                                <input class="form-control" type="text" value="{{ old('email_address') }}" required  name="email_address">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Phone Number</label>
                                <input class="form-control" type="number" minlength="9" maxlength="9" value="{{ old('phone_number') }}" required name="phone_number" >
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Postal Address</label>
                                <input class="form-control" type="text" value="{{ old('postal_address') }}" required  name="postal_address">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Contact Person</label>
                                <input class="form-control" type="text" value="{{ old('contact_person') }}" required  name="contact_person">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            {{-- <div class="form-group">
                                <label class="col-form-label" >Azania Account no</label>
                                <input class="form-control" type="text" value="{{ old('sale_point_code') }}"  name="sale_point_code">
                            </div> --}}
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="col-form-label" >Corporate Color</label>
                                <input class="form-control" type="color" value="{{ old('color') }}"  name="color" placeholder="Choose Corporate Color ...">
                            </div>
                        </div>
                    </div>
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
                <button class="btn btn-primary" type="submit" >Confirm & Register</button>
                </form>
            </div>
        </div>
    </div>
    </div>
 <!-- NEW MODAL END -->

  @push('scripts')
  @endpush
@endsection