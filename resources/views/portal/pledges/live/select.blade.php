@extends('layouts.admin.master')

@section('title', 'Live Presentation')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Live Presentation</h3>
    @endslot
    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Live Presentation</li>
@endcomponent

<div class="container-fluid">

@if($campaigns->isEmpty())
<div class="card pledge-card">
    <div class="card-body">
        <div class="pledge-empty">
            <i class="icofont icofont-presentation"></i>
            <p class="mb-0">No campaigns have Live Presentation enabled right now. Enable it from the campaign's Edit form.</p>
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
                <i class="icofont icofont-presentation"></i>
            @endunless
        </div>
        <div class="campaign-card-body">
            <h5 class="campaign-card-title">{{ $campaign->name }}</h5>
            <div class="pledge-progress"><div class="pledge-progress-bar" style="width: {{ $pct }}%"></div></div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">{{ $campaign->currency }} {{ number_format($campaign->totalPledged()) }}</small>
                <small class="text-muted">{{ $pct }}%</small>
            </div>

            <div class="d-flex gap-2 mt-2">
                <a href="{{ route('pledge-live.present', $campaign->id) }}" target="_blank" class="btn btn-primary flex-fill">
                    <i class="icofont icofont-external-link"></i> Present
                </a>
                <button class="btn btn-outline-secondary settings-btn"
                    data-bs-toggle="modal" data-bs-target="#liveSettingsModal"
                    data-id="{{ $campaign->id }}"
                    data-name="{{ $campaign->name }}"
                    data-amount="{{ $campaign->live_show_amount ? 1 : 0 }}"
                    data-pledgers="{{ $campaign->live_show_pledgers ? 1 : 0 }}"
                    data-latest="{{ $campaign->live_show_latest ? 1 : 0 }}"
                    data-graph="{{ $campaign->live_show_graph ? 1 : 0 }}"
                    data-target="{{ $campaign->live_show_target ? 1 : 0 }}"
                    data-mask="{{ $campaign->live_mask_names ? 1 : 0 }}"
                    title="Presentation Controls">
                    <i class="icofont icofont-gear"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
@endif

</div>

{{-- ================= LIVE SETTINGS MODAL ================= --}}
<div class="modal fade" id="liveSettingsModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" id="liveSettingsForm" action="">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-gear"></i> Presentation Controls</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p class="mb-3">Campaign: <strong id="settings_campaign_name"></strong></p>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_amount" value="1" id="s_amount">
        <label class="form-check-label" for="s_amount">Show pledge amount</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_target" value="1" id="s_target">
        <label class="form-check-label" for="s_target">Show target</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_pledgers" value="1" id="s_pledgers">
        <label class="form-check-label" for="s_pledgers">Show pledger count</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_latest" value="1" id="s_latest">
        <label class="form-check-label" for="s_latest">Show latest pledges</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_show_graph" value="1" id="s_graph">
        <label class="form-check-label" for="s_graph">Show progress graph</label>
    </div>
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="live_mask_names" value="1" id="s_mask">
        <label class="form-check-label" for="s_mask">Mask pledger names (e.g. "Member from christ embassy mbezi")</label>
    </div>
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary">Save Controls</button>
</div>

</form>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.settings-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('liveSettingsForm').action = `/v1/pledges/live/${this.dataset.id}/settings`;
        document.getElementById('settings_campaign_name').textContent = this.dataset.name;
        document.getElementById('s_amount').checked = this.dataset.amount === '1';
        document.getElementById('s_pledgers').checked = this.dataset.pledgers === '1';
        document.getElementById('s_latest').checked = this.dataset.latest === '1';
        document.getElementById('s_graph').checked = this.dataset.graph === '1';
        document.getElementById('s_target').checked = this.dataset.target === '1';
        document.getElementById('s_mask').checked = this.dataset.mask === '1';
    });
});
</script>
@endpush
