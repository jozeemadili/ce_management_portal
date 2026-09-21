@extends('layouts.admin.master')

@section('title', 'New Souls Dashboard')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .funnel-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; }
    .funnel-row:last-child { border-bottom: none; }
    .funnel-label { width: 160px; font-size: .82rem; color: #4b5563; font-weight: 600; }
    .funnel-bar-wrap { flex: 1; }
    .funnel-count { width: 40px; text-align: right; font-weight: 700; font-size: .9rem; }
    .mini-list-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f2f7; font-size: .84rem; }
    .mini-list-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>New Souls Dashboard</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('new-souls.index') }}">
                <i class="icofont icofont-listing-box"></i> New Souls List
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item"><a href="{{ route('new-souls.index') }}">New Souls</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endcomponent

<div class="container-fluid">

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-sun"></i></div>
                <div><p class="prog-stat-value">{{ $counts['today'] }}</p><p class="prog-stat-label">Today</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value">{{ $counts['week'] }}</p><p class="prog-stat-label">This Week</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value">{{ $counts['month'] }}</p><p class="prog-stat-label">This Month</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-people"></i></div>
                <div><p class="prog-stat-value">{{ $counts['total'] }}</p><p class="prog-stat-label">All Time</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Follow-Up Funnel</p>
                @php $maxFunnel = max(array_merge(array_values($funnel), [1])); @endphp
                @foreach(['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed'] as $key => $label)
                <div class="funnel-row">
                    <div class="funnel-label">{{ $label }}</div>
                    <div class="funnel-bar-wrap">
                        <div class="prog-progress"><div class="prog-progress-bar" style="width: {{ $funnel[$key] > 0 ? max(4, round($funnel[$key]/$maxFunnel*100)) : 0 }}%"></div></div>
                    </div>
                    <div class="funnel-count">{{ $funnel[$key] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-3 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">By Program</p>
                @forelse($byProgram as $row)
                <div class="mini-list-row">
                    <span>{{ optional($row->firstVisitProgram)->name ?? 'Unknown' }}</span>
                    <strong>{{ $row->total }}</strong>
                </div>
                @empty
                <p class="text-muted mb-0">No data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-3 mb-3">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">By Church</p>
                @forelse($byChurch as $row)
                <div class="mini-list-row">
                    <span>{{ optional($row->church)->name ?? 'Unknown' }}</span>
                    <strong>{{ $row->total }}</strong>
                </div>
                @empty
                <p class="text-muted mb-0">No data yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

</div>

@endsection
