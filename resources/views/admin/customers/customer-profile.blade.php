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
    <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">Motor Quotation <i class="icofont icofont-plus-circle"></i></button></li>
    <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">Non Motor Quotation <i class="icofont icofont-plus-circle"></i></button></li>
    @endslot
    
    <li class="breadcrumb-item">{{ucfirst(explode('-', Route::currentRouteName())[0])}}</li>
    <li class="breadcrumb-item active">{{ucfirst(explode('-', Route::currentRouteName())[1])}}</li>
  @endcomponent
  
  <div class="container-fluid">
      <div class="row">
          <div class="col-sm-12">
              <div class="card">
                  <div class="card-body">
                    <ul class="nav nav-tabs border-tab" id="top-tab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="customers-tab" data-bs-toggle="tab" href="#customers" role="tab" aria-controls="customers" aria-selected="true"><i class="icofont icofont-user"></i>Customer Details</a></li>
                        <li class="nav-item"><a class="nav-link" id="vehicles-tab" data-bs-toggle="tab" href="#vehicles" role="tab" aria-controls="vehicles" aria-selected="true"><i class="icofont icofont-bus-alt-3"></i>Vehicles</a></li>
                        <li class="nav-item"><a class="nav-link" id="profile-top-tab" data-bs-toggle="tab" href="#quotations" role="tab" aria-controls="quotations" aria-selected="false"><i class="icofont icofont-paper"></i>Quotations</a></li>
                        <li class="nav-item"><a class="nav-link" id="contact-top-tab" data-bs-toggle="tab" href="#policies" role="tab" aria-controls="policies" aria-selected="false"><i class="icofont icofont-shield"></i>Policies</a></li>
                        <li class="nav-item"><a class="nav-link" id="claims-top-tab" data-bs-toggle="tab" href="#claimstab" role="tab" aria-controls="claims" aria-selected="false"><i class="icofont icofont-whisle"></i>Claims</a></li>
                    </ul>
      
                      <div class="tab-content" id="top-tabContent">

                        <div class="tab-pane fade active show" id="customers" role="tabpanel" aria-labelledby="customers-tab">
                          <p>@livewire('components.customer.detail', ['customer' => $customer])</p>
                        </div>

                        <div class="tab-pane fade" id="quotations" role="tabpanel" aria-labelledby="profile-top-tab">
                          <p>@livewire('components.vehicle.quotations', ['customer' => $customer])</p>
                        </div>

                        <div class="tab-pane fade" id="policies" role="tabpanel" aria-labelledby="contact-top-tab">
                          <p>@livewire('components.vehicle.policies', ['customer' => $customer])</p>
                        </div>

                        <div class="tab-pane fade" id="claimstab" role="tabpanel" aria-labelledby="claims-top-tab">
                            <p>@livewire('components.vehicle.claims', ['customer' => $customer])</p>
                        </div> 

                        <div class="tab-pane fade" id="vehicles" role="tabpanel" aria-labelledby="vehicles-top-tab">
                          <p>
                            <div class="table-responsive">
                              @if(count($customer->quotations)>0)
                              <table class="table table-xs">
                                <thead>
                                <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Owner</th>
                                      <th scope="col">Reg #</th>
                                      <th scope="col">Chasis #</th>
                                      <th scope="col">Make</th>
                                      <th scope="col">Model</th>
                                      <th scope="col">Body</th>
                                      <th scope="col">Capacity</th>
                                      <th scope="col">Fuel</th>
                                      <th scope="col">YOM</th>
                                      <th scope="col">Usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                      @foreach($customer->quotations as $quotation)
                                      @if($quotation->vehicle != null)
                                        <tr>
                                            <th scope="row">{{$loop->index + 1}}.</th>
                                            <td><a href='{{route('vehicle-profile', ['id' => $quotation->vehicle->id])}}'><small>{{strtoupper(explode(' ',$quotation->vehicle->owner_name)[0] .' '. (explode(' ',$quotation->vehicle->owner_name)[count(explode(' ',$quotation->vehicle->owner_name))-1]))}}</small></a></td>
                                            <td><a href='{{route('vehicle-profile', ['id' => $quotation->vehicle->id])}}'>{{$quotation->vehicle->registration_number}}</a></td>
                                            <td><a href='{{route('vehicle-profile', ['id' => $quotation->vehicle->id])}}'>{{$quotation->vehicle->chassis_number}}</a></td>
                                            <td>{{$quotation->vehicle->make}}</td>
                                            <td>{{$quotation->vehicle->model}}</td>
                                            <td>{{$quotation->vehicle->body_type}}</td>
                                            <td>{{number_format($quotation->vehicle->engine_capacity, 0, '.',',')}}</td>
                                            <td>{{$quotation->vehicle->fuel_used}}</td>
                                            <td>{{$quotation->vehicle->year_of_manufacture}}</td>
                                            <td>{{($quotation->vehicle->motor_usage == 1 ? "Private" : "Commercial")}}</td>
                                        </tr>
                                      @endif
                                      @endforeach
                                </tbody>
                              </table>
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
      </div>
  </div>

 <!-- NEW QUOTATION MODAL START -->
 <div class="modal fade" id="newModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">New Quotation Form</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
                <div style="padding-right: 2em;padding-left: 2em;">
                    {{-- @livewire('components.quotations.motor', ['customer' => $customer, 'vehicle' => null, 'company' => (Auth::user()->role == 'Insurer Admin' || Auth::user()->role == 'Insurer Staff') ? Auth::user()->company : null])    --}}
                    @livewire('components.quotations.motor', ['customer' => $customer, 'vehicle' => null, 'company' =>  Auth::user()->company ])   
                </div>
            </div>
        </div>
    </div>
    </div>
 <!-- NEW QUOTATION MODAL END -->


  @push('scripts')
  @endpush
@endsection