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
    <!-- <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#searchModal">Search <i class="icofont icofont-search-alt-1"></i></button></li> -->
      
        <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">New <i class="icofont icofont-plus-circle"></i></button></li>
  

    @endslot
    
    <li class="breadcrumb-item">{{ucfirst(explode('-', Route::currentRouteName())[0])}}</li>
    <li class="breadcrumb-item active">{{ucfirst(explode('-', Route::currentRouteName())[1])}}</li>
  @endcomponent
  
  <div class="container-fluid">
      <div class="row">
          <div class="col-sm-12">
          @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
                        @if(count($Branch)>0)
						<table class="table table-xs">
							<thead>
								<tr>
									<th scope="col">#</th>
									<th scope="col">product Name</th>
                                    <th scope="col">category</th>
                                    <th scope="col">store</th>
                                    <th scope="col">barcode</th>
                                    <th scope="col">quantity</th>
                                    <th scope="col">purchasing price</th>
                                    <th scope="col">selling price</th>
                                    <th scope="col"> Expected profit/loss</th>
                                
                                    <th scope="col">Status</th>
                                    <th scope="col">Created By</th>
                                    <th scope="col">Action</th>
								</tr>
							</thead>
							<tbody>
                                @foreach($Branch as $user)
                                <!-- SELECT `id`, ``, ``, ``, ``, ``, ``, ``, `description`, `company_id`, `expire_date`, `created_by`, `reg_at`, `status` FROM `products` WHERE 1 -->
								<tr>
									<th scope="row">{{$loop->index + 1}}.</th>
									<td><a href=""><small>{{strtoupper($user->product_name)}}</small></a></td>
                                    <td><a href=""><small>{{strtoupper($user->Category->name)}}</small></a></td>
                                    <td><a href=""><small>{{strtoupper($user->Store->name)}}</small></a></td>
                                    <td>{{$user->barcode}}</td>
                                    <td>{{number_format($user->qty, 2)}}</td>
                                    <td>{{number_format($user->purchasing_price, 2)}}</td>
                                    <td>{{number_format($user->selling_price, 2)}}</td>
                                    <td style="color: {{ ($user->qty * $user->selling_price) - ($user->qty * $user->purchasing_price)   < 0 ? 'red' : 'green' }};">
                                        {{ number_format(($user->qty * $user->selling_price) - ($user->qty * $user->purchasing_price), 2) }}
                                    </td>

                                    <td>{{$user->status}}</td>
                                    
                                    <td>{{$user->user->first_name}}</td>
                                    <td>
                                    <!-- <div class="pull-left"> <a href='{!! Route('edited-product-adminidtration', ['id' => $user->id, 'status' => 'Approve']) !!}' class='btn btn-outline-primary btn-xs'>Approve </a>

                                    <div class="pull-left"> <a href='{!! Route('edited-product-adminidtration', ['id' => $user->id, 'status' => 'Reject']) !!}' class='btn btn-outline-danger btn-xs'>Reject </a> -->

                                    <div class="pull-left"> <a href='{!! Route('product-preview', ['id' => $user->id]) !!}' class='btn btn-outline-info btn-xs'> view </a>
                                    
							</td>
                                   	</tr>
                                @endforeach
							</tbody>
						</table>
                        <br />
                        {{ $Branch->links() }}

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
                <h5 class="modal-title">Category Registration</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ Route('add-product') }}">
                    @csrf
                    <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Stock/Product</label>
                            <input class="form-control" type="text" required name="product_name" placeholder="Commodity Name...">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Qnty</label>
                            <input class="form-control" type="number" required name="qty" value="1">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Unit Of Measure</label>
                            <input class="form-control" type="text" required name="unit_of_measuer" value="">
                        </div>
                    </div>
                   
                    
                </div>

                <div class="row">
                    

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Purchasing Price</label>
                            <input class="form-control" type="text" required name="purchasing_price" placeholder="Purchasing Price...">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Selling Price</label>
                            <input class="form-control" type="text" required name="selling_price" placeholder="Selling Price...">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Store</label>
                            <select class="form-control" required name="store_id">
                            <option value="">--- Choose Store ---</option>  
                                        @foreach ($stores as $st)
                                    <option value='{{$st->id}}'>{{strtoupper($st->name)}} - {{strtoupper($st->physica_addres)}}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Details</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Briefly describe the product..."></textarea>
                        </div>
                        
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Barcode</label>
                            <input class="form-control" type="text" required name="barcode" value="{{ $barcodeValue }}">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Category</label>
                            <select class="form-control" required name="category">
                            <option value="">--- Choose Category ---</option>  
                                        @foreach ($Categories as $cat)
                                    <option value='{{$cat->id}}'>{{strtoupper($cat->name)}} </option>
                                    @endforeach
                            </select>
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

@foreach($Branch as $user)
<div class="modal fade" id="editqtyModal{{$user->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Edit Details For Item : {{ strtoupper($user->product_name) }}</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="post" action="{{ Route('edit-item-product-details') }}">
                    @csrf
                    <input type="hidden" name="product_id"value="{{$user->id}}">
                    <div class="row">
                    <div class="col-lg-4">

                        <div class="form-group">
                            <label class="col-form-label">Stock/Product</label>
                            <input class="form-control" type="text" required name="product_name" placeholder="Commodity Name..." value="{{ $user->product_name }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Qnty</label>
                            <input class="form-control" type="number" required name="qty" value="{{ $user->qty }}" >
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Unit Of Measure</label>
                            <input class="form-control" type="text" required name="unit_of_measuer" value="{{ $user->unit_of_measuer }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Purchasing Price</label>
                            <input class="form-control" type="text" required name="purchasing_price" placeholder="Purchasing Price..." value="{{ $user->purchasing_price }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Selling Price</label>
                            <input class="form-control" type="text" required name="selling_price" placeholder="Selling Price..." value="{{ $user->selling_price }}">
                        </div>
                    </div>
                    
                    
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Reason For Edit</label>
                            <textarea class="form-control" name="reason_for_editing" rows="3" placeholder="Briefly describe the product..." required></textarea>
                        </div>
                        
                        
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Barcode</label>
                            <input class="form-control" type="text" required readonly name="barcode" value="{{ $user->barcode }}">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label">Category </label> 
                            <select class="form-control" required name="category">
                           
                            <option value="">--- Choose Category ---</option>  
                                        @foreach ($Categories as $cat)
                                    <option value='{{$cat->id}}'>{{strtoupper($cat->name)}} </option>
                                    @endforeach
                            </select>
                            <i>Previous was Category in This : <b>{{ $user->Category->name }} Category</b></i>
                        </div>
                    </div>
                   
                </div>
                    <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
                <button class="btn btn-primary" type="submit" >Confirm & Edit Quantity</button>
                </form>
            </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
