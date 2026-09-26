@extends('layouts.admin.master')

@section('title', 'Program Payments')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Program Payments</h3>
    @endslot
    <li class="breadcrumb-item">Programs</li>
    <li class="breadcrumb-item active">Payments to Confirm</li>
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

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<ul class="nav nav-pills mb-3">
    @foreach(['pending' => 'Awaiting Confirmation', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected'] as $key => $label)
    <li class="nav-item">
        <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('program-payments.index', ['status' => $key]) }}">
            {{ $label }} @if($key === 'pending' && $pendingCount)<span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>@endif
        </a>
    </li>
    @endforeach
</ul>

<div class="card prog-card">
    <div class="card-body">
        @if($status === 'pending')
        <p class="text-muted small">
            Payments submitted by attendees with a proof of payment. Open the proof, check it against your records
            (e.g. the M-Pesa statement), then confirm it - or reject it with a reason so they can submit again.
            Only confirmed payments count as paid and allow check-in.
        </p>
        @endif

        @if($payments->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
            <thead>
                <tr>
                    <th>Registration</th><th>Attendee</th><th>Program</th><th class="text-end">Amount</th>
                    <th>Method / Reference</th><th>Proof</th>
                    <th>{{ $status === 'pending' ? 'Submitted' : 'Reviewed' }}</th>
                    @if($status === 'pending')<th></th>@endif
                </tr>
            </thead>
            <tbody>
            @foreach($payments as $pay)
                @php $reg = $pay->registration; @endphp
                <tr>
                    <td>
                        <a href="{{ route('programs.show', $reg->program_id) }}" class="fw-semibold">{{ $reg->registration_reference }}</a>
                        <div class="text-muted small">
                            Due {{ number_format($reg->amount_due) }} &middot; confirmed {{ number_format($reg->totalPaid()) }}
                        </div>
                    </td>
                    <td>
                        {{ optional($reg->member)->first_name }} {{ optional($reg->member)->last_name }}
                        <div class="text-muted small">{{ optional(optional($reg->member)->church)->name }}</div>
                    </td>
                    <td>{{ optional($reg->program)->name }}</td>
                    <td class="text-end fw-semibold">{{ optional($reg->program)->currency }} {{ number_format($pay->amount) }}</td>
                    <td>
                        {{ $pay->payment_method ?? '—' }}
                        @if($pay->payment_reference)<div class="text-muted small">{{ $pay->payment_reference }}</div>@endif
                        <div class="text-muted small">Paid {{ $pay->payment_date->format('d M Y') }}</div>
                    </td>
                    <td>
                        @if($pay->proof_path)
                            <a href="{{ route('program-payments.proof', $pay->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light">
                                <i class="icofont icofont-attachment"></i> View Proof
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($status === 'pending')
                            {{ trim(optional($pay->recorder)->first_name . ' ' . optional($pay->recorder)->last_name) ?: '—' }}
                            <div class="text-muted small">{{ $pay->created_at->format('d M Y, H:i') }}</div>
                        @else
                            {{ trim(optional($pay->reviewer)->first_name . ' ' . optional($pay->reviewer)->last_name) ?: '—' }}
                            <div class="text-muted small">{{ optional($pay->reviewed_at)->format('d M Y, H:i') }}</div>
                            @if($pay->review_note)<div class="text-danger small">{{ $pay->review_note }}</div>@endif
                        @endif
                    </td>
                    @if($status === 'pending')
                    <td class="text-end" style="min-width: 230px;">
                        <form method="POST" action="{{ route('program-payments.confirm', $pay->id) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success"><i class="icofont icofont-check"></i> Confirm</button>
                        </form>
                        <form method="POST" action="{{ route('program-payments.reject', $pay->id) }}" class="d-inline-flex gap-1 mt-1">
                            @csrf
                            <input type="text" name="review_note" class="form-control form-control-sm" placeholder="Reason" required style="width: 120px;" aria-label="Reason for rejecting">
                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>
                    </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        {{ $payments->links() }}
        @else
        <div class="prog-empty"><i class="icofont icofont-money"></i><p class="mb-0">
            {{ $status === 'pending' ? 'No payments are waiting for confirmation.' : 'Nothing here yet.' }}
        </p></div>
        @endif
    </div>
</div>

</div>

@endsection
