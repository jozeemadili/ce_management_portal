@extends('layouts.admin.master')

@section('title', 'Pledge Confirmed')

@push('css')
@include('portal.pledges.partials.styles')
<style>
    .confirm-icon {
        width: 80px; height: 80px; border-radius: 50%; background: #e6f7ee; color: #0f9d58;
        display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 16px;
    }
    .confirm-ref {
        font-family: monospace; font-size: 1.4rem; font-weight: 700; letter-spacing: .05em;
        background: #f0f3f9; padding: 8px 18px; border-radius: 10px; display: inline-block;
    }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Pledge Confirmed</h3>
    @endslot
    <li class="breadcrumb-item"><a href="{{ route('my-pledges.browse') }}">Pledges</a></li>
    <li class="breadcrumb-item active">Confirmation</li>
@endcomponent

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card pledge-card">
<div class="card-body text-center py-5">

    <div class="confirm-icon"><i class="icofont icofont-check-circled"></i></div>
    <h4 class="mb-1">Thank you for your pledge!</h4>
    <p class="text-muted">Your commitment has been recorded successfully.</p>

    <div class="my-3">
        <span class="confirm-ref">{{ $pledge->pledge_reference }}</span>
    </div>

    <div class="row text-start mt-4">
        <div class="col-6 mb-3">
            <small class="text-muted d-block">Campaign</small>
            <strong>{{ $campaign->name }}</strong>
        </div>
        <div class="col-6 mb-3">
            <small class="text-muted d-block">Pledged Amount</small>
            <strong>{{ $campaign->currency }} {{ number_format($pledge->amount, 2) }}</strong>
        </div>
        <div class="col-6 mb-3">
            <small class="text-muted d-block">Frequency</small>
            <strong>{{ ucfirst(str_replace('_',' ',$pledge->frequency)) }}</strong>
        </div>
        <div class="col-6 mb-3">
            <small class="text-muted d-block">Date</small>
            <strong>{{ $pledge->pledged_at->format('d M Y, H:i') }}</strong>
        </div>
        <div class="col-6 mb-3">
            <small class="text-muted d-block">Status</small>
            <span class="badge-pill badge-status-{{ $pledge->status }}">{{ ucfirst(str_replace('_',' ',$pledge->status)) }}</span>
        </div>
        <div class="col-6 mb-3">
            <small class="text-muted d-block">Anonymous Display</small>
            <strong>{{ $pledge->anonymous_display ? 'Yes' : 'No' }}</strong>
        </div>
    </div>

    <a href="{{ route('my-pledges.index') }}" class="btn btn-primary mt-2">
        <i class="icofont icofont-listing-box"></i> View My Pledges
    </a>
    <a href="{{ route('my-pledges.browse') }}" class="btn btn-outline-secondary mt-2">
        Back to Campaigns
    </a>

</div>
</div>
</div>
</div>
</div>

@endsection
