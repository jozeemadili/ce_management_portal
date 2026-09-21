@extends('layouts.admin.master')

@section('title', 'Programs Dashboard')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; font-size: .82rem; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #4d7de0; margin-top: 6px; flex-shrink: 0; }
    .today-program-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f2f7; }
    .today-program-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Programs Dashboard</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-primary" href="{{ route('programs.index') }}">
                <i class="icofont icofont-listing-box"></i> All Programs
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item active">Dashboard</li>
@endcomponent

<div class="container-fluid">

<div class="row mb-3">
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value">{{ $stats['today_programs'] }}</p><p class="prog-stat-label">Today's Programs</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value">{{ $stats['live_attendance'] }}</p><p class="prog-stat-label">Present Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-badge"></i></div>
                <div><p class="prog-stat-value">{{ $stats['new_souls_today'] }}</p><p class="prog-stat-label">New Souls Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-4"><i class="icofont icofont-ticket"></i></div>
                <div><p class="prog-stat-value">{{ $stats['registrations_today'] }}</p><p class="prog-stat-label">Registrations Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-5"><i class="icofont icofont-refresh"></i></div>
                <div><p class="prog-stat-value">{{ $stats['active_programs'] }}</p><p class="prog-stat-label">Active Programs</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-listing-box"></i></div>
                <div><p class="prog-stat-value">{{ $stats['total_programs'] }}</p><p class="prog-stat-label">Total Programs</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="card prog-card">
            <div class="card-body">
                <p class="modal-section-label">7-Day Attendance Trend</p>
                <canvas id="trendChart" height="110"></canvas>
            </div>
        </div>

        <div class="card prog-card mt-3">
            <div class="card-body">
                <p class="modal-section-label">Happening Today</p>
                @forelse($todayPrograms as $p)
                <div class="today-program-row">
                    <div>
                        <a href="{{ route('programs.show', $p->id) }}" class="fw-semibold text-dark">{{ $p->name }}</a>
                        <div class="text-muted" style="font-size:.78rem">
                            <i class="icofont icofont-location-pin"></i> {{ $p->location ?? '—' }}
                            @if($p->start_time) &middot; {{ \Illuminate\Support\Carbon::parse($p->start_time)->format('H:i') }} @endif
                        </div>
                    </div>
                    <span class="badge-pill badge-class-{{ $p->classification }}">{{ ucfirst($p->classification) }}</span>
                </div>
                @empty
                <p class="text-muted mb-0">Nothing scheduled today.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-3">
        <div class="card prog-card mb-3">
            <div class="card-body">
                <p class="modal-section-label">Upcoming Programs</p>
                @forelse($upcomingPrograms as $p)
                <div class="today-program-row">
                    <div>
                        <a href="{{ route('programs.show', $p->id) }}" class="fw-semibold text-dark">{{ $p->name }}</a>
                        <div class="text-muted" style="font-size:.78rem">{{ optional($p->start_date)->format('d M Y') }}</div>
                    </div>
                    <span class="badge-pill badge-access-{{ $p->access_type }}">{{ $p->access_type === 'free' ? 'FREE' : 'PAID' }}</span>
                </div>
                @empty
                <p class="text-muted mb-0">No upcoming special programs.</p>
                @endforelse
            </div>
        </div>

        <div class="card prog-card">
            <div class="card-body">
                <p class="modal-section-label">Recent Activity</p>
                @forelse($recentActivity as $a)
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                        <span>{{ str_replace('_',' ',str_replace('.',' ',$a->action)) }}</span>
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
const trendLabels = @json($trend->keys());
const trendData = @json($trend->values());

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: trendLabels, datasets: [{ label: 'Present', data: trendData, borderColor: '#2e5aac', backgroundColor: 'rgba(46,90,172,0.1)', tension: 0.35, fill: true }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endpush
