@extends('layouts.admin.master')

@section('title', 'Pledges Settings')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Pledges Settings</h3>
    @endslot
    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Settings</li>
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
    <div class="col-lg-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Payment Methods</p>
                <p class="text-muted">Options shown on the Record Contribution form. Add new ones here or deactivate ones you no longer use &mdash; nothing is hardcoded.</p>

                <form method="POST" action="{{ route('pledge-settings.payment-methods.store') }}" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. USSD, Cheque, POS...">
                    <button class="btn btn-primary btn-sm text-nowrap"><i class="icofont icofont-plus-circle"></i> Add</button>
                </form>

                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead><tr><th>Name</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @forelse($paymentMethods as $m)
                <tr>
                    <td>{{ $m->name }}</td>
                    <td>
                        <span class="badge-pill {{ $m->is_active ? 'badge-status-active' : 'badge-status-closed' }}">
                            {{ $m->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('pledge-settings.payment-methods.toggle', $m->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-light">
                                {{ $m->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted">No payment methods yet.</td></tr>
                @endforelse
                </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Per-Campaign Settings</p>
                <p class="text-muted">Most Pledges settings live on each individual campaign, not here:</p>
                <ul class="text-muted">
                    <li>Allow anonymous pledges, enable live presentation &mdash; on the campaign's <strong>Edit</strong> form.</li>
                    <li>What appears on the presentation screen (amount, target, pledger count, latest pledges, graph, name masking) &mdash; on the <a href="{{ route('pledge-live.select') }}">Live Presentation</a> screen's controls (gear icon on each campaign).</li>
                </ul>
                <a href="{{ route('pledge-campaigns.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="icofont icofont-bullseye"></i> Go to Campaigns
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Access Permissions</p>
                <p class="text-muted">These permission codes gate Pledges-module actions. Assign them to a designation via the existing role/permission tables to grant non-ADMIN staff access to specific actions.</p>
                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead><tr><th>Code</th><th>Name</th></tr></thead>
                <tbody>
                @foreach($permissions as $p)
                <tr>
                    <td><code>{{ $p->code }}</code></td>
                    <td>{{ $p->name }}</td>
                </tr>
                @endforeach
                </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

@endsection
