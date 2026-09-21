@extends('layouts.admin.master')

@section('title')
  {{ ucfirst(str_replace('-', ' ', Route::currentRouteName())) }}
@endsection

@push('css')
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sweetalert2.css') }}">
@endpush

@section('content')
  @component('components.breadcrumb')
    @slot('breadcrumb_title')
      <h3>{{ ucfirst(str_replace('-', ' ', Route::currentRouteName())) }}</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
      @if (Auth::user()->role == 'ADMIN' || Auth::user()->role == 'Technician' || Auth::user()->role == 'Sales')
        <li>
          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newModal">
            New <i class="icofont icofont-plus-circle"></i>
          </button>
        </li>
      @endif
    @endslot

    <li class="breadcrumb-item">{{ ucfirst(explode('-', Route::currentRouteName())[0]) }}</li>
    <li class="breadcrumb-item active">{{ ucfirst(explode('-', Route::currentRouteName())[1]) }}</li>
  @endcomponent

  <div class="container-fluid">

    {{-- Error Alerts --}}
    @foreach ($errors->all() as $error)
      <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
        <i class="icon-info-alt txt-danger"></i> {{ $error }}
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endforeach

    {{-- Success Message --}}
    @if ($message = Session::get('success'))
      <div class="alert alert-success outline alert-dismissible fade show" role="alert">
        <i class="icofont icofont-check-circled"></i> {!! $message !!}
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <br />
    @endif

    <div class="row">
      @if (isset($Customers))
        <div class="col-sm-12">
          <div class="card">
            <div class="card-body">
              <ul class="nav nav-tabs border-tab" id="top-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="vehicles-tab" data-bs-toggle="tab" href="#vehicles" role="tab" aria-controls="vehicles" aria-selected="true">
                    
                    <i class="icofont icofont-user-alt-3"></i> Invoices
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="contact-top-tab" data-bs-toggle="tab" href="#policies" role="tab" aria-controls="policies" aria-selected="false">
                    <i class="icofont icofont-shield"></i> Payments
                  </a>
                </li>
              </ul>

              <div class="tab-content" id="top-tabContent">
                {{-- Invoices Tab --}}
                <div class="tab-pane fade show active" id="vehicles" role="tabpanel" aria-labelledby="vehicles-tab">
                  <div class="table-responsive">
                    @if(count($Invoice)>0)
                    <table class="table table-xs">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">invoice no</th>
                                <th scope="col">Type</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Invoice date</th>
                                <th scope="col">Total Quantity</th>
                                <th scope="col">Total Amount</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created By</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($Invoice as $user)
                            <?php
                                $totalQty = 0;
                                $totalAmount = 0;

                                foreach ($user['invoice_items'] as $item) {
                                    $totalQty += $item['qty'];
                                    $totalAmount += $item['qty'] * $item['price'];
                                }
                            ?>
                            <tr>
                                  <th scope="row">{{$loop->index + 1}}.</th>
                                <td><a href="#"><small>ND000{{ $user->id }}/025 </small></a></td>
                                <td>
                                @if($user->status == 'Pending')
                                 PROFOMAL INVOICE
                                @elseif($user->status == 'Confirmed')
                                INVOICE
                                @else
                                INVOICE
                                @endif
                                </td>
                                
                                <td><a href=""><small>{{$user->Customer->name}}</small></a></td>
                                <td>{{ \Carbon\Carbon::parse($user->invoice_date)->format('d/m/Y H:i:s') }}</td> <!-- Invoice Date -->
                                <td>{{number_format($totalQty, 2)}}</td>
                                <td>{{number_format($totalAmount, 2)}}</td>
                                <td>{{$user->status}}</td>
                                <td>{{$user->User->first_name}}</td>
                                <td>

                                <a href="{{ Route('invoice-download', ['id' => $user->id]) }}" class="btn btn-outline-primary btn-xs" style="margin-top: auto;" type="button">Print<i class="icofont icofont-printer"></i></a>

                                @if(Auth::user()->role == 'Sales') 
                                <a href="{{ Route('invoice-preview', ['id' => $user->id]) }}" class="btn btn-outline-primary btn-xs" style="margin-top: auto;" type="button">View <i class="icofont icofont-eye"></i></a>
                                
@endif
                                @if(Auth::user()->role == 'ADMIN'  | Auth::user()->role == 'Accountant') 
                                <a href="{{ Route('invoice-preview', ['id' => $user->id]) }}" class="btn btn-outline-primary btn-xs" style="margin-top: auto;" type="button">View <i class="icofont icofont-eye"></i></a>
                                @if($user->status == 'Pending')
                                <a href="{{ Route('invoice-status-update', ['id' => $user->id]) }}" class="btn btn-outline-primary btn-xs" style="margin-top: auto;" type="button">Confermed <i class="icofont icofont-tick-mark"></i></a>
                                @elseif($user->status == 'Confirmed')
                                <a href="{{ Route('invoice-status-paid', ['id' => $user->id]) }}" class="btn btn-outline-secondary btn-xs" style="margin-top: auto;" type="button">Mark Paid <i class="icofont icofont-money"></i></a>
                                @else
                                <a href="#" class="btn btn-outline-info btn-xs" style="margin-top: auto;" type="button">Paid <i class="icofont icofont-tick-mark"></i></a>
                                @endif
                                @endif
                                
                                

                                
                        </td>
                                   </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br />
                  

                    @else 
                    <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                        <i class="icon-info-alt txt-danger"></i>
                            No Records Found yet
                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                       </div>
                    @endif
                    <p class="text-muted">No invoices available.</p>
                  </div>
                </div>

                {{-- Payments Tab --}}
                <div class="tab-pane fade" id="policies" role="tabpanel" aria-labelledby="policies-tab">
                  <div class="table-responsive">
                    <p class="text-muted">No payments available.</p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      @else
        <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
          <i class="icon-info-alt txt-danger"></i> Employee does not exist.
          <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
@endpush

<!-- NEW QUOTATION MODAL START -->
<div class="modal fade" id="newModal" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-fullscreen" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Generate Control Number  for <b>{{ $Customers->name ?? 'N/A' }}</b></h5>
        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div style="padding-right: 2em; padding-left: 2em;">
          @if (isset($Customers))
            @livewire('sales-management.oparate-sales', ['Customers_details' => $Customers]) 
          @else
            <p class="text-danger">Customer details not found.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
