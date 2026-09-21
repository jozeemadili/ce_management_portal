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

    
    
    <li class="breadcrumb-item">{{ucfirst(explode('-', Route::currentRouteName())[0])}}</li>
    <li class="breadcrumb-item active">{{ucfirst(explode('-', Route::currentRouteName())[1])}}</li>
  @endcomponent
  
  <div class="container-fluid">
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
      <div class="row">
        @if(isset($Products))
          <div class="col-sm-12">
              <div class="card">
                  <div class="card-body">
                    <ul class="nav nav-tabs border-tab" id="top-tab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="vehicles-tab" data-bs-toggle="tab" href="#vehicles" role="tab" aria-controls="vehicles" aria-selected="true"><i class="icofont icofont-user-alt-3"></i>Product Edited Details</a></li>
                        <li class="nav-item"><a class="nav-link" id="contact-top-tab" data-bs-toggle="tab" href="#policies" role="tab" aria-controls="policies" aria-selected="false"><i class="icofont icofont-shield"></i>Product Transfer Details</a></li>
                    </ul>
                    <div class="tab-content" id="top-tabContent">
                        <div class="tab-pane fade active show" id="vehicles" role="tabpanel" aria-labelledby="vehicles-tab"> 
                  
<div class="ribbon-wrapper card">
    <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-primary">New Product Details</div>
                    <div class="row">
                    <div class="col-lg-6">
                    <ul class="list-group">
         
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Product Name<span class="badge badge-primary rounded-pill">{{ $Products->product_name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Category <span class="badge badge-primary rounded-pill">{{strtoupper($Products->Category->name)}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Store <span class="badge badge-primary rounded-pill">{{strtoupper($Products->Store->name)}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Barcode <span class="badge badge-primary rounded-pill">{{ $Products->barcode }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Descreption <span class="badge badge-primary rounded-pill">{{ $Products->description }}</span>
                            </li>
                            
                    </ul>
                    </div>
                    
                    <div class="col-lg-6">
                        <ul class="list-group">
     
		<!-- 'company_id',
		'expire_date',
		'created_by',
		'reg_at',
		'',
		'qty_sold',
		'invoiced_qty',
	 -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                Qantity <span class="badge badge-primary rounded-pill">{{ $Products->qty }} {{ $Products->unit_of_measuer }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Buying Price <span class="badge badge-primary rounded-pill">{{ $Products->purchasing_price }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Selling Price <span class="badge badge-primary rounded-pill">{{ $Products->selling_price }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Remained <span class="badge badge-primary rounded-pill">{{ $Products->qty_remained }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Status <span class="badge badge-primary rounded-pill">{{ $Products->status }}</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                </div>
</div>

<div class="ribbon-wrapper card">
    <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-primary">Old Details</div>
                    <div class="row">
                 

<!-- Display Edited Products -->
@if(isset($Products['edited_products']) && count($Products['edited_products']) > 0)
    <h5>Edited Products</h5>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>old Product Name</th>
                <th>old Quantity</th>
                <th>Category</th>
                <th>Purchasing Price</th>
                <th>Selling Price</th>
                <th>Description</th>
                <th>Edited By</th>
                <th>Reason for Editing</th>
                <th>Date Edited</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Products->edited_products as $edited)
                <tr>
                    <td>{{ $edited['product_name'] }}</td>
                    <td>{{ $edited['qty'] }}</td>
                    <td>{{strtoupper($edited->Category->name)}}</td>
                    <td>{{ number_format($edited['purchasing_price']) }} TZS</td>
                    <td>{{ number_format($edited['selling_price']) }} TZS</td>
                    <td>{{ $edited['description'] }}</td>
                    <td>{{ $edited->user->first_name}}</td>
                    <td>{{ $edited['reason_for_editing'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($edited['date_edited'])->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif


                    </div>
                </div>
</div>

<div class="ribbon-wrapper card">
    <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-primary">Action</div>
                    <div class="row">
                    <div class="col-lg-6">
                     <a href='{!! Route('edited-product-adminidtration', ['id' => $Products->id, 'status' => 'Approve']) !!}' class='btn btn-outline-primary w-100'>Approve </a>
                    </div>
                    <div class="col-lg-6">
                    <a href='{!! Route('edited-product-adminidtration', ['id' => $Products->id, 'status' => 'Reject']) !!}' class='btn btn-outline-danger w-100'>Reject </a>
                    </div>
                    </div>
                </div>
</div>
                        </div>
                        <div class="tab-pane fade" id="policies" role="tabpanel" aria-labelledby="policies-tab"> 
                            <!-- transfer details -->
                            <div class="ribbon-wrapper card">
    <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-secondary">Current Product Details</div>
                    <div class="row">
                    <div class="col-lg-6">
                    <ul class="list-group">
         
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Product Name<span class="badge badge-primary rounded-pill">{{ $Products->product_name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Category <span class="badge badge-primary rounded-pill">{{strtoupper($Products->Category->name)}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Store <span class="badge badge-primary rounded-pill">{{strtoupper($Products->Store->name)}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Barcode <span class="badge badge-primary rounded-pill">{{ $Products->barcode }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Descreption <span class="badge badge-primary rounded-pill">{{ $Products->description }}</span>
                            </li>
                            
                    </ul>
                    </div>
                    
                    <div class="col-lg-6">
                        <ul class="list-group">
     
		<!-- 'company_id',
		'expire_date',
		'created_by',
		'reg_at',
		'',
		'qty_sold',
		'invoiced_qty',
	 -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                Qantity <span class="badge badge-primary rounded-pill">{{ $Products->qty }} {{ $Products->unit_of_measuer }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Buying Price <span class="badge badge-primary rounded-pill">{{ $Products->purchasing_price }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Selling Price <span class="badge badge-primary rounded-pill">{{ $Products->selling_price }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Remained <span class="badge badge-primary rounded-pill">{{ $Products->qty_remained }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Status <span class="badge badge-primary rounded-pill">{{ $Products->status }}</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                </div>
</div>

<div class="ribbon-wrapper card">
    <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-secondary">Transfer Details</div>
                    <div class="row">
                 

<!-- Display Edited Products -->
@if(isset($transfer_details) && count($transfer_details) > 0)
    <h5>Transfer Details</h5>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>From Store</th>
                <th>To store</th>
                <th>Transfer Details</th>
                <th>quantity_transfered</th>
                <th>status</th>
                <th>transfered_by</th>
                <th>date_transfared</th>
                
            </tr>
        </thead>
        <tbody>
            @foreach($transfer_details as $transfer_det)
                <tr>
              
                    <td>{{ $transfer_det->product->product_name }}</td>
                    <td>{{ $transfer_det->store_from->name}}</td>
                    <td>{{ $transfer_det->store->name}}</td>
                    <td>{{ $transfer_det->transfer_details}}</td>
                    <td>{{ $transfer_det->quantity_transfered}}</td>
                    <td>{{ $transfer_det->status}}</td>
                    <td>{{ $transfer_det->user->first_name}}</td>
                    <td>{{ \Carbon\Carbon::parse($transfer_det['date_transfared'])->format('Y-m-d H:i:s') }}</td>
                   
                   
                </tr>
            @endforeach
        </tbody>
    </table>
@endif


                    </div>
                </div>
</div>

<div class="ribbon-wrapper card">
    <div class="card-body">
                    <div class="ribbon ribbon-clip ribbon-secondary">Action</div>
                    <div class="row">
                    <div class="col-lg-6">
                     <a href='{!! Route('transfer-product-adminidtration', ['id' => $Products->id, 'status' => 'Approve']) !!}' class='btn btn-outline-primary w-100'>Approve Transfer </a>
                    </div>
                    
                    </div>
                </div>
</div>
                            <!-- end trnasfer details -->
                        </div>
                      </div>
                  </div>
              </div>
          </div>
          @else
          <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                    <i class="icon-info-alt txt-danger"></i>
                       Employee Does not exit
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
                    </div>
          @endif
      </div>
  </div>




  @push('scripts')
  @endpush
@endsection