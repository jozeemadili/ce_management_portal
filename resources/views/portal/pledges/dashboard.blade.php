@extends('layouts.admin.master')

@section('title', 'Pledges Dashboard')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Pledges Dashboard</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-primary" href="{{ route('pledge-campaigns.index') }}">
                <i class="icofont icofont-bullseye"></i> Campaigns
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Dashboard</li>
@endcomponent

<div class="container-fluid">

<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-2"><i class="icofont icofont-flag"></i></div>
                <div><p class="pledge-stat-value">{{ $stats['active_campaigns'] }}</p><p class="pledge-stat-label">Active Campaigns</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-1"><i class="icofont icofont-coins"></i></div>
                <div><p class="pledge-stat-value">{{ number_format($stats['total_pledged']) }}</p><p class="pledge-stat-label">Total Pledged</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-6"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="pledge-stat-value">{{ number_format($stats['total_fulfilled']) }}</p><p class="pledge-stat-label">Total Fulfilled</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-5"><i class="icofont icofont-warning"></i></div>
                <div><p class="pledge-stat-value">{{ number_format($stats['total_outstanding']) }}</p><p class="pledge-stat-label">Total Outstanding</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-3"><i class="icofont icofont-people"></i></div>
                <div><p class="pledge-stat-value">{{ $stats['total_pledgers'] }}</p><p class="pledge-stat-label">Total Pledgers</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-4"><i class="icofont icofont-clock-time"></i></div>
                <div><p class="pledge-stat-value">{{ $stats['today_pledges'] }}</p><p class="pledge-stat-label">Today's Pledges</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-7"><i class="icofont icofont-money-bag"></i></div>
                <div><p class="pledge-stat-value">{{ number_format($stats['today_amount']) }}</p><p class="pledge-stat-label">Today's Pledged Amount</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Pledge Growth (last 30 days)</p>
                <canvas id="growthChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Fulfilled vs Outstanding</p>
                <canvas id="fulfillmentChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Pledgers Over Time</p>
                <canvas id="pledgersChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card pledge-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-2">Campaign Performance</p>
                <canvas id="performanceChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card pledge-card">
            <div class="card-body">
                <p class="modal-section-label mb-2">Recent Pledges</p>
                @if($recentPledges->count())
                <div class="table-responsive">
                <table class="table pledge-table align-middle">
                <thead>
                <tr><th>Reference</th><th>Campaign</th><th>Pledger</th><th class="text-end">Amount</th><th>Status</th><th>When</th></tr>
                </thead>
                <tbody>
                @foreach($recentPledges as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->pledge_reference }}</td>
                    <td>{{ optional($p->campaign)->name }}</td>
                    <td>{{ $p->displayName() }}</td>
                    <td class="text-end">{{ optional($p->campaign)->currency }} {{ number_format($p->amount, 2) }}</td>
                    <td><span class="badge-pill badge-status-{{ $p->status }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span></td>
                    <td>{{ $p->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
                </tbody>
                </table>
                </div>
                @else
                <div class="pledge-empty">
                    <i class="icofont icofont-listing-box"></i>
                    <p class="mb-0">No pledges recorded yet.</p>
                </div>
                @endif
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
const pledgersLabels = @json($pledgersOverTime->keys());
const pledgersData = @json($pledgersOverTime->values());
const perfLabels = @json($campaignPerformance->pluck('name'));
const perfData = @json($campaignPerformance->pluck('percent'));

new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: { labels: growthLabels, datasets: [{ label: 'Pledged', data: growthData, borderColor: '#2e5aac', backgroundColor: 'rgba(46,90,172,0.1)', tension: 0.35, fill: true }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('fulfillmentChart'), {
    type: 'doughnut',
    data: {
        labels: ['Fulfilled', 'Outstanding'],
        datasets: [{ data: [{{ $totalFulfilled }}, {{ max(0, $totalPledged - $totalFulfilled) }}], backgroundColor: ['#1fa971', '#e04b4b'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('pledgersChart'), {
    type: 'bar',
    data: { labels: pledgersLabels, datasets: [{ label: 'Pledgers', data: pledgersData, backgroundColor: '#a855f7' }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('performanceChart'), {
    type: 'bar',
    data: { labels: perfLabels, datasets: [{ label: 'Completion %', data: perfData, backgroundColor: '#4d7de0' }] },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 100 } } }
});
</script>
@endpush
