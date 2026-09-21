@extends('layouts.admin.master')

@section('title', 'Active Pledge Campaigns')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Active Campaigns</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('my-pledges.index') }}">
                <i class="icofont icofont-listing-box"></i> My Pledges
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Active Campaigns</li>
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

@if($campaigns->isEmpty())
<div class="card pledge-card">
    <div class="card-body">
        <div class="pledge-empty">
            <i class="icofont icofont-bullseye"></i>
            <p class="mb-0">There are no active campaigns available to you right now.</p>
        </div>
    </div>
</div>
@else
<div class="row">
@foreach($campaigns as $campaign)
@php($pct = $campaign->progressPercent())
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card campaign-card">
        <div class="campaign-card-banner" @if($campaign->banner_path) style="background-image:url('{{ asset('storage/' . $campaign->banner_path) }}');background-size:cover;background-position:center;" @endif>
            @unless($campaign->banner_path)
                <i class="icofont icofont-bullseye"></i>
            @endunless
        </div>
        <div class="campaign-card-body">
            <h5 class="campaign-card-title">{{ $campaign->name }}</h5>
            <p class="campaign-card-desc">{{ \Illuminate\Support\Str::limit($campaign->description ?? '', 90) ?: 'No description provided.' }}</p>

            <div class="pledge-progress"><div class="pledge-progress-bar" style="width: {{ $pct }}%"></div></div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">{{ $campaign->currency }} {{ number_format($campaign->totalPledged()) }} raised</small>
                <small class="text-muted">{{ $pct }}%</small>
            </div>

            <div class="campaign-card-meta">
                <span><i class="icofont icofont-bullseye"></i> {{ $campaign->currency }} {{ number_format($campaign->target_amount) }}</span>
                <span><i class="icofont icofont-people"></i> {{ $campaign->pledgersCount() }} pledgers</span>
            </div>

            @if($campaign->start_date || $campaign->end_date)
            <div class="campaign-card-meta">
                <span><i class="icofont icofont-calendar"></i>
                    {{ optional($campaign->start_date)->format('d M Y') }}
                    @if($campaign->end_date) &mdash; {{ $campaign->end_date->format('d M Y') }} @endif
                </span>
            </div>
            @endif

            <button class="btn btn-primary mt-2 make-pledge-btn"
                data-bs-toggle="modal" data-bs-target="#makePledgeModal"
                data-campaign-id="{{ $campaign->id }}"
                data-campaign-name="{{ $campaign->name }}"
                data-currency="{{ $campaign->currency }}">
                <i class="icofont icofont-gift"></i> Make a Pledge
            </button>
        </div>
    </div>
</div>
@endforeach
</div>
@endif

</div>

{{-- ================= MAKE A PLEDGE MODAL ================= --}}
<div class="modal fade" id="makePledgeModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="{{ route('my-pledges.store') }}">
@csrf
<input type="hidden" name="campaign_id" id="pledge_campaign_id">

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-gift"></i> Make a Pledge</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p class="mb-3">Campaign: <strong id="pledge_campaign_name"></strong></p>

    <label class="form-label">Pledge Amount (<span id="pledge_currency">TZS</span>)</label>
    <input type="number" step="0.01" min="1" name="amount" class="form-control" required>

    <label class="form-label mt-3">Fulfillment Frequency</label>
    <select name="frequency" class="form-control" required>
        <option value="one_time">One Time</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="custom">Custom</option>
    </select>

    <label class="form-label mt-3">Notes (optional)</label>
    <textarea name="notes" class="form-control" rows="2"></textarea>

    {{-- Pledges are always displayed anonymously on the live/public screen -
         this isn't a member choice, so no control is shown here. --}}
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary">Submit Pledge</button>
</div>

</form>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.make-pledge-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('pledge_campaign_id').value = this.dataset.campaignId;
        document.getElementById('pledge_campaign_name').textContent = this.dataset.campaignName;
        document.getElementById('pledge_currency').textContent = this.dataset.currency;
    });
});
</script>
@endpush
