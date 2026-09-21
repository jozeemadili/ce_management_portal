@extends('layouts.admin.master')

@section('title', $pledge->pledge_reference)

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>{{ $pledge->pledge_reference }}</h3>
    @endslot
    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Scanned Pledge</li>
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

<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Pledge Details</p>

                <h5 class="mb-1">{{ optional($pledge->member)->first_name }} {{ optional($pledge->member)->last_name }}</h5>
                <p class="text-muted mb-1">{{ optional($pledge->campaign)->name }}</p>
                <p class="text-muted mb-3"><i class="icofont icofont-building-alt"></i> {{ optional(optional($pledge->member)->church)->name ?? '—' }}</p>

                <span class="badge-pill badge-status-{{ $pledge->status }} mb-3 d-inline-block">{{ ucfirst(str_replace('_',' ',$pledge->status)) }}</span>

                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Pledged</span>
                    <strong>{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Fulfilled</span>
                    <strong>{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->totalFulfilled(), 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Outstanding</span>
                    <strong>{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->outstanding(), 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Reference</span>
                    <strong>{{ $pledge->pledge_reference }}</strong>
                </div>

                @if($pledge->notes)
                <p class="mt-3 mb-0 text-muted"><small>{{ $pledge->notes }}</small></p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Record Fulfillment</p>

                @if($pledge->status === 'fulfilled' || $pledge->status === 'cancelled')
                <div class="alert alert-info mb-0">
                    This pledge is {{ $pledge->status }} and cannot receive further contributions.
                </div>
                @else
                <form method="POST" action="{{ route('pledge-contributions.store') }}">
                    @csrf
                    <input type="hidden" name="pledge_id" value="{{ $pledge->id }}">

                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" required>

                    <label class="form-label mt-3">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>

                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach($paymentMethods as $m)
                                    <option value="{{ $m->name }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" name="payment_reference" class="form-control">
                        </div>
                    </div>

                    <button class="btn btn-primary mt-3">
                        <i class="icofont icofont-check-circled"></i> Record Fulfillment
                    </button>
                </form>
                @endif

                <hr class="my-3">
                <p class="modal-section-label">Contribution History</p>

                @if($pledge->contributions->count())
                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
                <tbody>
                @foreach($pledge->contributions as $c)
                <tr>
                    <td>{{ optional($c->payment_date)->format('d M Y') }}</td>
                    <td>{{ optional($pledge->campaign)->currency }} {{ number_format($c->amount, 2) }}</td>
                    <td>{{ $c->payment_method ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
                </table>
                </div>
                @else
                <p class="text-muted mb-0">No contributions recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

</div>

@endsection
