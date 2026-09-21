@extends('layouts.admin.master')

@section('title')
Church Management
@endsection

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

    .church-stat-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(46, 90, 172, 0.08);
        overflow: hidden;
        height: 100%;
    }
    .church-stat-card .stat-body {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
    }
    .church-stat-icon {
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
    .church-stat-icon.bg-total   { background: linear-gradient(135deg,#2e5aac,#4d7de0); }
    .church-stat-icon.bg-active  { background: linear-gradient(135deg,#1fa971,#34d399); }
    .church-stat-icon.bg-inactive{ background: linear-gradient(135deg,#e04b4b,#f0796f); }
    .church-stat-icon.bg-members { background: linear-gradient(135deg,#a855f7,#c084fc); }
    .church-stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1.1; margin: 0; }
    .church-stat-label { font-size: .8rem; color: #8a92a6; margin: 0; text-transform: uppercase; letter-spacing: .04em; }

    .church-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(46,90,172,.06); }

    .church-filter-bar { background: #f7f9fc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }

    .church-table thead th {
        background: #f0f3f9;
        border-bottom: none;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6b7280;
        white-space: nowrap;
    }
    .church-table tbody tr { transition: background .15s ease; }
    .church-table tbody tr:hover { background: #f7f9fc; }
    .church-table td { vertical-align: middle; }

    .church-name-cell { display: flex; align-items: center; gap: 10px; }
    .church-avatar {
        width: 38px; height: 38px; min-width: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg,#2e5aac,#4d7de0);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .badge-designation {
        background: #eef2ff; color: #4338ca; font-weight: 600; font-size: .72rem;
        padding: 5px 10px; border-radius: 20px;
    }
    .badge-status-active   { background:#e6f7ee; color:#0f9d58; font-weight:600; padding:5px 12px; border-radius:20px; font-size:.72rem; }
    .badge-status-inactive { background:#fdecec; color:#d93025; font-weight:600; padding:5px 12px; border-radius:20px; font-size:.72rem; }
    .badge-members { background:#f3e8ff; color:#7e22ce; font-weight:600; padding:4px 10px; border-radius:20px; font-size:.72rem; }
    .badge-unassigned { background:#fff4e5; color:#b45309; font-weight:600; padding:4px 10px; border-radius:20px; font-size:.72rem; }

    .pastor-cell { display: flex; align-items: center; gap: 8px; }
    .pastor-avatar {
        width: 26px; height: 26px; min-width: 26px; border-radius: 50%;
        background: #eef2ff; color: #4338ca; font-weight: 700; font-size: .7rem;
        display: flex; align-items: center; justify-content: center;
    }

    /* ---- modal polish ---- */
    .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    .modal-header { border-bottom: none; padding: 18px 24px; }
    .modal-header .modal-title { font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .modal-body { padding: 22px 24px; }
    .modal-body label { font-weight: 600; font-size: .82rem; color: #4b5563; margin-bottom: 4px; display: block; }
    .modal-body .form-control, .modal-body select.form-control { border-radius: 8px; border: 1px solid #e2e6ee; padding: 9px 12px; }
    .modal-body .form-control:focus { border-color: #4d7de0; box-shadow: 0 0 0 3px rgba(77,125,224,.15); }
    .modal-footer { border-top: 1px solid #f0f2f7; padding: 16px 24px; }
    .modal-section-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #9aa2b1; font-weight: 700; margin: 4px 0 10px; }

    .church-empty { padding: 60px 20px; text-align: center; color: #9aa2b1; }
    .church-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #c8cedb; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Church Management</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('churches-export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newChurchModal">
                New Church <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Church</li>
    <li class="breadcrumb-item active">Management</li>
@endcomponent

<div class="container-fluid">

{{-- ERRORS --}}
@foreach ($errors->all() as $error)
<div class="alert alert-danger alert-dismissible fade show">
    {{ $error }}
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endforeach

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
        <div class="card church-stat-card">
            <div class="stat-body">
                <div class="church-stat-icon bg-total"><i class="icofont icofont-building-alt"></i></div>
                <div>
                    <p class="church-stat-value">{{ $stats['total'] }}</p>
                    <p class="church-stat-label">Total Churches</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card church-stat-card">
            <div class="stat-body">
                <div class="church-stat-icon bg-active"><i class="icofont icofont-check-circled"></i></div>
                <div>
                    <p class="church-stat-value">{{ $stats['active'] }}</p>
                    <p class="church-stat-label">Active</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card church-stat-card">
            <div class="stat-body">
                <div class="church-stat-icon bg-inactive"><i class="icofont icofont-close-circled"></i></div>
                <div>
                    <p class="church-stat-value">{{ $stats['inactive'] }}</p>
                    <p class="church-stat-label">Inactive</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card church-stat-card">
            <div class="stat-body">
                <div class="church-stat-icon bg-members"><i class="icofont icofont-group"></i></div>
                <div>
                    <p class="church-stat-value">{{ $stats['members'] }}</p>
                    <p class="church-stat-label">Total Members</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card church-card">
<div class="card-body">

{{-- ================= FILTER BAR ================= --}}
<form method="GET" action="{{ route('churches-management') }}" class="church-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or location">
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Designation</label>
        <select name="designation_id" class="form-control">
            <option value="">All Designations</option>
            @foreach($designations as $des)
                <option value="{{ $des->id }}" @selected(request('designation_id') == $des->id)>{{ strtoupper($des->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="ACTIVE" @selected(request('status') == 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status') == 'INACTIVE')>Inactive</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i> Filter</button>
        @if(request()->anyFilled(['q','designation_id','status']))
        <a href="{{ route('churches-management') }}" class="btn btn-outline-secondary" title="Clear filters"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

{{-- CHURCH TABLE --}}
@if($churches->count())
<div class="table-responsive">
<table class="table church-table align-middle">
<thead>
<tr>
    <th>#</th>
    <th>Church</th>
    <th>Designation</th>
    <th>Parent Church</th>
    <th>Head of Church</th>
    <th>Location</th>
    <th>Members</th>
    <th>Status</th>
    <th class="text-end">Actions</th>
</tr>
</thead>
<tbody>
@foreach($churches as $church)
@php($pastor = optional($church->current_head)->member)
<tr>
    <td>{{ $loop->iteration + ($churches->currentPage() - 1) * $churches->perPage() }}</td>
    <td>
        <div class="church-name-cell">
            <div class="church-avatar"><i class="icofont icofont-building-alt"></i></div>
            <span class="fw-semibold">{{ strtoupper($church->name) }}</span>
        </div>
    </td>
    <td><span class="badge-designation">{{ strtoupper($church->church_designation->name ?? '-') }}</span></td>
    <td>{{ $church->church ? strtoupper($church->church->name) : 'ROOT' }}</td>
    <td>
        @if($pastor)
            <div class="pastor-cell">
                <div class="pastor-avatar">{{ strtoupper(substr($pastor->first_name ?? '?', 0, 1)) }}</div>
                <span>{{ $pastor->first_name }} {{ $pastor->last_name }}</span>
            </div>
        @else
            <span class="badge-unassigned">Not Assigned</span>
        @endif
    </td>
    <td>{{ $church->physical_location }}</td>
    <td><span class="badge-members">{{ $church->members_count ?? 0 }}</span></td>
    <td>
        <span class="{{ $church->status == 'ACTIVE' ? 'badge-status-active' : 'badge-status-inactive' }}">
            {{ $church->status }}
        </span>
    </td>
    <td class="text-end">
        <div class="dropdown">
            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icofont icofont-navigation-menu"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item edit-church-btn" href="javascript:void(0)"
                        data-bs-toggle="modal" data-bs-target="#editChurchModal"
                        data-id="{{ $church->id }}"
                        data-name="{{ $church->name }}"
                        data-location="{{ $church->physical_location }}"
                        data-designation="{{ $church->designation_id }}"
                        data-parent="{{ $church->parent_church_id }}"
                        data-head="{{ optional($church->current_head)->head_of_unit }}">
                        <i class="icofont icofont-edit"></i> Edit
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal"
                        data-bs-target="#transferModal" data-id="{{ $church->id }}">
                        <i class="icofont icofont-exchange"></i> Transfer
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('churches.transfer.history', $church->id) }}">
                        <i class="icofont icofont-history"></i> History
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    @if($church->status == 'ACTIVE')
                    <a href="{{ route('churches-deactivate',$church->id) }}"
                       class="dropdown-item text-danger"
                       onclick="return confirm('Deactivate this church?')">
                       <i class="icofont icofont-close-circled"></i> Deactivate
                    </a>
                    @else
                    <a href="{{ route('churches-activate',$church->id) }}"
                       class="dropdown-item text-success"
                       onclick="return confirm('Activate this church?')">
                       <i class="icofont icofont-check-circled"></i> Activate
                    </a>
                    @endif
                </li>
            </ul>
        </div>
    </td>
</tr>
@endforeach
</tbody>
</table>

{{ $churches->links() }}
</div>
@else
<div class="church-empty">
    <i class="icofont icofont-building-alt"></i>
    <p class="mb-0">No churches found @if(request()->anyFilled(['q','designation_id','status'])) for the selected filters @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

{{-- ================= NEW CHURCH MODAL ================= --}}
<div class="modal fade" id="newChurchModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" action="{{ route('churches-store') }}">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-building-alt"></i> Register Church</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Church Details</p>
<div class="row">

<div class="col-md-6">
    <label>Name</label>
    <input type="text" name="name" class="form-control" required>
</div>

<div class="col-md-6">
    <label>Location</label>
    <input type="text" name="physical_location" class="form-control" required>
</div>

<div class="col-md-6 mt-3">
    <label>Church Designation</label>
    <select name="designation_id" id="designation_id" class="form-control" required>
        <option value="">-- Select --</option>
        @foreach($designations as $des)
            <option value="{{ $des->id }}">{{ strtoupper($des->name) }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-6 mt-3">
    <label>Parent Church</label>
    <select name="parent_church_id" id="parent_church_id" class="form-control">
        <option value="">-- ROOT --</option>
    </select>
</div>

</div>

<hr class="my-3">
<p class="modal-section-label"><i class="icofont icofont-user-alt-3"></i> Leadership</p>
<div class="row">
<div class="col-md-12">
    <label>Head of Church (Pastor)</label>
    <select name="head_of_unit" class="form-control church-head-select">
        <option value="">-- Not Assigned Yet --</option>
        @foreach($members as $m)
            <option value="{{ $m->id }}">{{ $m->first_name }} {{ $m->last_name }}@if($m->member_roles->first()) — {{ ucwords($m->member_roles->first()->member_designation->name ?? '') }}@endif (@if($m->church){{ strtoupper($m->church->name) }}@endif)</option>
        @endforeach
    </select>
    <small class="text-muted">Optional — you can assign a head later from the church's Edit action.</small>
</div>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">Register Church</button>
</div>

</form>
</div>
</div>
</div>

{{-- ================= EDIT CHURCH MODAL ================= --}}
<div class="modal fade" id="editChurchModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" id="editChurchForm" action="">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-edit"></i> Edit Church</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Church Details</p>
<div class="row">

<div class="col-md-6">
    <label>Name</label>
    <input type="text" name="name" id="edit_name" class="form-control" required>
</div>

<div class="col-md-6">
    <label>Location</label>
    <input type="text" name="physical_location" id="edit_physical_location" class="form-control" required>
</div>

<div class="col-md-6 mt-3">
    <label>Church Designation</label>
    <select name="designation_id" id="edit_designation_id" class="form-control" required>
        <option value="">-- Select --</option>
        @foreach($designations as $des)
            <option value="{{ $des->id }}">{{ strtoupper($des->name) }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-6 mt-3">
    <label>Parent Church</label>
    <select name="parent_church_id" id="edit_parent_church_id" class="form-control">
        <option value="">-- ROOT --</option>
    </select>
</div>

</div>

<hr class="my-3">
<p class="modal-section-label"><i class="icofont icofont-user-alt-3"></i> Leadership</p>
<div class="row">
<div class="col-md-12">
    <label>Head of Church (Pastor)</label>
    <select name="head_of_unit" id="edit_head_of_unit" class="form-control church-head-select">
        <option value="">-- Not Assigned --</option>
        @foreach($members as $m)
            <option value="{{ $m->id }}">{{ $m->first_name }} {{ $m->last_name }}@if($m->member_roles->first()) — {{ ucwords($m->member_roles->first()->member_designation->name ?? '') }}@endif (@if($m->church){{ strtoupper($m->church->name) }}@endif)</option>
        @endforeach
    </select>
    <small class="text-muted">Selecting a different member records a new leadership assignment for this church.</small>
</div>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">Save Changes</button>
</div>

</form>
</div>
</div>
</div>

{{-- ================= TRANSFER MODAL ================= --}}
<div class="modal fade" id="transferModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="{{ route('churches-transfer') }}">
@csrf

<input type="hidden" name="church_id" id="transfer_church_id">

<div class="modal-header bg-warning">
    <h5 class="modal-title"><i class="icofont icofont-exchange"></i> Transfer Church</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<label>New Parent Church</label>
<select name="parent_church_id" class="form-control" required>
@foreach($churches as $c)
    <option value="{{ $c->id }}">{{ strtoupper($c->name) }}</option>
@endforeach
</select>
<small class="text-muted d-block mt-2">This moves the church to a new parent one designation level up and keeps a full history of the change.</small>
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-warning">Transfer</button>
</div>

</form>
</div>
</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
const designationHierarchy = @json(
    $designations->sortBy('id')->values()
);

function wireDesignationParent(designationSelectId, parentSelectId) {
    document.getElementById(designationSelectId).addEventListener('change', function () {
        const selectedId = parseInt(this.value);
        const parentSelect = document.getElementById(parentSelectId);

        parentSelect.innerHTML = '<option value="">-- ROOT --</option>';

        if (!selectedId) return;

        const currentIndex = designationHierarchy.findIndex(d => d.id === selectedId);

        if (currentIndex <= 0) return;

        const parentDesignationId = designationHierarchy[currentIndex - 1].id;

        fetch(`/v1/api/churches/by-designation/${parentDesignationId}`)
        .then(res => res.json())
        .then(data => {
            parentSelect.innerHTML = '<option value="">-- ROOT --</option>';
            data.forEach(ch => {
                parentSelect.innerHTML +=
                    `<option value="${ch.id}">${ch.name}</option>`;
            });
        });
    });
}

wireDesignationParent('designation_id', 'parent_church_id');
wireDesignationParent('edit_designation_id', 'edit_parent_church_id');

document.querySelectorAll('[data-bs-target="#transferModal"]').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('transfer_church_id').value = this.dataset.id;
    });
});

document.querySelectorAll('.edit-church-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        const designationId = parseInt(this.dataset.designation);
        const parentId = this.dataset.parent;

        document.getElementById('editChurchForm').action = `/v1/churches/${id}/update`;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_physical_location').value = this.dataset.location;
        document.getElementById('edit_designation_id').value = designationId;
        document.getElementById('edit_head_of_unit').value = this.dataset.head || '';

        const parentSelect = document.getElementById('edit_parent_church_id');
        parentSelect.innerHTML = '<option value="">-- ROOT --</option>';

        const currentIndex = designationHierarchy.findIndex(d => d.id === designationId);
        if (currentIndex > 0) {
            const parentDesignationId = designationHierarchy[currentIndex - 1].id;
            fetch(`/v1/api/churches/by-designation/${parentDesignationId}`)
            .then(res => res.json())
            .then(data => {
                parentSelect.innerHTML = '<option value="">-- ROOT --</option>';
                data.forEach(ch => {
                    const selected = (parentId && parseInt(parentId) === ch.id) ? 'selected' : '';
                    parentSelect.innerHTML +=
                        `<option value="${ch.id}" ${selected}>${ch.name}</option>`;
                });
            });
        }
    });
});

/* --------------------------------
 | SEARCHABLE "HEAD OF CHURCH" PICKER (Select2)
 | jQuery/Bootstrap already load before this pushed script (see
 | layouts/admin/partials/js.blade.php), so no readiness polling needed.
 |---------------------------------*/
(function () {
    var $ = window.jQuery;
    if (!$ || !$.fn.select2) return;

    function initSelect2In(modalId) {
        $(modalId).on('shown.bs.modal', function () {
            var $modal = $(this);
            $modal.find('select.church-head-select').each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    placeholder: 'Search for a member...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $modal
                });
            });
        });
    }

    initSelect2In('#newChurchModal');
    initSelect2In('#editChurchModal');
})();
</script>
@endpush
