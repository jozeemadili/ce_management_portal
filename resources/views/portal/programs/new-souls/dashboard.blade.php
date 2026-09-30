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
            <a class="btn btn-outline-primary" href="{{ $churchView ? route('invitees.index') : route('new-souls.index') }}">
                <i class="icofont icofont-listing-box"></i> {{ $churchView ? 'New Invitees List' : 'New Souls List' }}
            </a>
        </li>
    @endslot
    @if($churchView)
        <li class="breadcrumb-item">Church Setup</li>
        <li class="breadcrumb-item active">New Souls Dashboard</li>
    @else
        <li class="breadcrumb-item"><a href="{{ route('new-souls.index') }}">New Souls</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
    @endif
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
    <div class="col-md-5">
        <label class="form-label">Church</label>
        <select name="church" class="form-select" onchange="this.form.submit()">
            <option value="">All my churches</option>
            @foreach($churches as $c)
                <option value="{{ $c->id }}" @selected($selectedChurch && $selectedChurch->id === $c->id)>{{ strtoupper($c->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-7 text-muted" style="font-size:.85rem;">
        {{ $selectedChurch ? 'New souls assigned to ' . strtoupper($selectedChurch->name) . '.' : 'New souls across all your churches.' }}
        Follow them up below until they become members.
    </div>
</form>

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

<div class="card prog-card mt-1">
<div class="card-body">
    <p class="modal-section-label">Follow-up worklist ({{ $worklist->count() }})</p>
    <p class="text-muted" style="font-size:.85rem;">New souls still being followed up, longest waiting first. Update their status as you go; choose <strong>Became Member</strong> when they join (they get a login).</p>
    @if($worklist->isEmpty())
        <div class="prog-empty py-3"><i class="icofont icofont-check-circled"></i>Everyone is followed up. 🎉</div>
    @else
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th>New soul</th><th>First visit</th><th>Follow-up status</th><th>Church (assign)</th></tr></thead>
        <tbody>
        @foreach($worklist as $v)
            <tr>
                <td>
                    <strong>{{ trim($v->first_name . ' ' . $v->last_name) }}</strong>
                    <div class="text-muted" style="font-size:.75rem;">{{ $v->phone ?: '—' }}@if($v->location) &middot; {{ $v->location }}@endif</div>
                </td>
                <td>{{ optional($v->first_visit_date)->format('d M Y') }}<div class="text-muted" style="font-size:.75rem;">{{ optional($v->first_visit_date)->diffForHumans() }} &middot; {{ optional($v->firstVisitProgram)->name }}</div></td>
                <td>
                    <form method="POST" action="{{ route('new-souls.status', $v->id) }}" class="d-flex gap-1 flex-wrap"
                          onsubmit="return this.status.value !== 'became_member' || confirm('Make {{ addslashes($v->first_name) }} a member?')">
                        @csrf
                        <select name="status" class="form-select form-select-sm" style="max-width:190px;">
                            @foreach(['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed'] as $val => $label)
                                <option value="{{ $val }}" @selected($v->follow_up_status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input name="notes" class="form-control form-control-sm" style="max-width:170px;" placeholder="Note (optional)">
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
                <td>@include('portal.churches.invitees.partials.assign', ['invitee' => $v, 'occurrence' => null])</td>
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
