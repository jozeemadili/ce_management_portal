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
    @if($claim->status == 'Accepted' && $claim->reference_number != null && count($claim->claim_intimations)<=0)
      <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newIntimation">Claim Intimation <i class="icofont icofont-checked"></i></button></li>
    @endif
    @if($claim->status == 'Accepted' && count($claim->claim_intimations) > 0)
      @if($claim->claim_intimations[0]->status == 'Accepted')
        @if(count($claim->claim_intimations[0]->claim_assessments) <= 0)
         <li><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newAssesment">Assessment <i class="icofont icofont-document-search"></i></button></li>
        @endif 
       @endif
    @endif
    @if(count($claim->claim_intimations) > 0)
    @if(count($claim->claim_intimations[0]->claim_assessments) >0)
    @if($claim->claim_intimations[0]->claim_assessments[0]->status == 'Accepted')
      @if(count($claim->claim_intimations[0]->claim_assessments[0]->claim_discharge_vouchers) <= 0 && count($claim->claim_intimations[0]->claim_rejections) <= 0)
        <li><button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#newDischargeVoucher">Discharge Voucher <i class="icofont icofont-holding-hands"></i></button></li>
      @endif
    @endif
    @endif
    @endif

    @if(count($claim->claim_intimations) > 0)
    @if(count($claim->claim_intimations[0]->claim_assessments) > 0)
    @if(count($claim->claim_intimations[0]->claim_assessments[0]->claim_discharge_vouchers) > 0)
      @if(count($claim->claim_intimations[0]->claim_assessments[0]->claim_payments) <= 0 && count($claim->claim_intimations[0]->claim_rejections) <= 0 )
        <li><button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#newPayment">Payment <i class="icofont icofont-credit-card"></i></button></li>
      @endif
      @elseif (count($claim->claim_intimations[0]->claim_assessments[0]->claim_discharge_vouchers) <= 0 && count($claim->claim_intimations[0]->claim_rejections) <= 0)
      <li><button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#newRejection">Reject <i class="icofont icofont-close-line"></i></button></li>
      @endif
    @endif
    @endif


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
      </div><br />
    @endforeach

    @if($message = Session::get('error'))
    <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
      <i class="icon-info-alt txt-danger"></i>
      {!! $message !!}
      <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
      </div><br />
    @endif
    
    @if($message = Session::get('success'))
      <div class="alert alert-success outline alert-dismissible fade show" role="alert">
      <i class="icofont icofont-check-circled"></i>
          {!! $message !!}
      <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" data-bs-original-title="" title=""></button>
      </div>
      <br />
     @endif

    <div class="row">
      <div class="card">
        <div class="card-body">
          <ul class="nav nav-tabs border-tab" id="top-tab" role="tablist">
              <li class="nav-item"><a class="nav-link active" id="claims_notifications-tab" data-bs-toggle="tab" href="#claims_notifications" role="tab" aria-controls="claims_notifications" aria-selected="true"><i class="icofont icofont-bell-alt"></i>Notification</a></li>
              <li class="nav-item"><a class="nav-link" id="claims_intimations-tab" data-bs-toggle="tab" href="#claims_intimations" role="tab" aria-controls="claims_intimations" aria-selected="true"><i class="icofont icofont-checked"></i>Intimation</a></li>
              <li class="nav-item"><a class="nav-link" id="assessment-top-tab" data-bs-toggle="tab" href="#assessment" role="tab" aria-controls="assessment" aria-selected="false"><i class="icofont icofont-document-search"></i>Assessment</a></li>
              <li class="nav-item"><a class="nav-link" id="contact-top-tab" data-bs-toggle="tab" href="#claims_discharge_vouchers" role="tab" aria-controls="claims_discharge_vouchers" aria-selected="false"><i class="icofont icofont-holding-hands"></i>Discharge Voucher</a></li>
              <li class="nav-item"><a class="nav-link" id="rejection-top-tab" data-bs-toggle="tab" href="#rejection" role="tab" aria-controls="rejection" aria-selected="false"><i class="icofont icofont-close-line"></i>Rejection</a></li>
              <li class="nav-item"><a class="nav-link" id="payments-top-tab" data-bs-toggle="tab" href="#payments" role="tab" aria-controls="payments" aria-selected="false"><i class="icofont icofont-credit-card"></i>Payments</a></li>
          </ul>

            <div class="tab-content" id="top-tabContent">

              <div class="tab-pane fade active show" id="claims_notifications" role="tabpanel" aria-labelledby="claims_notifications-tab">
                <ul class="list-group">
                  <li class="list-group-item d-flex justify-content-between align-items-center">Reference Number<span class="badge badge-primary rounded-pill">{{ $claim->reference_number }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Customer<span class="badge badge-primary rounded-pill">{{ strtoupper($claim->policy->quotation->customer->first_name) }} {{ strtoupper($claim->policy->quotation->customer->last_name) }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Product<span class="badge badge-primary rounded-pill">{{ strtoupper($claim->policy->quotation->risk->product->name) }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Risk<span class="badge badge-primary rounded-pill">{{ $claim->policy->quotation->risk->name }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Total Premium Amount (Icluding Tax)<span class="badge badge-primary rounded-pill">{{ number_format($claim->policy->quotation->premium_including_tax, 2, '.', ',') }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Claim Reference Number<span class="badge badge-primary rounded-pill">{{ $claim->claim_reference_number }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Loss Date<span class="badge badge-primary rounded-pill">{{ $claim->loss_date->format('d/m/Y H:i:s') }} | {{ $claim->loss_date->diffForHumans() }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Report Date<span class="badge badge-primary rounded-pill">{{ $claim->report_date->format('d/m/Y H:i:s') }} | {{ $claim->report_date->diffForHumans() }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Loss Nature<span class="badge badge-primary rounded-pill">{{  $claim->loss_nature->title }} ({{  $claim->loss_nature->description }})</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Loss Type<span class="badge badge-primary rounded-pill">{{  $claim->loss_nature->title }} ({{  $claim->loss_nature->description }})</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Created At<span class="badge badge-primary rounded-pill">{{ $claim->created_at->format('d/m/Y H:i:s') }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Reported By<span class="badge badge-primary rounded-pill">{{ strtoupper($claim->creator->first_name) }} {{ strtoupper($claim->creator->last_name) }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">TIRA Response<span class="badge badge-primary rounded-pill">{{ $claim->tira_response_status }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Status<span class="badge badge-primary rounded-pill">{{ $claim->status }}</span></li>
              </ul>
              </div>

              <div class="tab-pane fade" id="claims_intimations" role="tabpanel" aria-labelledby="contact-top-tab">
                @if(count($claim->claim_intimations) > 0)
                @foreach ($claim->claim_intimations as $claim_intimation)
                <ul class="list-group">
                  <li class="list-group-item d-flex justify-content-between align-items-center">Reference Number<span class="badge badge-primary rounded-pill">{{ $claim_intimation->reference_number }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Claim Intimation Date<span class="badge badge-primary rounded-pill">{{ $claim_intimation->intimation_date->format('d/m/Y H:i:s') }} | {{ $claim->loss_date->diffForHumans() }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Currency Code<span class="badge badge-primary rounded-pill">{{ $claim_intimation->currency_code }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Exchange Rate<span class="badge badge-primary rounded-pill">{{  $claim_intimation->exchange_rate }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Estimated Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_intimation->estimated_amount, 2, '.', ',') }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Reserve Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_intimation->reserve_amount, 2, '.', ',') }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Reserve Method<span class="badge badge-primary rounded-pill">{{ $claim_intimation->reserve_method }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Loss Assessment Option<span class="badge badge-primary rounded-pill">{{ $claim_intimation->loss_assessment_option == 1 ? 'In - House' : 'External'}}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Reported By<span class="badge badge-primary rounded-pill">{{ strtoupper($claim_intimation->creator->first_name) }} {{ strtoupper($claim_intimation->creator->last_name) }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">TIRA Response<span class="badge badge-primary rounded-pill">{{ $claim_intimation->tira_response_status }}</span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">Status<span class="badge badge-primary rounded-pill">{{ $claim_intimation->status }}</span></li>
                </ul>
                @endforeach
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Intimation found yet
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
              </div>

              <div class="tab-pane fade" id="assessment" role="tabpanel" aria-labelledby="assessment-top-tab">
                @if(count($claim->claim_intimations) > 0)
                @foreach ($claim->claim_intimations as $claim_intimation)
                @if(count($claim_intimation->claim_assessments) > 0)
                <table class="table table-xs">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Reference Number</th>
                      <th scope="col">Received</th>
                      <th scope="col">Approval</th>
                      <th scope="col">Amount</th> 
                      <th scope="col">Approved</th>
                      <th scope="col">Summary</th>
                      <th scope="col">Reassessment</th>
                      <th scope="col">TIRA</th>
                      <th scope="col">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                       @foreach($claim_intimation->claim_assessments as $claim_assessment)
                        <tr>
                          <th scope="row">{{$loop->index + 1}}.</th>
                          <td>{{$claim_assessment->reference_number}}</td>
                          <td>{{$claim_assessment->received_date->format('d/M/Y')}}</td>
                          <td>{{$claim_assessment->approval_date->format('d/M/Y')}}</td>
                          <td>{{number_format($claim_assessment->amount, 2, '.', ',')}}</td>
                          <td>{{number_format($claim_assessment->approved_amount, 2, '.', ',')}}</td>
                          <td>{{$claim_assessment->report_summary}}</td>
                          <td>{{$claim_assessment->is_reassessment}}</td>
                          <td>{{$claim_assessment->tira_response_status}}</td>
                          <td>{{$claim_assessment->status}}</td>
                        </tr>
                      @endforeach
                  </tbody>
                </table>
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Assessment found yet
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
                @endforeach
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Intimation found yet
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
              </div> 

              <div class="tab-pane fade" id="claims_discharge_vouchers" role="tabpanel" aria-labelledby="claims_discharge_vouchers-top-tab">
                @if(count($claim->claim_intimations) > 0)
                @foreach ($claim->claim_intimations as $claim_intimation)
                @if(count($claim_intimation->claim_assessments) > 0)
                @foreach ($claim_intimation->claim_assessments as $claim_assessment)
                @if(count($claim_assessment->claim_discharge_vouchers) > 0)
                <ul class="list-group">
                  @foreach($claim_assessment->claim_discharge_vouchers as $claim_discharge_voucher)
                      <li class="list-group-item d-flex justify-content-between align-items-center">Reference Number<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->reference_number }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Discharge Voucher Date<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->discharge_voucher_date->format('d/m/Y H:i:s') }} | {{ $claim_discharge_voucher->discharge_voucher_date->diffForHumans() }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Offer Communication Date Date<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->claim_offer_communication_date->format('d/m/Y H:i:s') }} | {{ $claim_discharge_voucher->claim_offer_communication_date->diffForHumans() }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Claimant Response Date<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->claimant_response_date->format('d/m/Y H:i:s') }} | {{ $claim_discharge_voucher->claimant_response_date->diffForHumans() }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Claim Offer Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_discharge_voucher->claim_offer_amount, 2, '.', ',') }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Adjustment Date<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->adjustment_date->format('d/m/Y H:i:s') }} | {{ $claim_discharge_voucher->adjustment_date->diffForHumans() }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Adjustment Reason<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->adjustment_reason }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Adjustment Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_discharge_voucher->adjustment_amount, 2, '.', ',') }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Reconciliation Date<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->reconciliation_date->format('d/m/Y H:i:s') }} | {{ $claim_discharge_voucher->reconciliation_date->diffForHumans() }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Reconciled Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_discharge_voucher->reconciled_amount, 2, '.', ',') }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Reconciliation Summary<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->reconciliation_summary }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Currency Code<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->currency_code }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Exchange Rate<span class="badge badge-primary rounded-pill">{{  $claim_discharge_voucher->exchange_rate }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Is Offer Accepted<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->offer_accepted }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Reported By<span class="badge badge-primary rounded-pill">{{ strtoupper($claim_discharge_voucher->creator->first_name) }} {{ strtoupper($claim_intimation->creator->last_name) }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">TIRA Response<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->tira_response_status }}</span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">Status<span class="badge badge-primary rounded-pill">{{ $claim_discharge_voucher->status }}</span></li>
                    @endforeach
                </ul>
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Discharge Voucher found yet. There might be a Claim rejection or not filled
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
                @endforeach
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Assessment found yet
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
                @endforeach
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Intimation found yet
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
              </div> 

              <div class="tab-pane fade" id="rejection" role="tabpanel" aria-labelledby="rejection-top-tab">
                @if(count($claim->claim_intimations) > 0)
                @foreach ($claim->claim_intimations as $claim_intimation)
                @if(count($claim_intimation->claim_rejections) > 0)
                <ul class="list-group">
                  @foreach ($claim_intimation->claim_rejections as $claim_rejection)
                    <li class="list-group-item d-flex justify-content-between align-items-center">Reference Number<span class="badge badge-primary rounded-pill">{{ $claim_rejection->reference_number }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Claim Rejection Date<span class="badge badge-primary rounded-pill">{{ $claim_rejection->rejection_date->format('d/m/Y H:i:s') }} | {{ $claim_rejection->rejection_date->diffForHumans() }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Currency Code<span class="badge badge-primary rounded-pill">{{ $claim_rejection->currency_code }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Exchange Rate<span class="badge badge-primary rounded-pill">{{  $claim_rejection->exchange_rate }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Claim Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_rejection->claim_amount, 2, '.', ',') }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Rejection Reason<span class="badge badge-primary rounded-pill">{{ $claim_rejection->rejection_reason }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Has resulted into Ligitation ?<span class="badge badge-primary rounded-pill">{{ $claim_rejection->resulted_litigation}}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Reported By<span class="badge badge-primary rounded-pill">{{ strtoupper($claim_rejection->creator->first_name) }} {{ strtoupper($claim_rejection->creator->last_name) }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">TIRA Response<span class="badge badge-primary rounded-pill">{{ $claim_rejection->tira_response_status }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Status<span class="badge badge-primary rounded-pill">{{ $claim_rejection->status }}</span></li>
                    @endforeach
                </ul>
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Rejection found yet. It might be Already Paid or not filled
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
                @endforeach
                @else 
                <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                  <i class="icon-info-alt txt-danger"></i>
                      No Any Claim Intimation found yet
                  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                 </div>
                @endif
              </div> 

              <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-top-tab">
                  @if(count($claim->claim_intimations) > 0)
                  @foreach ($claim->claim_intimations as $claim_intimation)
                  @if(count($claim_intimation->claim_assessments) > 0)
                  @foreach ($claim_intimation->claim_assessments as $claim_assessment)
                  @if(count($claim_assessment->claim_payments) > 0)
                  <ul class="list-group">
                    @foreach($claim_assessment->claim_payments as $claim_payment)
                        <li class="list-group-item d-flex justify-content-between align-items-center">Reference Number<span class="badge badge-primary rounded-pill">{{ $claim_payment->reference_number }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Payment Date<span class="badge badge-primary rounded-pill">{{ $claim_payment->payment_date->format('d/m/Y H:i:s') }} | {{ $claim_payment->payment_date->diffForHumans() }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Currency Code<span class="badge badge-primary rounded-pill">{{ $claim_payment->currency_code }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Exchange Rate<span class="badge badge-primary rounded-pill">{{ $claim_payment->exchange_rate }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Paid Amount<span class="badge badge-primary rounded-pill">{{  number_format($claim_payment->paid_amount, 2, '.', ',') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Net Premium Earned<span class="badge badge-primary rounded-pill">{{  number_format($claim_payment->net_premium_earned, 2, '.', ',') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Deductions<span class="badge badge-primary rounded-pill">{{  number_format($claim_payment->deductions, 2, '.', ',') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Were Parties Notified ? <span class="badge badge-primary rounded-pill">{{ $claim_payment->parties_notified }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Payment Method<span class="badge badge-primary rounded-pill">{{ $claim_payment->method == 1 ? 'CASH' : ($claim_payment->method == 2 ? 'CHEQUE' : 'EFT') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Resulted into Ligitation ? <span class="badge badge-primary rounded-pill">{{  $claim_payment->resulted_litigation }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Ligitation Reason<span class="badge badge-primary rounded-pill">{{ $claim_payment->litigation_reason }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Reported By<span class="badge badge-primary rounded-pill">{{ strtoupper($claim_payment->user->first_name) }} {{ strtoupper($claim_payment->user->last_name) }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">TIRA Response<span class="badge badge-primary rounded-pill">{{ $claim_payment->tira_response_status }}</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Status<span class="badge badge-primary rounded-pill">{{ $claim_payment->status }}</span></li>
                      @endforeach
                  </ul>
                  @else 
                  <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                    <i class="icon-info-alt txt-danger"></i>
                        No Any Claim Payment found yet. There might be a Claim rejection or not filled
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                   </div>
                  @endif
                  @endforeach
                  @else 
                  <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                    <i class="icon-info-alt txt-danger"></i>
                        No Any Claim Assessment found yet
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                  </div>
                  @endif
                  @endforeach
                  @else 
                  <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                    <i class="icon-info-alt txt-danger"></i>
                        No Any Claim Intimation found yet
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close" ></button>
                  </div>
                  @endif
              </div> 

            </div>

        </div>
    </div>
    </div>
  </div>

 <!-- NEW CLAIM INTIMATION MODAL START -->
 <div class="modal fade" id="newIntimation" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Claim Intimation</h5>
            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ Route('claim-intimation-registration') }}">
                @csrf
                <div class="row">
                    <input type="hidden" name="claim_notification_id" value="{{ $claim->id }}" />
                    <div class="col-lg-12">
                        <div class="form-group">
                          <label class="col-form-label" >Intimation Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('intimation_date') }}"  name="intimation_date" >
                      </div>
                        <div class="form-group">
                            <label class="col-form-label" >Estimated Amount</label>
                            <input class="form-control" type="number" step="0.0001" value="{{ old('estimated_amount') }}" required  name="estimated_amount">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label" >Reserve Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('reserve_amount') }}" required  name="reserve_amount">
                       </div>
                       <div class="form-group">
                        <label class="col-form-label" >Reserve Method</label>
                        <input class="form-control" type="text"  value="{{ old('reserve_method') }}" required  name="reserve_method">
                       </div>

                       <div class="form-group">
                        <label class="col-form-label">Loss Assessment Option</label><br>
                        <input type="radio" checked class="radio_animated" value="1" name="loss_assessment_option"> In - House
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="radio_animated" value="2" name="loss_assessment_option"> External
                    </div>

                    </div>
                  
                </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
            <button class="btn btn-primary" type="submit" >Confirm & Submit</button>
            </form>
        </div>
    </div>
</div>
  </div>
<!-- NEW CLAIM INTIMATION MODAL END -->

 <!-- NEW CLAIM ASSESSMENT MODAL START -->
 <div class="modal fade" id="newAssesment" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Claim Assessment</h5>
            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ Route('claim-assessment-registration') }}">
                @csrf
                <div class="row">
                    @if(count($claim->claim_intimations) > 0)
                      <input type="hidden" name="claim_intimation_id" value="{{ $claim->claim_intimations[0]->id }}" />
                    @endif
                    <div class="col-lg-12">
                        <div class="form-group">
                          <label class="col-form-label" >Received Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('received_date') }}"  name="received_date" >
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Approval Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('approval_date') }}"  name="approval_date" >
                         </div>
                        <div class="form-group">
                            <label class="col-form-label" >Assessment Amount</label>
                            <input class="form-control" type="number" step="0.0001" value="{{ old('amount') }}" required  name="amount">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label" >Approved Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('approved_amount') }}" required  name="approved_amount">
                       </div>
                       <div class="form-group">
                        <label class="col-form-label" >Report Summary</label>
                        <textarea class="form-control"  value="{{ old('report_summary') }}" required  name="report_summary"></textarea>
                       </div>

                       <div class="form-group">
                        <label class="col-form-label">Is re-assessment ?</label><br>
                        <input type="radio" class="radio_animated" value="Y" name="is_reassessment"> Yes
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" checked class="radio_animated" value="N" name="is_reassessment"> No
                    </div>

                    </div>
                  
                </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
            <button class="btn btn-primary" type="submit" >Confirm & Submit</button>
            </form>
        </div>
    </div>
</div>
  </div>
<!-- NEW CLAIM ASSESSMENT MODAL END -->

 <!-- NEW CLAIM DISCHARGE VOUCHER MODAL START -->
 <div class="modal fade" id="newDischargeVoucher" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Claim Discharge Voucher</h5>
            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ Route('claim-discharge-voucher-registration') }}">
                @csrf
                <div class="row">
                    @if(count($claim->claim_intimations))
                      @if(count($claim->claim_intimations[0]->claim_assessments) > 0)
                      <input type="hidden" name="claim_assessment_id" value="{{ $claim->claim_intimations[0]->claim_assessments[0]->id }}" />
                      @endif
                    @endif
                    <div class="col-lg-6">
                        <div class="form-group">
                          <label class="col-form-label" >Discharge Voucher Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('discharge_voucher_date') }}" required  name="discharge_voucher_date" >
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Communication Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('claim_offer_communication_date') }}" required  name="claim_offer_communication_date" >
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Offer Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('claim_offer_amount') }}" required  name="claim_offer_amount">
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Response Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('claimant_response_date') }}" required  name="claimant_response_date" >
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Adjustment Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('adjustment_date') }}"  name="adjustment_date" >
                        </div>
                        <div class="form-group">
                          <label class="col-form-label" >Adjustment Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('adjustment_amount') }}"  name="adjustment_amount">
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="col-form-label" >Adjustment Reason</label>
                        <textarea class="form-control"  value="{{ old('adjustment_reason') }}"  name="adjustment_reason"></textarea>
                      </div>
                        <div class="form-group">
                          <label class="col-form-label" >Reconciliation Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('reconciliation_date') }}"  name="reconciliation_date" >
                        </div>
                        <div class="form-group">
                          <label class="col-form-label" >Reconciliation Summary</label>
                          <textarea class="form-control"  value="{{ old('reconciliation_summary') }}"  name="reconciliation_summary"></textarea>
                        </div>
                        <div class="form-group">
                          <label class="col-form-label" >Reconciled Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('reconciled_amount') }}"  name="reconciled_amount">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Offer Accepted ?</label><br>
                          <input type="radio" checked class="radio_animated" value="Y" name="offer_accepted"> Yes
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          <input type="radio" class="radio_animated" value="N" name="offer_accepted"> No
                        </div>
                    </div>
                  
                </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
            <button class="btn btn-primary" type="submit" >Confirm & Submit</button>
            </form>
        </div>
    </div>
</div>
  </div>
<!-- NEW CLAIM DISCHARGE VOUCHER MODAL END -->


 <!-- NEW CLAIM PAYMENT MODAL START -->
 <div class="modal fade" id="newPayment" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Claim Payment</h5>
            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ Route('claim-payment-registration') }}">
                @csrf
                <div class="row">
                    @if(count($claim->claim_intimations))
                      @if(count($claim->claim_intimations[0]->claim_assessments) > 0)
                      <input type="hidden" name="assessment_id" value="{{ $claim->claim_intimations[0]->claim_assessments[0]->id }}" />
                      @endif
                    @endif
                    <div class="col-lg-6">
                        <div class="form-group">
                          <label class="col-form-label" >Payment Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('payment_date') }}" required  name="payment_date" >
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Paid Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('paid_amount') }}" required  name="paid_amount">
                         </div>
                        <div class="form-group">
                          <label class="col-form-label" >Net Premium Earned</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('net_premium_earned') }}"  name="net_premium_earned">
                          </div>
                          <div class="form-group">
                            <label class="col-form-label" >Deductions</label>
                            <input class="form-control" type="number" step="0.0001" value="{{ old('deductions') }}"  name="deductions">
                          </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="col-form-label">Resulted in Ligitation ?</label><br>
                        <input type="radio" class="radio_animated" value="Y" name="resulted_litigation"> Yes
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" checked class="radio_animated" value="N" name="resulted_litigation"> No
                      </div>
                        <div class="form-group">
                          <label class="col-form-label" >Ligitation Reason</label>
                          <textarea class="form-control"  value="{{ old('litigation_reason') }}"  name="litigation_reason"></textarea>
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Payment Method</label><br>
                          <input type="radio" checked class="radio_animated" value="1" name="method"> Cash
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          <input type="radio" class="radio_animated" value="2" name="method"> Cheque
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          <input type="radio" class="radio_animated" value="3" name="method"> EFT
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Offer Accepted ?</label><br>
                          <input type="radio" checked class="radio_animated" value="Y" name="parties_notified"> Yes
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          <input type="radio" class="radio_animated" value="N" name="parties_notified"> No
                        </div>
                    </div>
                  
                </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
            <button class="btn btn-primary" type="submit" >Confirm & Submit</button>
            </form>
        </div>
    </div>
</div>
  </div>
<!-- NEW CLAIM PAYMENT MODAL END -->

 <!-- NEW CLAIM REJECTION MODAL START -->
 <div class="modal fade" id="newRejection" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Claim Rejection</h5>
            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" ></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ Route('claim-rejection-registration') }}">
                @csrf
                <div class="row">
                    @if(count($claim->claim_intimations))
                      <input type="hidden" name="claim_intimation_id" value="{{ $claim->claim_intimations[0]->id }}" />
                    @endif
                    <div class="col-lg-12">
                        <div class="form-group">
                          <label class="col-form-label" >Rejection Date</label>
                          <input class="form-control" type="datetime-local" data-language="en" value="{{ old('rejection_date') }}" required  name="rejection_date" >
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Claim Amount</label>
                          <input class="form-control" type="number" step="0.0001" value="{{ old('claim_amount') }}" required  name="claim_amount">
                         </div>
                         <div class="form-group">
                          <label class="col-form-label" >Rejection Reason</label>
                          <textarea class="form-control"  value="{{ old('rejection_reason') }}"  name="rejection_reason"></textarea>
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Resulted in Ligitation ?</label><br>
                          <input type="radio" class="radio_animated" value="Y" name="resulted_litigation"> Yes
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                          <input type="radio" checked class="radio_animated" value="N" name="resulted_litigation"> No
                        </div>
                    </div>
                </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" >Close</button>
            <button class="btn btn-primary" type="submit" >Confirm & Submit</button>
            </form>
        </div>
    </div>
</div>
  </div>
<!-- NEW CLAIM REJECTION MODAL END -->

@endsection
