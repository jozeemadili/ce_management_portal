@extends('layouts.admin.master')
@section('title')
{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}
@endsection
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/date-picker.css') }}">
@endpush

@section('content')
  @component('components.breadcrumb')
    @slot('breadcrumb_title')
      <h3>{{ucfirst(str_replace('-',' ',Route::currentRouteName()))}}</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
    <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#searchModal">Search <i class="icofont icofont-search-alt-1"></i></button></li>
    <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">New <i class="icofont icofont-plus-circle"></i></button></li>
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
                        @if(count($customers)>0)
						<table class="table table-xs">
							<thead>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Name</th>
                                    <th scope="col">Sex</th>
									<th scope="col">Type</th>
									<th scope="col">ID Type</th> 
                                    <th scope="col">ID Number</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Status</th>
								</tr>
							</thead>
							<tbody>
                                @foreach($customers as $customer)
								<tr>
									<th scope="row">{{$loop->index + 1}}.</th>
									<td><a href='{{url()->current()}}/profile/{{$customer->id}}''><small>{{strtoupper($customer->first_name)}} {{strtoupper($customer->last_name)}}</small></a></td>
									<td>{{strtoupper(substr($customer->gender,0,1))}}</td>
									<td>{{($customer->type == 1 ? "Individual" : "Corporate")}}</td>
                                    <td>{{App\Http\Controllers\API\Auth\CustomersController::resolveIdType($customer->id_type)}}</td>
                                    <td><a href='{{url()->current()}}/profile/{{$customer->id}}'>{{$customer->id_number}}</a></td>
                                    <td>{{$customer->mobile}}</td>
                                    <td>{{$customer->email}}</td>
                                    <td>{{$customer->status}}</td>
								</tr>
                                @endforeach
							</tbody>
						</table>
                        <br />
                        {{ $customers->links() }}

                        @else 
                        <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-danger"></i>
								No Customers Records created by you Found yet
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
 <div class="modal fade" id="newModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Customer Registration</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ Route('customers-add') }}">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-form-label" >First Name</label>
                                <input class="form-control" type="text" value="{{ old('first_name') }}" required  name="first_name">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Middle Name</label>
                                <input class="form-control" type="text" value="{{ old('middle_name') }}"  name="middle_name" >
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Last Name</label>
                                <input class="form-control" type="text" value="{{ old('last_name') }}" required  name="last_name" >
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Gender</label><br>
                                <input type="radio" checked class="radio_animated" value="Male" name="gender"> Male
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="radio_animated" value="Female" name="gender"> Female
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Customer Type</label><br>
                                <input type="radio" checked class="radio_animated" value="1" name="type"> Individual
                                &nbsp;&nbsp;
                                <input type="radio" class="radio_animated" value="2" name="type"> Corporate
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-form-label" >Date of Birth</label>
                                <input class="datepicker-here form-control digits" type="text" data-language="en" value="{{ old('dob') }}"  name="dob">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Email Address</label>
                                <input class="form-control" type="text" value="{{ old('email') }}" required  name="email">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Phone Number</label>
                                <input class="form-control" type="text" minlength="9" maxlength="9" value="{{ old('mobile') }}" required name="mobile" >
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Identity Type</label>
                                <select class="form-select" required value="{{ old('id_type') }}" name="id_type">
                                <option value="">--- Choose ID Type ---</option>    
                                    <option value="1">NIDA</option>
                                    <option value="2">VOTERS</option>
                                    <option value="3">PASSPORT</option>
                                    <option value="4">DRIVING LICENCE</option>
                                    <option value="5">ZANID</option>
                                    <option value="6">TIN</option>
                                    <option value="7">INCORPORATION CERTIFICATE NUMBER</option>
								</select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" >Identity Number</label>
                                <input class="form-control" type="text" minlength="3"  value="{{ old('id_number') }}" required  name="id_number">
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="col-form-label" >Country Code</label>
                                @include('admin.customers.countries')
                            </div>

                            @livewire('components.regions-districts-wards')

                            <div class="form-group">
                                <label class="col-form-label" >Choose Password | Default : <b>Policypro{{ date('Y'); }}!</b></label>
                                <input class="form-control" type="password" value="Policypro{{ date('Y') }}!" minlength="8"  value="{{ old('password') }}" required  name="password">
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

   <!-- SEARCH MODAL START -->
   <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Customer Search</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ url()->current() }}">
                    @csrf

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="col-form-label">Search By</label><br>
                                <input type="radio" checked class="radio_animated" value="id_number" name="search_by" id="search_by" onChange="searchBy(this)"> Identity No.
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="radio_animated" value="phone_number" name="search_by" id="search_by" onChange="searchBy(this)"> Phone No.
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="radio_animated" value="name" name="search_by" id="search_by" onChange="searchBy(this)"> Name
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ old('reference_number') }}" maxlength="20" required id="reference_number">
                            </div>
                        </div>
                    </div>
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
                <button class="btn btn-primary" type="submit" >Search</button>
                </form>
            </div>
        </div>
    </div>
    </div>
 <!-- SEARCH MODAL END -->

  @push('scripts')
  <script src="{{ asset('assets/js/datepicker/date-picker/datepicker.js') }}"></script>
  <script src="{{ asset('assets/js/datepicker/date-picker/datepicker.en.js') }}"></script>
  <script src="{{ asset('assets/js/datepicker/date-picker/datepicker.custom.js') }}"></script>
  <script>
    document.getElementById("reference_number").placeholder = "Enter Customer's ID Number ...";
    document.getElementById('reference_number').name = 'id_number';
    function searchBy(search_by)
    {
        if(search_by.value == "id_number")
        {
            document.getElementById("reference_number").type = "text";
            document.getElementById("reference_number").placeholder = "Enter Customer's ID Number ...";
            document.getElementById('reference_number').name = 'id_number';
        }
        else if(search_by.value == "phone_number")
        {
            document.getElementById("reference_number").maxlength = "9";
            document.getElementById("reference_number").type = "number";
            document.getElementById("reference_number").placeholder = "Enter Customer's Phone (e.g. 766192332) ...";
            document.getElementById('reference_number').name = 'phone_number';
            
        }
        else
        {
            document.getElementById("reference_number").type = "text";
            document.getElementById("reference_number").placeholder = "Enter Customer's First Name or Middle Name or Last Name ...";
            document.getElementById('reference_number').name = 'cname';
        }
    }
 </script>
  @endpush
@endsection
