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
    <!-- <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#searchModal">Search <i class="icofont icofont-search-alt-1"></i></button></li> -->
      

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
      <!DOCTYPE html>
<html lang="en">
    <head>
        
        <title>NDANI INVESTIMENT CORPORATION LIMITED</title>
        <style>
     #invoice {
  padding: 30px;
}

.invoice {
  position: relative;
  background-color: #FFF;
  min-height: 680px;
  padding: 15px;
}

.invoice header {
  padding: 10px 0;
  margin-bottom: 20px;
  border-bottom: 1px solid #223673;
}

.invoice .company-details {
  text-align: left;
}

.invoice .company-details .name {
  margin-top: 0;
  margin-bottom: 0;
}

.invoice .contacts {
  margin-bottom: 20px;
}

.invoice .invoice-to {
  text-align: left;
}

.qrcode {
  text-align: right;
}

.invoice .invoice-to .to {
  margin-top: 0;
  margin-bottom: 0;
}

.invoice .invoice-details {
  text-align: right;
}

.invoice .invoice-details .invoice-id {
  margin-top: 0;
  color: {{ $quotation->company->color }};
}

.invoice main {
  padding-bottom: 50px;
}

.invoice main .thanks {
  margin-top: -110px;
  font-size: 2em;
  margin-bottom: 10px;
}

.invoice main .notices {
  padding-left: 6px;
  border-left: 6px solid {{ $quotation->company->color }};
}
.invoice main .notice2 {
  padding-right: 6px;
  border-right: 6px solid {{ $quotation->company->color }};
}

.invoice main .notices .notice {
  font-size: 1.2em;
}

.invoice table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin-bottom: 20px;
}

.invoice table td,
.invoice table th {
  padding: 15px;
  /* border-bottom: 1px solid #fff; */
  background: none; /* Background removed */
}

.invoice table th {
  white-space: nowrap;
  font-weight: 400;
  font-size: 16px;
}

.invoice table td h3 {
  margin: 0;
  font-weight: 400;
  font-size: 1em;
}

.invoice table .qty,
.invoice table .total,
.invoice table .unit {
  text-align: center;
  font-size: 1.2em;
}

.invoice table .no {
  font-size: 1.2em;
}

.invoice table .unit {
  background: none; /* Background removed */
}

.invoice table .total {
  color: {{ $quotation->company->color }};
}

.invoice table tbody tr:last-child td {
  /* border: none; */
}

.invoice table tfoot td {
  background: none; /* Background removed */
  border-bottom: none;
  white-space: nowrap;
  text-align: right;
  padding: 10px 20px;
  font-size: 1.2em;
  border-top: 1px solid {{ $quotation->company->color }};
}

.invoice table tfoot tr:first-child td {
  /* border-top: none; */
}

.invoice table tfoot tr:last-child td {
  font-size: 1.4em;
}

.invoice table tfoot tr td:first-child {
  /* border: none; */
}

.invoice footer {
  width: 100%;
  text-align: center;
  font-size: 12px;
  color: #777;
  border-top: 1px solid {{ $quotation->company->color }};
  padding: 8px 0;
}
.invoice .company-details-heading {
        text-align: center;
        font-size: 15px
        
        }
.invoice footer {
  position: absolute;
  bottom: 10px;
}

.invoice>div:last-child {
}

.th {
  background-color: white;
  color: black;
}

.page-break {
  page-break-after: always;
}

                        </style>
    </head>
    <body style="margin: 20px auto;">
      <div id="invoice">
        <div class="invoice overflow-auto">
            <div style="min-width: 600px">
                <header>
                    <div class="row">
                        <div class="col company-details">
                            <table>
                                <tr>
                                    <td>
                                    {{-- <img src="{{ asset('/assets/images/logo/azania_logo.jpg') }}" data-holder-rendered="true" width="15%" /> --}}
                                   
                                      <img 
                                        src="{{ asset('assets/images/logo/azania_logo.jpg') }}" 
                                        alt="oda" 
                                        style="width: 15%; border-radius: 50%;" 
                                      />
                                      
                                    </td>
                                    
                                    <td>
                                  
                                    </td>
                                </tr>
                            </table>
                        </div>
  
                    </div>
                </header>

                <main>
                <div class="col company-details-heading">

                  @if($quotation->status == 'Pending')
                  <div class="text-gray-light"><b> PROFOMA INVOICE </b></div>
                  @elseif($quotation->status == 'Confirmed')
                  <div class="text-gray-light"><b> INVOICE </b></div>
                  @elseif($quotation->status == 'Paid')
                  <div class="text-gray-light"><b> INVOICE </b></div>
                  @else
                  <div class="text-gray-light"><b> INVOICE </b></div>
                  @endif
                     
                </div>
                <div class="col invoice-details">
                <div><b>Invoice No : </b> ND000{{ $quotation->id }}/025 </div>
                <div><b>Control No : </b> {{ $quotation->control_no }}</div>
                <div><b>Date : </b> {{ $quotation->invoice_date->format('d M Y')}} </div>
                </div>

                <div class="row contacts">
               

                        <div class="col invoice-to">
                            <div class="text-gray-light"> THE BUYER (INVOICE TO) :</div>
                            <h6 class="to"> {{ strtoupper($quotation->customer->name) }}  </h6>
                            <div class="address">+255{{ $quotation->customer->phone }} | {{ $quotation->customer->email }}</div>
                            <div>TIN: {{ $quotation->customer->tin }} VRN : {{ $quotation->customer->tin }} </div>
                            <div>P.O.BOX {{ $quotation->customer->physical_addres }}</div>
                        </div>
<br/>

                        <table border="1" cellspacing="0" cellpadding="0">
                        <thead >
                            <tr>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">#</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">PRODUCT NAME</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">BARCODE</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">QUANTITY</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">SUB TOTAL</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">TOTAL</th>
                                <th style="background-color: {{$quotation->company->color}}; color:white;">ACTION</th>
                            </tr>
                           
                           
                        </thead>
                        <tbody>
        @php
            $total = 0;
        @endphp

        
        <tbody>
        @php
            $total = 0;
        @endphp

        @foreach ($quotation->invoice_items as $item)
            @php
                $subTotal = $item->price * $item->qty;
                $total += $subTotal;
            @endphp
            <tr>
              
              

                <td>{{$loop->index + 1}}.</td>
                <td> {{ $item->market_price?->chick_category?->name;}}</td>
                <td> {{ $item->market_price?->company?->name;}}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ number_format($item->price, 2) }} TZS</td> 
                <td>{{ number_format($subTotal, 2) }} TZS</td>
                <td>
                @if($quotation->status == 'Pending')
                 <a class='btn btn-outline-info btn-xs'  data-bs-toggle="modal"  data-bs-target="#editqtyModal{{$item->id}}">Edit Qnty</a>
                  &nbsp;&nbsp;&nbsp;
                  <a  class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#approvalModal{{$item->id}}"><i class='fa fa-tags'></i> Discount</a>
                  @else
                  <!-- <a class='btn btn-outline-secondary btn-xs'>Can not Edit, Invoice is Confemed By Customer!</a> -->
                  <!-- <a  class="btn btn-info btn-xs" data-bs-toggle="modal" data-bs-target="#returnToStock{{$item->id}}"> Return To Stock</a> -->
                  @endif
                </td>
            </tr>
        @endforeach
      
    </tbody>
    <tfoot>
      <tr>
                          
                            <td colspan="5">Subtotal</td>
                              <td>{{ number_format($total,2,'.',',') }} TZS</td>
                            </tr>
                            <tr>
                              <td></td>
                              <td colspan="4">Tax  0%</td>
                              <td>{{ number_format(0*$total,2,'.',',') }} TZS</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td colspan="4">GRAND TOTAL</td>
                                <td>{{ number_format($total+(0*$total),2,'.',',') }} TZS</td>
                            </tr>
                        </tfoot>
                    </table>
                        
                    </div>
                    <table>
                        <tr style="font-size: 9px; ">
                            <td>
                            <div class="notices">
                        <div>NOTICE:</div>
                        <ul>
                          <li> <div class="notice">All Figures above are in Tanzania Shillings (TZS)</div></li>
                          <li><div class="notice">Terms Of Payment : 14 Days</div></li>
                        </ul>
                       
                        
                    </div>
                    <div class="notices">
                        <ul>
                          {{-- <li><div class="notice">Ndani Investment Corporation Limited</div></li>
                          <li><div class="notice">82 MTAA WA COTEX,</div></li>
                          <li><div class="notice">CHUMBUNI,71112</div></li>
                          <li><div class="notice">MJINI MAGHARIBI ,</div></li>
                          <li><div class="notice">ZANZIBAR – TANZANIA.</div></li> --}}
                        </ul>
                    </div>
                            </td>
                            <td style="text-align: right;">
                            <div class="notice2">
                            {{-- <div class="notice"><i>www.gaaws.go.tz</i></div>
                            <div class="notice"><i>Email: info@gaaws.go.tz</i></div>
                            <div class="notice"><i> Simu:  +255 658 966 316</i></div> --}}
                            </div>
                        </td>
                        </tr>
                      </table>
                   
                </main>
                <footer>                                     
                 Corporation Limited                                   
                </footer>
            </div>
            <div></div>
        </div>
    </div>
    

 
    </body>
</html>
       
      </div>
  </div>




  @push('scripts')
  @endpush
@endsection

@foreach($quotation->invoice_items as $item)
<div class="modal fade" id="editqtyModal{{$item->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Edit Quantity For Item : {{ $item->market_price?->chick_category?->name;}}</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="post" action="{{ Route('edit-item-invoice-qty') }}">
                    @csrf
                <div class="form-group">

                                <label class="col-form-label" > Enter New Quantity </label>
                                <input class="form-control" type="text" value="{{ old('branch_name') }}" required  name="new_qty">
                                <i>Previous Quantity was {{ number_format($item->qty, 2) }}</i>
                                <input type="hidden" name="item_id"value="{{$item->id}}">
                                <input type="hidden" name="invoice_id"value="{{$invoice_id}}">
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
@foreach($quotation->invoice_items as $item)
<div class="modal fade" id="returnToStock{{$item->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"> Return Item : {{ $item->market_price?->chick_category?->name;}} To stock</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="post" action="{{ Route('remove-item-from-invoice') }}">
                    @csrf
                <div class="form-group">

                                <label class="col-form-label" > Quantity To Return To Stock </label>
                                <input class="form-control" type="number" required value="{{ $item->qty }}" required  name="qty_to_return" min="1" max="{{ $item->qty }}">

                                <i>Sold Quantity was {{ number_format($item->qty, 2) }}</i>
                                <input type="hidden" name="item_id"value="{{$item->id}}">
                                <input type="hidden" name="invoice_id"value="{{$invoice_id}}">
                                <div class="form-group">
                            <label class="col-form-label">Reason For Returning</label>
                            <textarea class="form-control" name="reason_for_retuning" rows="3" placeholder="Briefly describe the product..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
                <button class="btn btn-primary" type="submit" >Confirm & Discount</button>
                </form>
            </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@foreach($quotation->invoice_items as $item)
<div class="modal fade" id="approvalModal{{$item->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Enter Discount Amount  For Item : {{ $item->market_price?->chick_category?->name;}}</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="post" action="{{ Route('edit-item-invoice-price') }}">
                    @csrf
                <div class="form-group">

                                <label class="col-form-label" > Enter New Amount </label>
                                <input class="form-control" type="text" value="{{ old('branch_name') }}" required  name="discount_amount">
                                <i>Previous Amount was {{ number_format($item->price, 2) }} TZS</i>
                                <input type="hidden" name="item_id"value="{{$item->id}}">
                                <input type="hidden" name="invoice_id"value="{{$invoice_id}}">
                    </div>
                    <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
                <button class="btn btn-primary" type="submit" >Confirm & Discount</button>
                </form>
            </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach


 
