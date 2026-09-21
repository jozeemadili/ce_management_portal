@extends('layouts.admin.master')

@section('title', $campaign->name)

@push('css')
@include('portal.pledges.partials.styles')
<style>
    .big-progress-wrap { text-align: center; padding: 20px 0; }
    .big-progress-pct { font-size: 3rem; font-weight: 800; color: #2e5aac; line-height: 1; }
    .big-progress-bar { height: 22px; border-radius: 20px; background: #eef1f6; overflow: hidden; margin: 16px 0; }
    .big-progress-bar-fill { height: 100%; border-radius: 20px; background: linear-gradient(90deg,#2e5aac,#4d7de0); transition: width .5s ease; }
    .activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; font-size: .82rem; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #4d7de0; margin-top: 6px; flex-shrink: 0; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>{{ $campaign->name }}</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        @if(Route::has('pledge-live.select'))
        <li>
            <a class="btn btn-outline-primary" href="{{ route('pledge-live.select') }}">
                <i class="icofont icofont-presentation"></i> Live Presentation
            </a>
        </li>
        @endif
        <li>
            <a class="btn btn-primary" href="{{ route('pledge-campaigns.index') }}">
                <i class="icofont icofont-listing-box"></i> All Campaigns
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item"><a href="{{ route('pledge-campaigns.index') }}">Pledges</a></li>
    <li class="breadcrumb-item active">{{ $campaign->name }}</li>
@endcomponent

<div class="container-fluid">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php($pct = $campaign->progressPercent())
@php($pledged = $campaign->totalPledged())
@php($fulfilled = $campaign->totalFulfilled())

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-4"><i class="icofont icofont-bullseye"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $campaign->currency }} {{ number_format($campaign->target_amount) }}</p>
                    <p class="pledge-stat-label">Target</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-1"><i class="icofont icofont-coins"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $campaign->currency }} {{ number_format($pledged) }}</p>
                    <p class="pledge-stat-label">Total Pledged</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $campaign->currency }} {{ number_format($fulfilled) }}</p>
                    <p class="pledge-stat-label">Total Fulfilled</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-5"><i class="icofont icofont-warning"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $campaign->currency }} {{ number_format(max(0, $pledged - $fulfilled)) }}</p>
                    <p class="pledge-stat-label">Outstanding</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body big-progress-wrap">
                <div class="big-progress-pct">{{ $pct }}%</div>
                <div class="big-progress-bar"><div class="big-progress-bar-fill" style="width: {{ $pct }}%"></div></div>
                <p class="mb-0 text-muted">{{ $campaign->pledgersCount() }} Pledgers</p>
                <span class="badge-pill badge-status-{{ $campaign->status }} mt-2 d-inline-block">{{ ucfirst($campaign->status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Pledge Growth</p>
                <canvas id="growthChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Latest Pledges</p>
                @forelse($latestPledges as $p)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span class="fw-semibold">{{ $p->displayName() }}</span>
                            <br><small class="text-muted">{{ $p->created_at->diffForHumans() }}</small>
                        </div>
                        <span class="badge-pill badge-status-{{ $p->status }}">{{ $campaign->currency }} {{ number_format($p->amount) }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No pledges yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Top Pledges</p>
                @forelse($topPledges as $p)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span class="fw-semibold">{{ $p->displayName() }}</span>
                            <br><small class="text-muted">{{ $p->pledge_reference }}</small>
                        </div>
                        <span class="badge-pill badge-status-{{ $p->status }}">{{ $campaign->currency }} {{ number_format($p->amount) }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No pledges yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Recent Contributions</p>
                @forelse($recentContributions as $c)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span class="fw-semibold">{{ optional($c->pledge->member)->first_name }} {{ optional($c->pledge->member)->last_name }}</span>
                            <br><small class="text-muted">{{ optional($c->payment_date)->format('d M Y') }}</small>
                        </div>
                        <span class="badge-pill badge-status-fulfilled">{{ $campaign->currency }} {{ number_format($c->amount) }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No contributions recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Recent Activity</p>
                @forelse($recentActivity as $a)
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div>
                            <span>{{ str_replace('_',' ',str_replace('campaign.','',$a->action)) }}</span>
                            by <strong>{{ optional($a->actor)->first_name ?? 'System' }}</strong>
                            <br><small class="text-muted">{{ $a->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No activity recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart/chartjs/chart.min.js') }}"></script>
<script>
const growthLabels = @json($growth->keys());
const growthData = @json($growth->values());

new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: growthLabels,
        datasets: [{
            label: 'Pledged ({{ $campaign->currency }})',
            data: growthData,
            borderColor: '#2e5aac',
            backgroundColor: 'rgba(46,90,172,0.1)',
            tension: 0.35,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endpush
