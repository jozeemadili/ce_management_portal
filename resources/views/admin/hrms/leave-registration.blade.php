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
        <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">New Leave<i class="icofont icofont-plus-circle"></i></button></li>
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
                        @if(count($users)>0)
						<table class="table table-xs">
							<thead>

								<tr>
									<th scope="col">#</th>
                                      <th scope="col">Emp ID</th>
                                    <th scope="col">Leave Type</th>
                                    <th scope="col">Leave Details</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">End Date</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Phone</th>
                                   
								</tr>
							</thead>
							<tbody>
                                @foreach($users as $user)
								<tr>
                                <!-- SELECT `id`, `emp_id`, ``, ``, `reg_by`, `status`, `reg_date`, ``, ``, ``, `email`, `` FROM `leaves` WHERE 1 -->
									<th scope="row">{{$loop->index + 1}}.</th>
								         	<td>{{$user->emp_id}}</td>
                                             <td>{{$user->leave_type}}</td>
                                             <td>{{$user->leave_details}}</td>
                                             <td>{{$user->start_date}}</td>
                                             <td>{{$user->end_date}}</td>
                                             <td>{{$user->address}}</td>
                                             <td>{{$user->phone_no}}</td>
                                  
                                   	</tr>
                                @endforeach
							</tbody>
						</table>
                        <br />
                        {{ $users->links() }}

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
 <div class="modal fade" id="newModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"> Add Leave</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
    <form method="post" action="{{ Route('add-leave') }}">
        @csrf
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label class="col-form-label">Start Date</label>
                    <input class="form-control" type="date" value="{{ old('start_date') }}" required name="start_date">
                </div>
                <div class="form-group">
                    <label class="col-form-label">Leave Type</label>
                    <select class="form-select" required name="leave_type">
                        <option value="Annual Leave" {{ old('leave_type') == 'Annual Leave' ? 'selected' : '' }}>Annual Leave</option>
                        <option value="Emergence Leave" {{ old('leave_type') == 'Emergence Leave' ? 'selected' : '' }}>Emergence Leave</option>
                        <option value="Maternity Leave" {{ old('leave_type') == 'Maternity Leave' ? 'selected' : '' }}>Maternity Leave</option>
                        <option value="Examination Leave" {{ old('leave_type') == 'Examination Leave' ? 'selected' : '' }}>Examination Leave</option>
                        <option value="Paternity Leave" {{ old('leave_type') == 'Paternity Leave' ? 'selected' : '' }}>Paternity Leave</option>
                        <option value="Compassionate Leave" {{ old('leave_type') == 'Compassionate Leave' ? 'selected' : '' }}>Compassionate Leave</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="col-form-label">Contact Phone</label>
                    <input class="form-control" type="text" value="{{ old('contact_phone') }}" required name="contact_phone">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label class="col-form-label">End Date</label>
                    <input class="form-control" type="date" value="{{ old('end_date') }}" required name="end_date">
                </div>
                <div class="form-group">
                    <label class="col-form-label">Address</label>
                    <input class="form-control" type="text" value="{{ old('address') }}" required name="address">
                </div>
                <div class="form-group">
                    <label class="col-form-label">Details</label>
                    <textarea class="form-control" required name="details">{{ old('details') }}</textarea>
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



   <!-- SEARCH MODAL START -->
   <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header  bg-primary text-white">
                <h5 class="modal-title">User Search</h5>
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
    document.getElementById("reference_number").placeholder = "Enter user's ID Number ...";
    document.getElementById('reference_number').name = 'id_number';
    function searchBy(search_by)
    {
        if(search_by.value == "id_number")
        {
            document.getElementById("reference_number").type = "text";
            document.getElementById("reference_number").placeholder = "Enter user's ID Number ...";
            document.getElementById('reference_number').name = 'id_number';
        }
        else if(search_by.value == "phone_number")
        {
            document.getElementById("reference_number").maxlength = "9";
            document.getElementById("reference_number").type = "number";
            document.getElementById("reference_number").placeholder = "Enter user's Phone (e.g. 766192332) ...";
            document.getElementById('reference_number').name = 'phone_number';
            
        }
        else
        {
            document.getElementById("reference_number").type = "text";
            document.getElementById("reference_number").placeholder = "Enter user's First Name or Middle Name or Last Name ...";
            document.getElementById('reference_number').name = 'cname';
        }
    }
 </script>
  @endpush
@endsection
