@extends('layouts.admin.master')

@section('title', 'Cell Group Management')

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

    .cell-stat-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(46, 90, 172, 0.08);
        overflow: hidden;
        height: 100%;
    }
    .cell-stat-card .stat-body { display: flex; align-items: center; gap: 16px; padding: 20px; }
    .cell-stat-icon {
        width: 54px; height: 54px; min-width: 54px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff;
    }
    .cell-stat-icon.bg-total    { background: linear-gradient(135deg,#2e5aac,#4d7de0); }
    .cell-stat-icon.bg-members  { background: linear-gradient(135deg,#a855f7,#c084fc); }
    .cell-stat-icon.bg-leader   { background: linear-gradient(135deg,#1fa971,#34d399); }
    .cell-stat-icon.bg-noleader { background: linear-gradient(135deg,#e04b4b,#f0796f); }
    .cell-stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1.1; margin: 0; }
    .cell-stat-label { font-size: .8rem; color: #8a92a6; margin: 0; text-transform: uppercase; letter-spacing: .04em; }

    .cell-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(46,90,172,.06); }
    .cell-filter-bar { background: #f7f9fc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }

    .cell-table thead th {
        background: #f0f3f9; border-bottom: none; font-size: .78rem;
        text-transform: uppercase; letter-spacing: .03em; color: #6b7280; white-space: nowrap;
    }
    .cell-table tbody tr { transition: background .15s ease; }
    .cell-table tbody tr:hover { background: #f7f9fc; }
    .cell-table td { vertical-align: middle; }

    .cell-name-cell { display: flex; align-items: center; gap: 10px; }
    .cell-avatar {
        width: 38px; height: 38px; min-width: 38px; border-radius: 10px;
        background: linear-gradient(135deg,#2e5aac,#4d7de0); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .badge-members-pill { background:#f3e8ff; color:#7e22ce; font-weight:600; padding:4px 10px; border-radius:20px; font-size:.72rem; }
    .badge-leader-pill { background:#e6f7ee; color:#0f9d58; font-weight:600; padding:4px 10px; border-radius:20px; font-size:.72rem; }
    .badge-unassigned { background:#fff4e5; color:#b45309; font-weight:600; padding:4px 10px; border-radius:20px; font-size:.72rem; }
    .badge-designation-pill { background: #eef2ff; color: #4338ca; font-weight: 600; font-size: .68rem; padding: 4px 9px; border-radius: 20px; display: inline-block; margin: 1px 2px 1px 0; }

    .cell-empty { padding: 60px 20px; text-align: center; color: #9aa2b1; }
    .cell-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #c8cedb; }

    /* ---- modal polish ---- */
    .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    .modal-header { border-bottom: none; padding: 18px 24px; }
    .modal-header .modal-title { font-weight: 600; }
    .modal-body { padding: 22px 24px; }
    .modal-body label.form-label { font-weight: 600; font-size: .82rem; color: #4b5563; }
    .modal-body .form-control, .modal-body .form-select { border-radius: 8px; border: 1px solid #e2e6ee; }
    .modal-body .form-control:focus, .modal-body .form-select:focus { border-color: #4d7de0; box-shadow: 0 0 0 3px rgba(77,125,224,.15); }
    .modal-footer { border-top: 1px solid #f0f2f7; padding: 16px 24px; }
    .modal-section-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #9aa2b1; font-weight: 700; margin: 4px 0 10px; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Cell Group Management</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('cells-export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCellModal">
                New Cell Group <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Church</li>
    <li class="breadcrumb-item active">Cell Groups</li>
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
        <div class="card cell-stat-card">
            <div class="stat-body">
                <div class="cell-stat-icon bg-total"><i class="icofont icofont-users-alt-4"></i></div>
                <div>
                    <p class="cell-stat-value">{{ $stats['total'] }}</p>
                    <p class="cell-stat-label">Total Cell Groups</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card cell-stat-card">
            <div class="stat-body">
                <div class="cell-stat-icon bg-members"><i class="icofont icofont-people"></i></div>
                <div>
                    <p class="cell-stat-value">{{ $stats['members'] }}</p>
                    <p class="cell-stat-label">Members in Cells</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card cell-stat-card">
            <div class="stat-body">
                <div class="cell-stat-icon bg-leader"><i class="icofont icofont-crown"></i></div>
                <div>
                    <p class="cell-stat-value">{{ $stats['with_leader'] }}</p>
                    <p class="cell-stat-label">With Leader</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card cell-stat-card">
            <div class="stat-body">
                <div class="cell-stat-icon bg-noleader"><i class="icofont icofont-warning"></i></div>
                <div>
                    <p class="cell-stat-value">{{ $stats['without_leader'] }}</p>
                    <p class="cell-stat-label">Without Leader</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card cell-card">
<div class="card-body">

{{-- ================= FILTER BAR ================= --}}
<form method="GET" action="{{ route('cell.management') }}" class="cell-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-5">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by cell group name">
    </div>
    <div class="col-md-5">
        <label class="form-label mb-1">Church</label>
        <select name="church_id" class="form-control cell-church-select">
            <option value="">All Churches</option>
            @foreach($churches as $church)
                <option value="{{ $church->id }}" @selected(request('church_id') == $church->id)>{{ strtoupper($church->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i> Filter</button>
        @if(request()->anyFilled(['q','church_id']))
        <a href="{{ route('cell.management') }}" class="btn btn-outline-secondary" title="Clear filters"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

{{-- CELL GROUPS TABLE --}}
@if($cellGroups->count())
<div class="table-responsive">
<table class="table cell-table align-middle">
<thead>
<tr>
    <th>#</th>
    <th>Cell Group</th>
    <th>Church</th>
    <th>Members</th>
    <th>Cell Leader</th>
    <th class="text-end">Actions</th>
</tr>
</thead>
<tbody>
@foreach($cellGroups as $cell)
@php($leader = $cell->members->first(fn($m) => $m->pivot->role === 'CELL_LEADER'))
<tr>
    <td>{{ $loop->iteration + ($cellGroups->currentPage() - 1) * $cellGroups->perPage() }}</td>
    <td>
        <div class="cell-name-cell">
            <div class="cell-avatar"><i class="icofont icofont-users-alt-4"></i></div>
            <div>
                <span class="fw-semibold d-block">{{ strtoupper($cell->name) }}</span>
                @if($cell->description)
                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($cell->description, 40) }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>{{ $cell->church->name ?? 'N/A' }}</td>
    <td><span class="badge-members-pill">{{ $cell->members_count ?? $cell->members->count() }}</span></td>
    <td>
        @if($leader)
            {{ $leader->first_name }} {{ $leader->last_name }}
        @else
            <span class="badge-unassigned">Not Assigned</span>
        @endif
    </td>
    <td class="text-end">
        <button class="btn btn-sm btn-light"
            data-bs-toggle="modal"
            data-bs-target="#viewCellMembers{{ $cell->id }}" title="Manage">
            <i class="icofont icofont-users"></i> Manage
        </button>
    </td>
</tr>
@endforeach
</tbody>
</table>

<div class="d-flex justify-content-end">
    {{ $cellGroups->links() }}
</div>
</div>
@else
<div class="cell-empty">
    <i class="icofont icofont-users-alt-4"></i>
    <p class="mb-0">No cell groups found @if(request()->anyFilled(['q','church_id'])) for the selected filters @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

{{-- ================= VIEW CELL MEMBERS MODAL ================= --}}
@foreach($cellGroups as $cell)
<div class="modal fade" id="viewCellMembers{{ $cell->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form method="POST" action="{{ route('cells.update', $cell->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="icofont icofont-users-alt-4"></i> {{ strtoupper($cell->name) }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="modal-section-label">Cell Details</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Cell Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ $cell->name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Church</label>
                            <input type="text" class="form-control"
                                   value="{{ $cell->church->name }}" disabled>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"
                                      rows="2">{{ $cell->description }}</textarea>
                        </div>
                    </div>

                    <hr>

                    <p class="modal-section-label">Cell Members ({{ $cell->members->count() }})</p>

                    @if($cell->members->count())
                        <div class="table-responsive">
                            <table class="table cell-table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member</th>
                                        <th>Designation</th>
                                        <th>Cell Role</th>
                                        <th width="80" class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cell->members as $member)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                {{ $member->first_name }} {{ $member->last_name }}
                                            </td>

                                            <td>
                                                @forelse($member->member_roles as $role)
                                                    <span class="badge-designation-pill">{{ ucwords($role->member_designation->name ?? '') }}</span>
                                                @empty
                                                    <span class="badge-unassigned">N/A</span>
                                                @endforelse
                                            </td>

                                            <td>
                                                @if($member->pivot->role === 'CELL_LEADER')
                                                    <span class="badge-leader-pill">Cell Leader</span>
                                                @elseif($member->pivot->role === 'ASSISTANT_CELL_LEADER')
                                                    <span class="badge-members-pill">Assistant Leader</span>
                                                @else
                                                    <span class="badge-designation-pill">Member</span>
                                                @endif
                                            </td>

                                            <td class="text-end">
                                                <form method="POST"
                                                    action="{{ route('cells.members.remove', [$cell->id, $member->id]) }}"
                                                    onsubmit="return confirm('Remove this member from the cell?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-light text-danger" title="Remove">
                                                        <i class="icofont icofont-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">
                            No members assigned to this cell group.
                        </div>
                    @endif

                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button class="btn btn-primary">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endforeach

{{-- ================= NEW CELL MODAL ================= --}}
<div class="modal fade" id="newCellModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Create Cell Group</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                @livewire('cell-group.create-cell-group')
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    /* --------------------------------
     | SEARCHABLE PICKERS (Select2)
     |---------------------------------*/
    (function () {
        var $ = window.jQuery;
        if (!$ || !$.fn.select2) return;

        function initSelect2In(root, selector, opts) {
            var $scope = root ? $(root) : $(document);
            $scope.find(selector).each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                var $modal = $el.closest('.modal');
                $el.select2(Object.assign({
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $modal.length ? $modal : undefined
                }, opts || {}));
            });
        }

        // Church filter on the main listing (plain page select, init once).
        // Note: deliberately NOT applied inside the New Cell Group modal - that
        // form is a Livewire component whose member rows re-render on every
        // add/remove/validation round-trip. Select2 wraps a <select> with extra
        // DOM Livewire doesn't know about, and Livewire's morphdom diff against
        // that untracked markup can break the component's click handlers
        // entirely (e.g. "Add Member" appearing to do nothing).
        initSelect2In(document, '.cell-church-select', { placeholder: 'Search for a church...' });
    })();
</script>
@endpush
