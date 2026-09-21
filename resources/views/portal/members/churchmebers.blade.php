@extends('layouts.admin.master')

@section('title', 'Member Management')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select2.css') }}">
<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #e2e6ee !important;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
        padding: 0 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: normal; padding-left: 6px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #4d7de0 !important;
        box-shadow: 0 0 0 3px rgba(77,125,224,.15);
    }
    .select2-dropdown { border-radius: 8px; border-color: #e2e6ee; overflow: hidden; }
    .select2-search--dropdown .select2-search__field { border-radius: 6px; border: 1px solid #e2e6ee; padding: 6px 8px; }

    .member-stat-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(46, 90, 172, 0.08);
        overflow: hidden;
        height: 100%;
    }
    .member-stat-card .stat-body {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
    }
    .member-stat-icon {
        width: 54px;
        height: 54px;
        min-width: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
    }
    .member-stat-icon.bg-total      { background: linear-gradient(135deg,#2e5aac,#4d7de0); }
    .member-stat-icon.bg-foundation { background: linear-gradient(135deg,#d08c1d,#f0b429); }
    .member-stat-icon.bg-baptized   { background: linear-gradient(135deg,#0ea5a0,#2dd4bf); }
    .member-stat-icon.bg-married    { background: linear-gradient(135deg,#c2418c,#e879b9); }
    .member-stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1.1; margin: 0; }
    .member-stat-label { font-size: .8rem; color: #8a92a6; margin: 0; text-transform: uppercase; letter-spacing: .04em; }

    .member-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(46,90,172,.06); }
    .member-filter-bar { background: #f7f9fc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }

    .member-table thead th {
        background: #f0f3f9;
        border-bottom: none;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6b7280;
        white-space: nowrap;
    }
    .member-table tbody tr { transition: background .15s ease; }
    .member-table tbody tr:hover { background: #f7f9fc; }
    .member-table td { vertical-align: middle; }

    .member-name-cell { display: flex; align-items: center; gap: 10px; }
    .member-avatar {
        width: 38px; height: 38px; min-width: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg,#2e5aac,#4d7de0);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700;
    }
    .member-contact { font-size: .78rem; color: #6b7280; }
    .member-contact div { display: flex; align-items: center; gap: 5px; }

    .badge-designation-pill {
        background: #eef2ff; color: #4338ca; font-weight: 600; font-size: .68rem;
        padding: 4px 9px; border-radius: 20px; display: inline-block; margin: 1px 2px 1px 0;
    }
    .badge-status-active { background:#e6f7ee; color:#0f9d58; font-weight:600; padding:5px 12px; border-radius:20px; font-size:.72rem; }
    .badge-na { color: #9aa2b1; font-size: .78rem; font-style: italic; }

    .member-empty { padding: 60px 20px; text-align: center; color: #9aa2b1; }
    .member-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #c8cedb; }

    /* ---- modal polish ---- */
    .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    .modal-header { border-bottom: none; padding: 18px 24px; }
    .modal-header .modal-title { font-weight: 600; }
    .modal-body { padding: 22px 24px; }
    .modal-body label.form-label { font-weight: 600; font-size: .82rem; color: #4b5563; }
    .modal-body .form-control, .modal-body .form-select { border-radius: 8px; border: 1px solid #e2e6ee; }
    .modal-body .form-control:focus, .modal-body .form-select:focus { border-color: #4d7de0; box-shadow: 0 0 0 3px rgba(77,125,224,.15); }
    .modal-footer { border-top: 1px solid #f0f2f7; padding: 16px 24px; }
    .nav-tabs .nav-link { font-weight: 600; font-size: .85rem; }
    .modal-section-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #9aa2b1; font-weight: 700; margin: 4px 0 10px; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Member Management</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('members-export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMemberModal">
                New Member <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Member</li>
    <li class="breadcrumb-item active">Management</li>
@endcomponent

<div class="container-fluid">

{{-- ERRORS --}}
@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $error }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endforeach
@endif

{{-- SUCCESS --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ================= STAT CARDS ================= --}}
<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card member-stat-card">
            <div class="stat-body">
                <div class="member-stat-icon bg-total"><i class="icofont icofont-people"></i></div>
                <div>
                    <p class="member-stat-value">{{ $stats['total'] }}</p>
                    <p class="member-stat-label">Total Members</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card member-stat-card">
            <div class="stat-body">
                <div class="member-stat-icon bg-foundation"><i class="icofont icofont-graduate-alt"></i></div>
                <div>
                    <p class="member-stat-value">{{ $stats['foundation'] }}</p>
                    <p class="member-stat-label">Foundation Classes</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card member-stat-card">
            <div class="stat-body">
                <div class="member-stat-icon bg-baptized"><i class="icofont icofont-water-drop"></i></div>
                <div>
                    <p class="member-stat-value">{{ $stats['baptized'] }}</p>
                    <p class="member-stat-label">Baptized</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card member-stat-card">
            <div class="stat-body">
                <div class="member-stat-icon bg-married"><i class="icofont icofont-heart-alt"></i></div>
                <div>
                    <p class="member-stat-value">{{ $stats['married'] }}</p>
                    <p class="member-stat-label">Married</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card member-card">
<div class="card-body">

{{-- ================= FILTER BAR ================= --}}
<form method="GET" action="{{ route('member.management') }}" class="member-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name, phone or email">
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Church</label>
        <select name="church_id" class="form-control">
            <option value="">All Churches</option>
            @foreach($churches as $church)
                <option value="{{ $church->id }}" @selected(request('church_id') == $church->id)>{{ strtoupper($church->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Designation</label>
        <select name="designation_id" class="form-control">
            <option value="">All Designations</option>
            @foreach($designations as $des)
                <option value="{{ $des->id }}" @selected(request('designation_id') == $des->id)>{{ ucwords($des->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i> Filter</button>
        @if(request()->anyFilled(['q','church_id','designation_id']))
        <a href="{{ route('member.management') }}" class="btn btn-outline-secondary" title="Clear filters"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

{{-- MEMBERS TABLE --}}
@if($Members->count())
<div class="table-responsive">
    <table class="table member-table align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Member</th>
                <th>Contact</th>
                <th>Designation</th>
                <th>Church</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Members as $Member)
                <tr>
                    <td>{{ $loop->iteration + ($Members->currentPage() - 1) * $Members->perPage() }}</td>

                    <td>
                        <div class="member-name-cell">
                            <div class="member-avatar">{{ strtoupper(substr($Member->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($Member->last_name ?? '', 0, 1)) }}</div>
                            <span class="fw-semibold">{{ strtoupper(trim($Member->first_name . ' ' . $Member->last_name)) }}</span>
                        </div>
                    </td>

                    <td>
                        <div class="member-contact">
                            @if($Member->phone)
                                <div><i class="icofont icofont-phone"></i> {{ $Member->phone }}</div>
                            @endif
                            @if($Member->email)
                                <div><i class="icofont icofont-email"></i> {{ $Member->email }}</div>
                            @endif
                            @if(!$Member->phone && !$Member->email)
                                <span class="badge-na">N/A</span>
                            @endif
                        </div>
                    </td>

                    <td>
                        @forelse($Member->member_roles as $role)
                            <span class="badge-designation-pill">{{ ucwords($role->member_designation->name ?? '') }}</span>
                        @empty
                            <span class="badge-na">N/A</span>
                        @endforelse
                    </td>

                    <td>{{ $Member->church->name ?? 'N/A' }}</td>

                    <td>
                        <span class="badge-status-active">ACTIVE</span>
                    </td>

                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#viewMemberModal{{ $Member->id }}" title="View">
                                <i class="icofont icofont-eye"></i>
                            </button>
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#editMemberModal{{ $Member->id }}" title="Edit">
                                <i class="icofont icofont-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        {{ $Members->links() }}
    </div>
</div>
@else
<div class="member-empty">
    <i class="icofont icofont-people"></i>
    <p class="mb-0">No members found @if(request()->anyFilled(['q','church_id','designation_id'])) for the selected filters @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

{{-- ================= NEW MEMBER MODAL ================= --}}
<div class="modal fade" id="newMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Add New Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                @livewire('members.create-member')
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ================= VIEW MEMBER MODAL ================= --}}
@foreach($Members as $Member)
<div class="modal fade" id="viewMemberModal{{ $Member->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="icofont icofont-user-alt-3"></i> {{ $Member->first_name }} {{ $Member->last_name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <strong>Full Name:</strong><br>
                        {{ $Member->first_name }} {{ $Member->last_name }}
                    </div>

                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        {{ $Member->email ?? 'N/A' }}
                    </div>

                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        {{ $Member->phone ?? 'N/A' }}
                    </div>

                    <div class="col-md-6">
                        <strong>Church:</strong><br>
                        {{ $Member->church->name ?? 'N/A' }}
                    </div>

                    <div class="col-md-6">
                        <strong>Church Location:</strong><br>
                        {{ $Member->church->physical_location ?? 'N/A' }}
                    </div>

                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge-status-active">ACTIVE</span>
                    </div>

                    <div class="col-md-12">
                        <strong>Designations:</strong><br>
                        @forelse($Member->member_roles as $role)
                            <span class="badge-designation-pill">{{ ucwords($role->member_designation->name ?? '') }}</span>
                        @empty
                            <span class="badge-na">No designation assigned</span>
                        @endforelse
                    </div>

                    <div class="col-md-12">
                        <strong>Departments:</strong><br>
                        @forelse($Member->departments as $department)
                            <span class="badge-designation-pill">{{ $department->name }}</span>
                        @empty
                            <span class="badge-na">Not assigned</span>
                        @endforelse
                    </div>

                    <div class="col-md-12">
                        <strong>Cell Groups:</strong><br>
                        @forelse($Member->cell_groups as $cell)
                            <span class="badge-designation-pill">{{ $cell->name }}</span>
                        @empty
                            <span class="badge-na">Not assigned</span>
                        @endforelse
                    </div>

                    <div class="col-md-6">
                        <strong>Joined On:</strong><br>
                        {{ $Member->created_at->format('d M Y') }}
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Foundation Classes:</strong><br>
                            <span class="badge bg-{{ $Member->foundation_clases === 'yes' ? 'success' : 'secondary' }}">
                                {{ strtoupper($Member->foundation_clases ?? 'N/A') }}
                            </span>
                        </div>

                        <div class="col-md-6">
                            <strong>Foundation Class Date:</strong><br>
                            {{ $Member->foundation_clases_date ? $Member->foundation_clases_date->format('d M Y') : 'N/A' }}
                        </div>

                        <div class="col-md-6">
                            <strong>Baptism Status:</strong><br>
                            <span class="badge bg-{{ $Member->baptism_status === 'yes' ? 'success' : 'secondary' }}">
                                {{ strtoupper($Member->baptism_status ?? 'N/A') }}
                            </span>
                        </div>

                        <div class="col-md-6">
                            <strong>Baptism Date:</strong><br>
                            {{ $Member->baptism_date ? $Member->baptism_date->format('d M Y') : 'N/A' }}
                        </div>

                        <div class="col-md-6">
                            <strong>Marriage Status:</strong><br>
                            <span class="badge bg-{{ $Member->marriage_status === 'married' ? 'success' : 'secondary' }}">
                                {{ strtoupper($Member->marriage_status ?? 'N/A') }}
                            </span>
                        </div>

                        <div class="col-md-6">
                            <strong>Marriage Date:</strong><br>
                            {{ $Member->marriage_dates ? $Member->marriage_dates->format('d M Y') : 'N/A' }}
                        </div>

                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
@endforeach

{{-- ================= EDIT MEMBER MODAL ================= --}}
@foreach($Members as $Member)
<div class="modal fade" id="editMemberModal{{ $Member->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form method="POST" action="{{ route('members.update', $Member->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="icofont icofont-edit"></i> Edit Member — {{ $Member->first_name }} {{ $Member->last_name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- TABS --}}
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile{{ $Member->id }}">
                                Profile
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#roles{{ $Member->id }}">
                                Roles
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#groups{{ $Member->id }}">
                                Groups
                            </button>
                        </li>
                    </ul>

                    {{-- TAB CONTENT --}}
                    <div class="tab-content mt-3">

                        {{-- ================= PROFILE TAB ================= --}}
                        <div class="tab-pane fade show active" id="profile{{ $Member->id }}">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control"
                                           value="{{ $Member->first_name }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control"
                                           value="{{ $Member->last_name }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ $Member->email }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control"
                                           value="{{ $Member->phone }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Church</label>
                                    <select name="church_id" class="form-select member-church-select">
                                        @foreach($churches as $church)
                                            <option value="{{ $church->id }}"
                                                {{ $Member->church_id == $church->id ? 'selected' : '' }}>
                                                {{ strtoupper($church->name) }}@if(optional($church->current_head)->member) ({{ $church->current_head->member->first_name }} {{ $church->current_head->member->last_name }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Foundation Classes</label><br>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input foundation-toggle"
                                                   type="radio"
                                                   name="foundation_clases"
                                                   value="yes"
                                                   data-target="foundationDate{{ $Member->id }}"
                                                   {{ $Member->foundation_clases === 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label">Yes</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input foundation-toggle"
                                                   type="radio"
                                                   name="foundation_clases"
                                                   value="no"
                                                   data-target="foundationDate{{ $Member->id }}"
                                                   {{ $Member->foundation_clases === 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6"
                                         id="foundationDate{{ $Member->id }}"
                                         style="{{ $Member->foundation_clases === 'yes' ? '' : 'display:none;' }}">
                                        <label class="form-label">Foundation Class Date</label>
                                        <input type="date" class="form-control"
                                               name="foundation_clases_date"
                                               value="{{ optional($Member->foundation_clases_date)->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Baptism Status</label><br>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input baptism-toggle"
                                                   type="radio"
                                                   name="baptism_status"
                                                   value="yes"
                                                   data-target="baptismDate{{ $Member->id }}"
                                                   {{ $Member->baptism_status === 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label">Yes</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input baptism-toggle"
                                                   type="radio"
                                                   name="baptism_status"
                                                   value="no"
                                                   data-target="baptismDate{{ $Member->id }}"
                                                   {{ $Member->baptism_status === 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6"
                                         id="baptismDate{{ $Member->id }}"
                                         style="{{ $Member->baptism_status === 'yes' ? '' : 'display:none;' }}">
                                        <label class="form-label">Baptism Date</label>
                                        <input type="date" class="form-control"
                                               name="baptism_date"
                                               value="{{ optional($Member->baptism_date)->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Marriage Status</label><br>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input marriage-toggle"
                                                   type="radio"
                                                   name="marriage_status"
                                                   value="married"
                                                   data-target="marriageDate{{ $Member->id }}"
                                                   {{ $Member->marriage_status === 'married' ? 'checked' : '' }}>
                                            <label class="form-check-label">Married</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input marriage-toggle"
                                                   type="radio"
                                                   name="marriage_status"
                                                   value="single"
                                                   data-target="marriageDate{{ $Member->id }}"
                                                   {{ $Member->marriage_status === 'single' ? 'checked' : '' }}>
                                            <label class="form-check-label">Single</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6"
                                         id="marriageDate{{ $Member->id }}"
                                         style="{{ $Member->marriage_status === 'married' ? '' : 'display:none;' }}">
                                        <label class="form-label">Marriage Date</label>
                                        <input type="date" class="form-control"
                                               name="marriage_dates"
                                               value="{{ optional($Member->marriage_dates)->format('Y-m-d') }}">
                                    </div>

                                </div>

                            </div>
                        </div>

                        {{-- ================= ROLES TAB ================= --}}
                        <div class="tab-pane fade" id="roles{{ $Member->id }}">
                            <div class="row g-3">

                                @foreach($designations as $designation)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="designations[]"
                                                   value="{{ $designation->id }}"
                                                   {{ $Member->member_roles->contains('designation_id', $designation->id) ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                {{ ucwords($designation->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>

                        {{-- ================= GROUPS TAB ================= --}}
                        <div class="tab-pane fade" id="groups{{ $Member->id }}">
                            <div class="row g-3">

                                <div class="col-md-12">
                                    <label class="form-label">Cell Groups</label>

                                    @php
                                        $churchCells = $cellGroups->where('church_id', $Member->church_id);
                                        $memberCells = $Member->cell_groups;
                                    @endphp

                                    {{-- Church has NO cell groups --}}
                                    @if($churchCells->isEmpty())
                                        <div class="alert alert-warning py-2 mb-0">
                                            <i class="icofont icofont-warning"></i>
                                            This church has no cell groups.
                                        </div>

                                    {{-- Church has cells but member not assigned --}}
                                    @elseif($memberCells->isEmpty())
                                        <div class="alert alert-info py-2 mb-2">
                                            <i class="icofont icofont-info-circle"></i>
                                            This member does not belong to any cell group yet. Please Select to Assing
                                        </div>

                                        <select name="cell_groups[]" class="form-select" multiple>
                                            @foreach($churchCells as $group)
                                                <option value="{{ $group->id }}">
                                                    {{ $group->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                    {{-- Normal case --}}
                                    @else
                                        <select name="cell_groups[]" class="form-select" multiple>
                                            @foreach($churchCells as $group)
                                                <option value="{{ $group->id }}"
                                                    {{ $memberCells->contains($group->id) ? 'selected' : '' }}>
                                                    {{ $group->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>


                                <div class="col-md-12">
                                    <label class="form-label">Departments</label>
                                    <select name="departments[]" class="form-select" multiple>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ $Member->departments->contains($department->id) ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button class="btn btn-primary">
                        Update Member
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    document.addEventListener('change', function (e) {
        if (e.target.matches('input[type="radio"][data-target]')) {
            const targetId = e.target.getAttribute('data-target');
            const target = document.getElementById(targetId);

            if (!target) return;

            if (e.target.value === 'yes' || e.target.value === 'married') {
                target.style.display = '';
            } else {
                target.style.display = 'none';
                const input = target.querySelector('input[type="date"]');
                if (input) input.value = '';
            }
        }
    });

    /* --------------------------------
     | SEARCHABLE "CHURCH" PICKER (Select2) — Edit Member modal only.
     | Deliberately NOT applied to the New Member modal's Church select - that
     | form is a Livewire component that re-renders on every server round-trip
     | (e.g. a validation error). Select2 wraps a <select> with extra DOM
     | Livewire doesn't know about, and Livewire's morphdom diff against that
     | untracked markup can break the component's click handlers entirely.
     |---------------------------------*/
    (function () {
        var $ = window.jQuery;
        if (!$ || !$.fn.select2) return;

        function initChurchSelect2(scope) {
            var $scope = scope ? $(scope) : $(document);
            $scope.find('select.member-church-select').each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                var $modal = $el.closest('.modal');
                $el.select2({
                    placeholder: 'Search for a church...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $modal.length ? $modal : undefined
                });
            });
        }

        // Edit Member modals: one per row, so delegate on the id prefix.
        $(document).on('shown.bs.modal', '[id^="editMemberModal"]', function () {
            initChurchSelect2(this);
        });
    })();
</script>
@endpush
