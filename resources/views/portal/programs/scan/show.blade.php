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
                @if(empty($sessionOptions) && $registration->program && $registration->program->sessions->isNotEmpty())
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
                    @if(!empty($sessionOptions))
                    {{-- Pick which of today's sessions to check in for. --}}
                    <p class="modal-section-label mb-2">Check in for session</p>
                    <div class="list-group mb-3">
                        @foreach($sessionOptions as $option)
                            @php $s = $option['session']; $done = $option['checked_in_at']; @endphp
                            <label class="list-group-item d-flex align-items-center gap-2 {{ $done ? 'text-muted' : '' }}" style="cursor: {{ $done ? 'default' : 'pointer' }};">
                                <input class="form-check-input m-0" type="radio" name="session_id" value="{{ $s->id }}"
                                       @checked(optional($session)->id === $s->id) @disabled($done) required>
                                <span class="flex-grow-1">
                                    <strong>{{ $s->name }}</strong>
                                    <span class="text-muted">&middot; {{ $s->timeRange() }}</span>
                                </span>
                                @if($done)
                                    <span class="badge-pill badge-payment-paid">Checked in {{ $done->format('H:i') }}</span>
                                @elseif($option['open_now'])
                                    <span class="badge-pill badge-payment-pending">Now</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @endif
                    <button class="btn btn-primary w-100">
                        <i class="icofont icofont-check-circled"></i> Confirm Check-In
                    </button>
                </form>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="icofont icofont-warning"></i> {{ $message }}
                </div>
                @if(!empty($sessionOptions))
                <ul class="list-group mt-2">
                    @foreach($sessionOptions as $option)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $option['session']->name }} <span class="text-muted">&middot; {{ $option['session']->timeRange() }}</span></span>
                        @if($option['checked_in_at'])<span class="badge-pill badge-payment-paid">Checked in {{ $option['checked_in_at']->format('H:i') }}</span>@endif
                    </li>
                    @endforeach
                </ul>
                @endif
                @endif

                @if($registration->pendingPaymentsTotal() > 0)
                <div class="alert alert-info mt-3 mb-0">
                    {{ $registration->program->currency }} {{ number_format($registration->pendingPaymentsTotal()) }} submitted with proof is awaiting confirmation.
                    <a href="{{ route('programs.show', $registration->program_id) }}">Review it on the program page</a>.
                </div>
                @endif
                @if(!$registration->isSettled() && $registration->registration_status === 'registered' && $registration->payableAmount() > 0)
                {{-- Take payment at the door, then check in. --}}
                <div class="mt-3 border rounded p-3">
                    <p class="modal-section-label mb-2">Record Payment</p>
                    @include('portal.programs.partials.payment-form', [
                        'action' => route('program-payments.store', $registration->id),
                        'maxAmount' => $registration->payableAmount(),
                        'currency' => $registration->program->currency,
                        'paymentMethods' => $paymentMethods,
                        'proofRequired' => false,
                        'idPrefix' => 'scanpay',
                        'submitLabel' => 'Record Payment',
                    ])
                </div>
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
