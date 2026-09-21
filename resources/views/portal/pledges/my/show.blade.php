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

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('my-pledges.pdf', $pledge->id) }}">
                <i class="icofont icofont-download-alt"></i> Download PDF
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item"><a href="{{ route('my-pledges.index') }}">My Pledges</a></li>
    <li class="breadcrumb-item active">{{ $pledge->pledge_reference }}</li>
@endcomponent

<div class="container-fluid">

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Pledge Summary</p>
                <p class="mb-1"><strong>{{ optional($pledge->campaign)->name }}</strong></p>
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
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Frequency</span>
                    <strong>{{ ucfirst(str_replace('_',' ',$pledge->frequency)) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Pledged On</span>
                    <strong>{{ optional($pledge->pledged_at)->format('d M Y') }}</strong>
                </div>

                @if($pledge->source === 'staff')
                <div class="alert alert-info mt-3 mb-0 py-2 px-3">
                    <i class="icofont icofont-info-circle"></i> Recorded by Church Staff
                </div>
                @endif

                @if($pledge->notes)
                <p class="mt-3 mb-0 text-muted"><small>{{ $pledge->notes }}</small></p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Contribution History</p>

                @if($pledge->contributions->count())
                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                </tr>
                </thead>
                <tbody>
                @foreach($pledge->contributions as $c)
                <tr>
                    <td>{{ optional($c->payment_date)->format('d M Y') }}</td>
                    <td>{{ optional($pledge->campaign)->currency }} {{ number_format($c->amount, 2) }}</td>
                    <td>{{ $c->payment_method ?? '—' }}</td>
                    <td>{{ $c->payment_reference ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
                </table>
                </div>
                @else
                <div class="pledge-empty">
                    <i class="icofont icofont-money"></i>
                    <p class="mb-0">No contributions recorded against this pledge yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

</div>

@endsection
