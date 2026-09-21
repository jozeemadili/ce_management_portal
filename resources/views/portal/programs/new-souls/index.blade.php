@extends('layouts.admin.master')

@section('title', 'New Souls')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>New Souls</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('new-souls.dashboard') }}">
                <i class="icofont icofont-chart-bar-graph"></i> Dashboard
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item active">New Souls</li>
@endcomponent

<div class="container-fluid">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-people"></i></div>
                <div><p class="prog-stat-value">{{ $stats['total'] }}</p><p class="prog-stat-label">Total New Souls</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-4"><i class="icofont icofont-badge"></i></div>
                <div><p class="prog-stat-value">{{ $stats['new'] }}</p><p class="prog-stat-label">New</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-phone"></i></div>
                <div><p class="prog-stat-value">{{ $stats['in_progress'] }}</p><p class="prog-stat-label">In Follow-Up</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value">{{ $stats['connected'] }}</p><p class="prog-stat-label">Connected / Became Member</p></div>
            </div>
        </div>
    </div>
</div>

<div class="card prog-card">
    <div class="card-body">
        <form method="GET" class="prog-filter-bar row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or phone...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All Statuses --</option>
                    @foreach(['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="icofont icofont-search"></i> Filter</button>
            </div>
        </form>

        @if($visitors->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Name</th><th>Phone</th><th>Church</th><th>First Visit</th><th>Program</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($visitors as $v)
        <tr>
            <td class="fw-semibold">{{ $v->first_name }} {{ $v->last_name }}</td>
            <td>{{ $v->phone ?? '—' }}</td>
            <td>{{ optional($v->church)->name ?? '—' }}</td>
            <td>{{ optional($v->first_visit_date)->format('d M Y') ?? '—' }}</td>
            <td>{{ optional($v->firstVisitProgram)->name ?? '—' }}</td>
            <td><span class="badge-pill badge-status-{{ $v->follow_up_status }}">{{ ucfirst(str_replace('_',' ',$v->follow_up_status)) }}</span></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal{{ $v->id }}">
                    <i class="icofont icofont-edit"></i> Update Status
                </button>
            </td>
        </tr>

        <div class="modal fade" id="statusModal{{ $v->id }}">
        <div class="modal-dialog">
        <div class="modal-content">
        <form method="POST" action="{{ route('new-souls.status', $v->id) }}">
        @csrf
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ $v->first_name }} {{ $v->last_name }} — Follow-Up Status</h5>
            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                @foreach(['new'=>'New','contacted'=>'Contacted','follow_up'=>'Follow-Up In Progress','foundation_classes'=>'Foundation Classes','connected_to_cell'=>'Connected to Cell','became_member'=>'Became Member','closed'=>'Closed'] as $val => $label)
                    <option value="{{ $val }}" @selected($v->follow_up_status===$val)>{{ $label }}</option>
                @endforeach
            </select>
            <label class="form-label mt-3">Add a Note (optional)</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
            @if($v->notes)
            <p class="text-muted mt-2 mb-0"><small><strong>Previous notes:</strong> {{ $v->notes }}</small></p>
            @endif
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary">Save Status</button>
        </div>
        </form>
        </div>
        </div>
        </div>
        @endforeach
        </tbody>
        </table>
        </div>
        {{ $visitors->links() }}
        @else
        <div class="prog-empty"><i class="icofont icofont-people"></i><p class="mb-0">No new souls recorded yet.</p></div>
        @endif
    </div>
</div>

</div>

@endsection
