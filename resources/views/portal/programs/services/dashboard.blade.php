@extends('layouts.admin.master')

@section('title', 'Services Dashboard')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .testimony { border-bottom: 1px solid #f0f2f7; padding: 10px 0; }
    .testimony:last-child { border-bottom: 0; }
    .testimony-text { white-space: pre-wrap; font-size: .88rem; margin: 4px 0; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Services Dashboard</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li><a class="btn btn-primary" href="{{ route('services.checkin') }}"><i class="icofont icofont-qr-code"></i> Service Check-in</a></li>
    @endslot
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Services Dashboard</li>
@endcomponent

<div class="container-fluid">

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">{{ $error }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endforeach
@endif
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="GET" class="prog-filter-bar row g-2 align-items-end">
    <div class="col-md-3">
        <label class="form-label">Church</label>
        <select name="church" class="form-select">
            <option value="">All my churches</option>
            @foreach($churches as $c)
                <option value="{{ $c->id }}" @selected((int) request('church') === $c->id)>{{ strtoupper($c->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Service</label>
        <select name="service" class="form-select">
            <option value="">All services</option>
            @foreach($services as $s)
                <option value="{{ $s->id }}" @selected((int) request('service') === $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100"><i class="icofont icofont-filter"></i> Show</button>
    </div>
</form>

<div class="row">
    @foreach([
        ['Services held', $totals['services'], 'bg-1', 'icofont-building-alt'],
        ['Attended', $totals['attended'], 'bg-2', 'icofont-users-alt-4'],
        ['Avg. per service', $totals['average'] ?? '—', 'bg-6', 'icofont-chart-line'],
        ['Late', $totals['late'], 'bg-4', 'icofont-clock-time'],
        ['Absent', $totals['absent'], 'bg-5', 'icofont-close-circled'],
        ['New souls', $totals['new_souls'], 'bg-3', 'icofont-heart-alt'],
    ] as [$label, $value, $bg, $icon])
        <div class="col-6 col-xl-2 mb-3">
            <div class="card prog-stat-card"><div class="stat-body">
                <div class="prog-stat-icon {{ $bg }}"><i class="icofont {{ $icon }}"></i></div>
                <div><p class="prog-stat-value">{{ is_numeric($value) ? number_format($value) : $value }}</p><p class="prog-stat-label">{{ $label }}</p></div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card prog-card h-100"><div class="card-body">
            <p class="modal-section-label">Attendance &amp; new souls per service day</p>
            @if($trend->isEmpty())
                <div class="prog-empty py-4"><i class="icofont icofont-chart-line"></i>No services in this period yet.</div>
            @else
                <canvas id="trendChart" height="120"></canvas>
            @endif
        </div></div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card prog-card h-100"><div class="card-body">
            <p class="modal-section-label">Latest testimonies</p>
            @forelse($testimonies as $t)
                <div class="testimony">
                    <strong>{{ trim($t->member->first_name . ' ' . $t->member->last_name) }}</strong>
                    <small class="text-muted">&middot; {{ optional($t->member->church)->name }} &middot; {{ $t->submitted_at->diffForHumans() }}</small>
                    <div class="testimony-text">{{ \Illuminate\Support\Str::limit($t->feedback, 300) }}</div>
                </div>
            @empty
                <p class="text-muted mb-0" style="font-size:.85rem;">No testimonies yet. After a service, send the follow-up SMS to its new souls from the table below.</p>
            @endforelse
        </div></div>
    </div>
</div>

<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Services</p>
    @if($occurrences->isEmpty())
        <div class="prog-empty py-4"><i class="icofont icofont-building-alt"></i>No services in this period.</div>
    @else
    <div class="table-responsive">
    <table class="table prog-table">
        <thead>
            <tr>
                <th>Date</th><th>Service</th><th>Church</th><th>Attended</th><th>Late</th><th>Absent</th><th>New souls</th><th>Report</th><th>Follow-up</th>
            </tr>
        </thead>
        <tbody>
        @foreach($occurrences as $o)
            <tr>
                <td>{{ $o->occurrence_date->format('D d M Y') }}
                    @unless($o->isClosed())<span class="badge-pill badge-status-active ms-1">Open</span>@endunless
                </td>
                <td>{{ $o->program->name }}</td>
                <td>{{ strtoupper(optional($o->church)->name) }}</td>
                <td><strong>{{ $o->stats['attended'] }}</strong></td>
                <td>{{ $o->stats['late'] }}</td>
                <td>{{ $o->isClosed() ? $o->stats['absent'] : '—' }}</td>
                <td>{{ $o->stats['new_souls'] }}</td>
                <td>
                    <a class="btn btn-sm {{ $o->isReportClosed() ? 'btn-light' : 'btn-outline-primary' }}" href="{{ route('services.report', $o->id) }}">
                        {{ $o->isReportClosed() ? 'Closed' : 'Open report' }}
                    </a>
                </td>
                <td>
                    @if($o->followup)
                        <small class="d-block">{{ $o->followup->sent }} SMS sent &middot; {{ $o->followup->replies }} replied</small>
                    @endif
                    @if($o->stats['new_souls'] > 0)
                        <form method="POST" action="{{ route('services.followup', $o->id) }}"
                              onsubmit="return confirm('Send the follow-up SMS (with the testimony link) to this service\'s new souls who have not received it yet?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-success"><i class="icofont icofont-ui-message"></i> Send follow-up SMS</button>
                        </form>
                    @elseif(!$o->followup)
                        <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
</div>

</div>
@endsection

@push('scripts')
@if($trend->isNotEmpty())
<script src="{{ asset('assets/js/chart/chartjs/chart.min.js') }}"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: @json($trend->pluck('label')),
        datasets: [
            { label: 'Attended', data: @json($trend->pluck('attended')), backgroundColor: 'rgba(46,90,172,.75)', borderRadius: 6 },
            { label: 'New souls', data: @json($trend->pluck('new_souls')), backgroundColor: 'rgba(168,85,247,.75)', borderRadius: 6 }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endif
@endpush
