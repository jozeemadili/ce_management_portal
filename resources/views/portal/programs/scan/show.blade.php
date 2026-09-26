@extends('layouts.admin.master')

@section('title', $registration->registration_reference)

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>{{ $registration->registration_reference }}</h3>
    @endslot
    <li class="breadcrumb-item">Programs</li>
    <li class="breadcrumb-item active">Scanned Registration</li>
@endcomponent

<div class="container-fluid">

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $error }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endforeach
@endif

@if(session('error'))
    <div class="alert alert-warning alert-dismissible fade show">
        {{ session('error') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-6 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Registration Details</p>

                <h5 class="mb-1">{{ optional($registration->member)->first_name }} {{ optional($registration->member)->last_name }}</h5>
                <p class="text-muted mb-1">{{ optional($registration->program)->name }}</p>
                <p class="text-muted mb-3"><i class="icofont icofont-building-alt"></i> {{ optional(optional($registration->member)->church)->name ?? '—' }}</p>

                <span class="badge-pill badge-status-{{ $registration->registration_status }} mb-3 d-inline-block">{{ ucfirst($registration->registration_status) }}</span>
                <span class="badge-pill badge-payment-{{ $registration->payment_status }} mb-3 d-inline-block">{{ $registration->paymentLabel() }}</span>

                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Reference</span>
                    <strong>{{ $registration->registration_reference }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Location</span>
                    <strong>{{ optional($registration->program)->location ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Date</span>
                    <strong>{{ optional(optional($registration->program)->start_date)->format('d M Y') ?? '—' }}</strong>
                </div>
                @if($session)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Session now</span>
                    <strong>{{ $session->name }} ({{ $session->timeRange() }})</strong>
                </div>
                @elseif($registration->program && $registration->program->sessions->isNotEmpty())
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Sessions</span>
                    <strong class="text-end">{{ $registration->program->sessionsLabel() }}</strong>
                </div>
                @endif
                @if((float) $registration->amount_due > 0)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Amount Due{{ $registration->pricedDesignation ? ' (' . ucwords($registration->pricedDesignation->name) . ')' : '' }}</span>
                    <strong>{{ $registration->program->currency }} {{ number_format($registration->amount_due) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Paid / Balance</span>
                    <strong>{{ number_format($registration->totalPaid()) }} / {{ number_format($registration->balance()) }}</strong>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Registered On</span>
                    <strong>{{ optional($registration->registered_at)->format('d M Y') }}</strong>
                </div>

                <hr class="my-3">

                @if($ok)
                <form method="POST" action="{{ route('program-scan.check-in', $registration->id) }}">
                    @csrf
                    <button class="btn btn-primary w-100">
                        <i class="icofont icofont-check-circled"></i> Confirm Check-In
                    </button>
                </form>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="icofont icofont-warning"></i> {{ $message }}
                </div>
                @endif

                @if(!$registration->isSettled() && $registration->registration_status === 'registered')
                {{-- Take payment at the door, then check in. --}}
                <form method="POST" action="{{ route('program-payments.store', $registration->id) }}" class="mt-3 border rounded p-3">
                    @csrf
                    <p class="modal-section-label mb-2">Record Payment</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="1" name="amount" class="form-control" value="{{ $registration->balance() ?: '' }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->name }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Reference</label>
                            <input type="text" name="payment_reference" class="form-control" placeholder="e.g. M-Pesa code">
                        </div>
                    </div>
                    <button class="btn btn-success w-100 mt-3"><i class="icofont icofont-money"></i> Record Payment</button>
                </form>
                @endif

                @if($registration->attendance)
                <div class="alert alert-success mt-3 mb-0">
                    <i class="icofont icofont-check-circled"></i> Last check-in {{ optional($registration->attendance->checked_in_at)->format('d M Y, H:i') }}@if($registration->attendance->session) &middot; {{ $registration->attendance->session->name }}@endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

</div>

@endsection
