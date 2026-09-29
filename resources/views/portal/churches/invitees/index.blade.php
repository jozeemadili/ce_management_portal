@extends('layouts.admin.master')

@section('title', 'New Invitees')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .inv-meta { font-size: .78rem; color: #6b7280; }
    .step-pill { display: inline-block; font-size: .7rem; font-weight: 600; border-radius: 20px; padding: 2px 9px; margin: 1px 2px 1px 0; }
    .step-yes { background: #e6f7ee; color: #0f9d58; }
    .step-no { background: #eef0f3; color: #6b7280; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>New Invitees</h3>
    @endslot
    <li class="breadcrumb-item">Church Setup</li>
    <li class="breadcrumb-item active">New Invitees</li>
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

<div class="row">
    @foreach([['New invitees', $stats['total'], 'bg-3', 'icofont-heart-alt'], ['Not assigned yet', $stats['unassigned'], 'bg-4', 'icofont-warning'], ['Did foundation classes', $stats['foundation'], 'bg-1', 'icofont-graduate-alt'], ['Baptized', $stats['baptized'], 'bg-6', 'icofont-water-drop']] as [$label, $value, $bg, $icon])
        <div class="col-6 col-xl-3 mb-3">
            <div class="card prog-stat-card"><div class="stat-body">
                <div class="prog-stat-icon {{ $bg }}"><i class="icofont {{ $icon }}"></i></div>
                <div><p class="prog-stat-value">{{ $value }}</p><p class="prog-stat-label">{{ $label }}</p></div>
            </div></div>
        </div>
    @endforeach
</div>

<form method="GET" class="prog-filter-bar row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">Search</label><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, phone or area"></div>
    <div class="col-md-3">
        <label class="form-label">Church</label>
        <select name="church" class="form-select">
            <option value="">All my churches</option>
            @foreach($churches as $c)<option value="{{ $c->id }}" @selected((int) request('church') === $c->id)>{{ strtoupper($c->name) }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Follow-up</label>
        <select name="status" class="form-select">
            <option value="">Any</option>
            @foreach(\App\Http\Controllers\API\Church\InviteeController::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Assigned</label>
        <select name="assigned" class="form-select">
            <option value="">Any</option>
            <option value="no" @selected(request('assigned') === 'no')>Not assigned yet</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100"><i class="icofont icofont-filter"></i> Filter</button></div>
</form>

<div class="card prog-card">
<div class="card-body">
@if($invitees->isEmpty())
    <div class="prog-empty"><i class="icofont icofont-heart-alt"></i>No new invitees here yet. They appear when first-time visitors are recorded at a service or program.</div>
@else
<div class="table-responsive">
<table class="table prog-table">
    <thead><tr><th>Invitee</th><th>First visit</th><th>Follow-up</th><th>Church (assign)</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    @foreach($invitees as $invitee)
        <tr>
            <td>
                <strong>{{ trim($invitee->first_name . ' ' . $invitee->last_name) }}</strong>
                <div class="inv-meta">
                    <i class="icofont icofont-phone"></i> {{ $invitee->phone ?: '—' }}
                    @if($invitee->email)<br><i class="icofont icofont-email"></i> {{ $invitee->email }}@endif
                    <br><i class="icofont icofont-location-pin"></i> {{ $invitee->location ?: 'Area not given' }}
                </div>
            </td>
            <td>
                {{ optional($invitee->first_visit_date)->format('d M Y') ?? '—' }}
                <div class="inv-meta">{{ optional($invitee->firstVisitProgram)->name }}</div>
                @if($invitee->invitedByMember || $invitee->invited_by)
                    <div class="inv-meta">Invited by {{ $invitee->invitedByMember ? trim($invitee->invitedByMember->first_name . ' ' . $invitee->invitedByMember->last_name) : $invitee->invited_by }}</div>
                @endif
            </td>
            <td>
                <span class="badge-pill badge-status-{{ $invitee->follow_up_status ?? 'new' }}">{{ \App\Http\Controllers\API\Church\InviteeController::STATUSES[$invitee->follow_up_status ?? 'new'] ?? ucfirst($invitee->follow_up_status) }}</span>
                <div class="mt-1">
                    <span class="step-pill {{ $invitee->foundation_clases === 'yes' ? 'step-yes' : 'step-no' }}">Foundation {{ $invitee->foundation_clases === 'yes' ? '✓' : '—' }}</span>
                    <span class="step-pill {{ $invitee->baptism_status === 'yes' ? 'step-yes' : 'step-no' }}">Baptism {{ $invitee->baptism_status === 'yes' ? '✓' : '—' }}</span>
                </div>
            </td>
            <td>@include('portal.churches.invitees.partials.assign', ['invitee' => $invitee, 'occurrence' => null])</td>
            <td class="text-end">
                <div class="d-flex gap-1 justify-content-end flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#followUp{{ $invitee->id }}">Update</button>
                    <form method="POST" action="{{ route('invitees.make-member', $invitee->id) }}" onsubmit="return confirm('Make {{ addslashes($invitee->first_name) }} a member of {{ addslashes(optional($invitee->church)->name) }}?')">
                        @csrf <button class="btn btn-sm btn-success">Make member</button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{ $invitees->links() }}
@endif
</div>
</div>

@foreach($invitees as $invitee)
<div class="modal fade" id="followUp{{ $invitee->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="{{ route('invitees.update', $invitee->id) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Follow-up: {{ trim($invitee->first_name . ' ' . $invitee->last_name) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Follow-up status</label>
                        <select name="follow_up_status" class="form-select">
                            @foreach(\App\Http\Controllers\API\Church\InviteeController::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(($invitee->follow_up_status ?? 'new') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Lives in (area)</label><input name="location" class="form-control" value="{{ $invitee->location }}"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ $invitee->phone }}"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $invitee->email }}"></div>
                    <div class="col-md-6">
                        <label class="form-label">Foundation classes</label>
                        <select name="foundation_clases" class="form-select">
                            <option value="">Not yet</option>
                            <option value="yes" @selected($invitee->foundation_clases === 'yes')>Yes - completed</option>
                            <option value="no" @selected($invitee->foundation_clases === 'no')>No</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Foundation classes date</label><input type="date" name="foundation_clases_date" class="form-control" value="{{ optional($invitee->foundation_clases_date)->format('Y-m-d') }}"></div>
                    <div class="col-md-6">
                        <label class="form-label">Baptism</label>
                        <select name="baptism_status" class="form-select">
                            <option value="">Not yet</option>
                            <option value="yes" @selected($invitee->baptism_status === 'yes')>Yes - baptized</option>
                            <option value="no" @selected($invitee->baptism_status === 'no')>No</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Baptism date</label><input type="date" name="baptism_date" class="form-control" value="{{ optional($invitee->baptism_date)->format('Y-m-d') }}"></div>
                    <div class="col-12">
                        <label class="form-label">Add a note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. Visited at home, will attend Sunday"></textarea>
                        @if($invitee->notes)<div class="inv-meta mt-2" style="white-space:pre-line;">{{ $invitee->notes }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endforeach

</div>
@endsection
