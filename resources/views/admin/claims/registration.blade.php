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
    @endslot
    
    <li class="breadcrumb-item">{{ucfirst(explode('-', Route::currentRouteName())[0])}}</li>
    <li class="breadcrumb-item active">{{ucfirst(explode('-', Route::currentRouteName())[1])}}</li>
  @endcomponent
  
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
          <div class="card">
              <div class="card-body">
                  <p>
                    <div class="table-responsive">
                      @if(count($claims)>0)
                      <table class="table table-xs">
                          <thead>
                              <tr>
                                  <th scope="col">#</th>
                                  <th scope="col">Reference #</th>
                                  <th scope="col">Customer</th>
                                  <th scope="col">Product</th>
                                  <th scope="col">Loss Date</th>
                                  <th scope="col">Report Date</th>
                                  <th scope="col">Premium</th>
                                  <th scope="col">Status</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach($claims as $claim)
                              <tr>
                                  <th scope="row">{{$loop->index + 1}}.</th>
                                  <td><a href='{{route('claim-profile', ['id' => $claim->id])}}'>{{ $claim->reference_number }}</a></td>
                                  <td>{{strtoupper($claim->policy->quotation->customer->first_name)}} {{strtoupper($claim->policy->quotation->customer->last_name)}}</td>
                                  <td>{{strtoupper($claim->policy->quotation->risk->product->name)}}</td>
                                  <td>{{$claim->loss_date->format('d/m/Y')}}</td>
                                  <td>{{$claim->report_date->format('d/m/Y')}}</td>
                                  <td>{{number_format($claim->policy->quotation->total_premium_including_tax, 2, '.',',')}}</td>
                                  <td>{{$claim->status}}</td>
                              </tr>
                              @endforeach
                          </tbody>
                      </table>
                      <br />
                  
                      @else 
                      <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                          <i class="icon-info-alt txt-danger"></i>
                              No Claim Records created by you Found yet
                          <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                         </div>
                      @endif
                  </div>
                    <br />
                    {{ $claims->links() }}
                </div>
                  </p>
              </div>
          </div>
      </div>
  </div>

@endsection
