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
                        @if(count($branches)>0)
						<table class="table table-xs">
							<thead>
								<tr>
									<th scope="col">#</th>
									
                                    <th scope="col">Name</th>
									<th scope="col">Type</th>
									<th scope="col">Region</th> 
                                    <th scope="col">District</th>
                                    <th scope="col">Ward</th>
                                    <th scope="col">Street</th>
								</tr>
							</thead>
							<tbody>
                                @foreach($branches as $branch)
								<tr>
									<th scope="row">{{$loop->index + 1}}.</th>
                                   
                                    <td>{{$branch->name}}</td>
                                    <td>{{strtoupper($branch->type)}}</td>
                                   
                                    <td>{{$branch->street}}</td>
								</tr>
                                @endforeach
							</tbody>
						</table>
                        <br />
                        {{ $branches->links() }}

                        @else 
                        <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                            <i class="icon-info-alt txt-danger"></i>
								No Record Found yet
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
                <h5 class="modal-title">Branch Registration</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ Route('branches-add') }}">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="col-form-label" >Branch Name</label>
                                <input class="form-control" type="text" value="{{ old('name') }}" required  name="name">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Branch Type</label><br>
                                <input type="radio" checked class="radio_animated" value="Branch" name="type"> Branch
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="radio_animated" value="Head Office" name="type"> Head Office
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <label class="col-form-label" >Branch Street</label>
                                    <textarea class="form-control" type="text" value="{{ old('street') }}" required  name="street"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            @livewire('components.regions-districts-wards')
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
