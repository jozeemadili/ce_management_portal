@extends('layouts.admin.master')

@section('title', 'Pledge Campaigns')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Pledge Campaigns</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('pledge-campaigns.export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCampaignModal">
                New Campaign <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Campaigns</li>
@endcomponent

<div class="container-fluid">

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $error }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endforeach
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-1"><i class="icofont icofont-bullseye"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $stats['total'] }}</p>
                    <p class="pledge-stat-label">Total Campaigns</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-2"><i class="icofont icofont-flag"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $stats['active'] }}</p>
                    <p class="pledge-stat-label">Active Campaigns</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-4"><i class="icofont icofont-money-bag"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ number_format($stats['target']) }}</p>
                    <p class="pledge-stat-label">Total Target</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-3"><i class="icofont icofont-coins"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ number_format($stats['pledged']) }}</p>
                    <p class="pledge-stat-label">Total Pledged</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card pledge-card">
<div class="card-body">

<form method="GET" action="{{ route('pledge-campaigns.index') }}" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by campaign name">
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All Status</option>
            @foreach(['draft','active','completed','closed'] as $s)
                <option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Scope</label>
        <select name="scope" class="form-control">
            <option value="">All Scopes</option>
            <option value="global" @selected(request('scope') == 'global')>Global</option>
            <option value="church" @selected(request('scope') == 'church')>Church Specific</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i> Filter</button>
        @if(request()->anyFilled(['q','status','scope']))
        <a href="{{ route('pledge-campaigns.index') }}" class="btn btn-outline-secondary" title="Clear filters"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

@if($campaigns->count())
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>#</th>
    <th>Campaign</th>
    <th>Scope</th>
    <th>Status</th>
    <th>Target</th>
    <th>Progress</th>
    <th>Pledgers</th>
    <th class="text-end">Actions</th>
</tr>
</thead>
<tbody>
@foreach($campaigns as $campaign)
<tr>
    <td>{{ $loop->iteration + ($campaigns->currentPage() - 1) * $campaigns->perPage() }}</td>
    <td>
        <a href="{{ route('pledge-campaigns.show', $campaign->id) }}" class="fw-semibold text-decoration-none">
            {{ $campaign->name }}
        </a>
    </td>
    <td>
        @if($campaign->scope === 'global')
            <span class="badge-pill badge-scope-global">Global</span>
        @else
            <span class="badge-pill badge-scope-church">{{ optional($campaign->church)->name ?? 'Church' }}</span>
        @endif
    </td>
    <td><span class="badge-pill badge-status-{{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span></td>
    <td>{{ $campaign->currency }} {{ number_format($campaign->target_amount) }}</td>
    <td style="min-width:140px">
        @php($pct = $campaign->progressPercent())
        <div class="pledge-progress mb-1"><div class="pledge-progress-bar" style="width: {{ $pct }}%"></div></div>
        <small class="text-muted">{{ $pct }}%</small>
    </td>
    <td>{{ $campaign->pledgersCount() }}</td>
    <td class="text-end">
        <div class="dropdown">
            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                <i class="icofont icofont-navigation-menu"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('pledge-campaigns.show', $campaign->id) }}"><i class="icofont icofont-eye"></i> View Dashboard</a></li>
                <li>
                    <a class="dropdown-item edit-campaign-btn" href="javascript:void(0)"
                        data-bs-toggle="modal" data-bs-target="#editCampaignModal"
                        data-id="{{ $campaign->id }}"
                        data-name="{{ $campaign->name }}"
                        data-description="{{ $campaign->description }}"
                        data-target="{{ $campaign->target_amount }}"
                        data-currency="{{ $campaign->currency }}"
                        data-start="{{ optional($campaign->start_date)->format('Y-m-d') }}"
                        data-end="{{ optional($campaign->end_date)->format('Y-m-d') }}"
                        data-status="{{ $campaign->status }}"
                        data-scope="{{ $campaign->scope }}"
                        data-church="{{ $campaign->church_id }}"
                        data-anonymous="{{ $campaign->allow_anonymous ? 1 : 0 }}"
                        data-live="{{ $campaign->live_enabled ? 1 : 0 }}"
                        data-notes="{{ $campaign->notes }}"
                        data-banner="{{ $campaign->banner_path ? asset('storage/' . $campaign->banner_path) : '' }}">
                        <i class="icofont icofont-edit"></i> Edit
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @if($campaign->status !== 'active')
                <li>
                    <form method="POST" action="{{ route('pledge-campaigns.status', [$campaign->id, 'active']) }}">
                        @csrf
                        <button class="dropdown-item text-success"><i class="icofont icofont-check-circled"></i> Activate</button>
                    </form>
                </li>
                @endif
                @if($campaign->status !== 'completed')
                <li>
                    <form method="POST" action="{{ route('pledge-campaigns.status', [$campaign->id, 'completed']) }}">
                        @csrf
                        <button class="dropdown-item"><i class="icofont icofont-medal"></i> Mark Completed</button>
                    </form>
                </li>
                @endif
                @if($campaign->status !== 'closed')
                <li>
                    <form method="POST" action="{{ route('pledge-campaigns.status', [$campaign->id, 'closed']) }}"
                        onsubmit="return confirm('Close this campaign? New pledges will no longer be accepted.')">
                        @csrf
                        <button class="dropdown-item text-danger"><i class="icofont icofont-close-circled"></i> Close</button>
                    </form>
                </li>
                @endif
            </ul>
        </div>
    </td>
</tr>
@endforeach
</tbody>
</table>

{{ $campaigns->links() }}
</div>
@else
<div class="pledge-empty">
    <i class="icofont icofont-bullseye"></i>
    <p class="mb-0">No campaigns found @if(request()->anyFilled(['q','status','scope'])) for the selected filters @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

{{-- ================= NEW CAMPAIGN MODAL ================= --}}
<div class="modal fade" id="newCampaignModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" action="{{ route('pledge-campaigns.store') }}" enctype="multipart/form-data">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-bullseye"></i> Create Pledge Campaign</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Campaign Details</p>
<div class="row">
    <div class="col-md-8">
        <label class="form-label">Campaign Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="TZS">
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Target Amount</label>
        <input type="number" step="0.01" min="0" name="target_amount" class="form-control" required>
    </div>
    <div class="col-md-3 mt-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control">
    </div>
    <div class="col-md-3 mt-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control">
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Campaign Image / Banner</label>
        <input type="file" name="banner" accept="image/*" class="form-control">
        <small class="text-muted">Shown on the campaign card and behind the Live Presentation screen.</small>
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Scope & Status</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-control" required>
            <option value="draft">Draft</option>
            <option value="active" selected>Active</option>
            <option value="completed">Completed</option>
            <option value="closed">Closed</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Campaign Scope</label>
        <select name="scope" id="new_scope" class="form-control" required>
            <option value="global">Global</option>
            <option value="church">Specific Church</option>
        </select>
    </div>
    <div class="col-md-4" id="new_church_wrap" style="display:none">
        <label class="form-label">Church / Branch</label>
        <select name="church_id" class="form-control">
            <option value="">-- Select Church --</option>
            @foreach($churches as $church)
                <option value="{{ $church->id }}">{{ strtoupper($church->name) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="allow_anonymous" value="1" id="new_anon" checked>
            <label class="form-check-label" for="new_anon">Allow Anonymous Pledges</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="live_enabled" value="1" id="new_live" checked>
            <label class="form-check-label" for="new_live">Enable Live Presentation</label>
        </div>
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">Create Campaign</button>
</div>

</form>
</div>
</div>
</div>

{{-- ================= EDIT CAMPAIGN MODAL ================= --}}
<div class="modal fade" id="editCampaignModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" id="editCampaignForm" action="" enctype="multipart/form-data">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-edit"></i> Edit Campaign</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Campaign Details</p>
<div class="row">
    <div class="col-md-8">
        <label class="form-label">Campaign Name</label>
        <input type="text" name="name" id="edit_name" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" id="edit_currency" class="form-control">
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Description</label>
        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Target Amount</label>
        <input type="number" step="0.01" min="0" name="target_amount" id="edit_target" class="form-control" required>
    </div>
    <div class="col-md-3 mt-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" id="edit_start" class="form-control">
    </div>
    <div class="col-md-3 mt-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" id="edit_end" class="form-control">
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Campaign Image / Banner</label>
        <div id="edit_banner_preview_wrap" class="mb-2" style="display:none">
            <img id="edit_banner_preview" src="" alt="Current banner" style="max-height:90px;border-radius:8px;">
        </div>
        <input type="file" name="banner" accept="image/*" class="form-control">
        <small class="text-muted">Leave empty to keep the current image.</small>
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Scope & Status</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" id="edit_status" class="form-control" required>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="closed">Closed</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Campaign Scope</label>
        <select name="scope" id="edit_scope" class="form-control" required>
            <option value="global">Global</option>
            <option value="church">Specific Church</option>
        </select>
    </div>
    <div class="col-md-4" id="edit_church_wrap">
        <label class="form-label">Church / Branch</label>
        <select name="church_id" id="edit_church" class="form-control">
            <option value="">-- Select Church --</option>
            @foreach($churches as $church)
                <option value="{{ $church->id }}">{{ strtoupper($church->name) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="allow_anonymous" value="1" id="edit_anon">
            <label class="form-check-label" for="edit_anon">Allow Anonymous Pledges</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="live_enabled" value="1" id="edit_live">
            <label class="form-check-label" for="edit_live">Enable Live Presentation</label>
        </div>
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
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

@endsection

@push('scripts')
<script>
function toggleChurchWrap(scopeSelectId, wrapId) {
    const scopeSelect = document.getElementById(scopeSelectId);
    const wrap = document.getElementById(wrapId);
    function update() { wrap.style.display = scopeSelect.value === 'church' ? '' : 'none'; }
    scopeSelect.addEventListener('change', update);
    update();
}
toggleChurchWrap('new_scope', 'new_church_wrap');
toggleChurchWrap('edit_scope', 'edit_church_wrap');

document.querySelectorAll('.edit-campaign-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        document.getElementById('editCampaignForm').action = `/v1/pledges/campaigns/${id}/update`;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_description').value = this.dataset.description || '';
        document.getElementById('edit_target').value = this.dataset.target;
        document.getElementById('edit_currency').value = this.dataset.currency;
        document.getElementById('edit_start').value = this.dataset.start || '';
        document.getElementById('edit_end').value = this.dataset.end || '';
        document.getElementById('edit_status').value = this.dataset.status;
        document.getElementById('edit_scope').value = this.dataset.scope;
        document.getElementById('edit_church').value = this.dataset.church || '';
        document.getElementById('edit_anon').checked = this.dataset.anonymous === '1';
        document.getElementById('edit_live').checked = this.dataset.live === '1';
        document.getElementById('edit_notes').value = this.dataset.notes || '';
        document.getElementById('edit_church_wrap').style.display = this.dataset.scope === 'church' ? '' : 'none';

        const previewWrap = document.getElementById('edit_banner_preview_wrap');
        const preview = document.getElementById('edit_banner_preview');
        if (this.dataset.banner) {
            preview.src = this.dataset.banner;
            previewWrap.style.display = '';
        } else {
            previewWrap.style.display = 'none';
        }
    });
});
</script>
@endpush
