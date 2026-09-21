@extends('layouts.admin.master')

@section('title', 'Manage Pledges')

@push('css')
@include('portal.pledges.partials.styles')
<style>
    .member-search-results {
        border: 1px solid #e2e6ee; border-radius: 8px; max-height: 220px; overflow-y: auto;
        margin-top: 4px; display: none; background: #fff; position: relative; z-index: 5;
    }
    .member-search-results .item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f2f7; }
    .member-search-results .item:last-child { border-bottom: none; }
    .member-search-results .item:hover { background: #f7f9fc; }
    .selected-member-chip {
        display: none; align-items: center; gap: 8px; background: #eef2ff; color: #4338ca;
        padding: 8px 12px; border-radius: 8px; margin-top: 8px; font-size: .85rem;
    }
    .member-search-results .item.add-new { color: #2e5aac; font-weight: 600; }
    .member-search-results .item.add-new:hover { background: #eef2ff; }
    .new-member-form { display: none; background: #f7f9fc; border-radius: 8px; padding: 14px; margin-top: 8px; }
    .already-pledged-note {
        display: none; background: #fff4e5; color: #92400e; border-radius: 8px;
        padding: 10px 14px; margin-top: 10px; font-size: .85rem;
    }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Manage Pledges</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('pledge-management.export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordOnBehalfModal">
                <i class="icofont icofont-plus-circle"></i> Record Pledge on Behalf
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Manage</li>
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
    <div class="col-xl-4 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-1"><i class="icofont icofont-listing-box"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $stats['total'] }}</p>
                    <p class="pledge-stat-label">Total Pledges</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6 mb-3 mb-xl-0">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-4"><i class="icofont icofont-badge"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ $stats['staff'] }}</p>
                    <p class="pledge-stat-label">Staff Recorded</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
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

<form method="GET" action="{{ route('pledge-management.index') }}" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-3">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Member, reference...">
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Campaign</label>
        <select name="campaign_id" class="form-control">
            <option value="">All Campaigns</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}" @selected(request('campaign_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All</option>
            @foreach(['pledged','partially_fulfilled','fulfilled','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label mb-1">Source</label>
        <select name="source" class="form-control">
            <option value="">All</option>
            <option value="member" @selected(request('source') == 'member')>Member</option>
            <option value="staff" @selected(request('source') == 'staff')>Staff</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i></button>
        @if(request()->anyFilled(['q','campaign_id','status','source']))
        <a href="{{ route('pledge-management.index') }}" class="btn btn-outline-secondary"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

@if($pledges->count())
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>Reference</th>
    <th>Member</th>
    <th>Church</th>
    <th>Campaign</th>
    <th class="text-end">Amount</th>
    <th class="text-end">Outstanding</th>
    <th>Status</th>
    <th>Source</th>
    <th class="text-end">Actions</th>
</tr>
</thead>
<tbody>
@foreach($pledges as $pledge)
<tr>
    <td class="fw-semibold">{{ $pledge->pledge_reference }}</td>
    <td>{{ optional($pledge->member)->first_name }} {{ optional($pledge->member)->last_name }}</td>
    <td>{{ optional(optional($pledge->member)->church)->name ?? '—' }}</td>
    <td>{{ optional($pledge->campaign)->name }}</td>
    <td class="text-end">{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->amount, 2) }}</td>
    <td class="text-end">{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->outstanding(), 2) }}</td>
    <td><span class="badge-pill badge-status-{{ $pledge->status }}">{{ ucfirst(str_replace('_',' ',$pledge->status)) }}</span></td>
    <td>
        <span class="badge-pill badge-source-{{ $pledge->source }}">{{ $pledge->source === 'staff' ? 'Staff Recorded' : 'Member' }}</span>
    </td>
    <td class="text-end">
        <a class="btn btn-sm btn-light" href="{{ route('my-pledges.pdf', $pledge->id) }}" title="Download PDF">
            <i class="icofont icofont-download-alt"></i>
        </a>
    </td>
</tr>
@endforeach
</tbody>
</table>

{{ $pledges->links() }}
</div>
@else
<div class="pledge-empty">
    <i class="icofont icofont-listing-box"></i>
    <p class="mb-0">No pledges found @if(request()->anyFilled(['q','campaign_id','status','source'])) for the selected filters @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

{{-- ================= RECORD PLEDGE ON BEHALF MODAL ================= --}}
<div class="modal fade" id="recordOnBehalfModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" action="{{ route('pledge-management.record') }}" id="recordOnBehalfForm">
@csrf
<input type="hidden" name="member_id" id="record_member_id" required>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Record Pledge on Behalf of Member</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Find Member</p>
<div class="position-relative">
    <input type="text" class="form-control" id="member_search_input" autocomplete="off"
        placeholder="Search by name, phone, or email...">
    <div class="member-search-results" id="member_search_results"></div>
</div>

{{-- Shown when the search comes back empty - record a brand-new person on
     the spot, same "new soul" concept as the Programs registration desk. --}}
<div class="new-member-form" id="new_member_form">
    <p class="modal-section-label mb-2">Not Found &mdash; Add New Person</p>
    <div class="row g-2">
        <div class="col-md-6">
            <input type="text" class="form-control form-control-sm" id="new_member_first_name" placeholder="First Name">
        </div>
        <div class="col-md-6">
            <input type="text" class="form-control form-control-sm" id="new_member_last_name" placeholder="Last Name">
        </div>
        <div class="col-md-6">
            <input type="text" class="form-control form-control-sm" id="new_member_phone" placeholder="Phone">
        </div>
        <div class="col-md-6">
            <select class="form-control form-control-sm" id="new_member_church_id">
                <option value="">-- Church --</option>
                @foreach($churches as $church)
                    <option value="{{ $church->id }}">{{ strtoupper($church->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <button type="button" class="btn btn-sm btn-primary w-100" id="new_member_submit">
                <i class="icofont icofont-plus"></i> Add &amp; Select
            </button>
            <p class="text-muted mt-1 mb-0" style="font-size:.76rem;">Not yet a church member? They'll be recorded as a new soul.</p>
        </div>
    </div>
</div>

<div class="selected-member-chip" id="selected_member_chip">
    <i class="icofont icofont-user-alt-3"></i>
    <span id="selected_member_name"></span>
    <span id="selected_member_new_soul_badge" style="display:none;font-size:.7rem;font-weight:600;background:#fff4e5;color:#92400e;padding:3px 10px;border-radius:20px;">New Soul</span>
    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="clear_selected_member">Change</button>
</div>

<div class="already-pledged-note" id="already_pledged_note"></div>

<hr class="my-3">
<p class="modal-section-label">Pledge Details</p>
<div class="row">
    <div class="col-md-6 mt-2">
        <label class="form-label">Campaign</label>
        <select name="campaign_id" class="form-control" id="record_campaign_select" required>
            <option value="">-- Select Campaign --</option>
            @foreach($campaigns->where('status','active') as $c)
                <option value="{{ $c->id }}" data-currency="{{ $c->currency }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mt-2">
        <label class="form-label">Pledge Amount</label>
        <input type="number" step="0.01" min="1" name="amount" class="form-control" required>
    </div>
    <div class="col-md-6 mt-2">
        <label class="form-label">Frequency</label>
        <select name="frequency" class="form-control" required>
            <option value="one_time">One Time</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="custom">Custom</option>
        </select>
    </div>
    <div class="col-md-12 mt-2">
        <label class="form-label">Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>
</div>
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="recordOnBehalfSubmit" disabled>Record Pledge</button>
</div>

</form>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('member_search_input');
    const results = document.getElementById('member_search_results');
    const newForm = document.getElementById('new_member_form');
    const newFirstName = document.getElementById('new_member_first_name');
    const newLastName = document.getElementById('new_member_last_name');
    const newPhone = document.getElementById('new_member_phone');
    const newChurch = document.getElementById('new_member_church_id');
    const newSubmit = document.getElementById('new_member_submit');
    const chip = document.getElementById('selected_member_chip');
    const nameEl = document.getElementById('selected_member_name');
    const newSoulBadge = document.getElementById('selected_member_new_soul_badge');
    const hiddenId = document.getElementById('record_member_id');
    const submitBtn = document.getElementById('recordOnBehalfSubmit');
    const clearBtn = document.getElementById('clear_selected_member');
    const campaignSelect = document.getElementById('record_campaign_select');
    const pledgedNote = document.getElementById('already_pledged_note');
    let debounceTimer = null;
    let selectedMemberPledges = [];

    function selectMember(m) {
        hiddenId.value = m.id;
        nameEl.textContent = m.name;
        newSoulBadge.style.display = m.is_new_soul ? 'inline-block' : 'none';
        chip.style.display = 'flex';
        input.style.display = 'none';
        newForm.style.display = 'none';
        results.style.display = 'none';
        submitBtn.disabled = false;
        selectedMemberPledges = m.pledges || [];
        checkAlreadyPledged();
    }

    function checkAlreadyPledged() {
        const campaignId = campaignSelect.value;
        const match = selectedMemberPledges.find(p => String(p.campaign_id) === String(campaignId));
        if (match) {
            pledgedNote.innerHTML = '<i class="icofont icofont-warning-alt"></i> This member has already pledged '
                + (match.currency || '') + ' ' + Number(match.amount).toLocaleString() + ' to <strong>' + match.campaign_name + '</strong>.';
            pledgedNote.style.display = 'block';
        } else {
            pledgedNote.style.display = 'none';
        }
    }

    campaignSelect.addEventListener('change', checkAlreadyPledged);

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        newForm.style.display = 'none';
        if (q.length < 2) { results.style.display = 'none'; return; }

        debounceTimer = setTimeout(function () {
            fetch(`{{ route('pledge-management.search-members') }}?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    results.innerHTML = '';
                    if (!data.length) {
                        const parts = q.split(/\s+/);
                        newFirstName.value = parts[0] || '';
                        newLastName.value = parts.slice(1).join(' ') || '';
                        const div = document.createElement('div');
                        div.className = 'item text-muted';
                        div.textContent = 'No members found';
                        results.appendChild(div);
                        const addDiv = document.createElement('div');
                        addDiv.className = 'item add-new';
                        addDiv.innerHTML = '<i class="icofont icofont-plus-circle"></i> Add "' + q + '" as a new person';
                        addDiv.addEventListener('click', function () {
                            results.style.display = 'none';
                            newForm.style.display = 'block';
                        });
                        results.appendChild(addDiv);
                    } else {
                        data.forEach(m => {
                            const div = document.createElement('div');
                            div.className = 'item';
                            div.innerHTML = `<strong>${m.name}</strong>${m.is_new_soul ? ' <span class="text-muted">(new soul)</span>' : ''}<br><small class="text-muted">${m.phone || ''} ${m.church ? '&middot; ' + m.church : ''}</small>`;
                            div.addEventListener('click', function () { selectMember(m); });
                            results.appendChild(div);
                        });
                    }
                    results.style.display = 'block';
                });
        }, 300);
    });

    newSubmit.addEventListener('click', function () {
        if (!newFirstName.value.trim() || !newPhone.value.trim() || !newChurch.value) {
            alert('First name, phone, and church are required to add a new person.');
            return;
        }
        newSubmit.disabled = true;
        fetch('{{ route('pledge-management.create-member') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                first_name: newFirstName.value.trim(),
                last_name: newLastName.value.trim(),
                phone: newPhone.value.trim(),
                church_id: newChurch.value,
            }),
        })
            .then(res => res.json())
            .then(m => {
                newSubmit.disabled = false;
                selectMember(m);
            })
            .catch(() => { newSubmit.disabled = false; });
    });

    clearBtn.addEventListener('click', function () {
        hiddenId.value = '';
        chip.style.display = 'none';
        input.style.display = '';
        input.value = '';
        newForm.style.display = 'none';
        pledgedNote.style.display = 'none';
        selectedMemberPledges = [];
        submitBtn.disabled = true;
    });

    document.getElementById('recordOnBehalfModal').addEventListener('hidden.bs.modal', function () {
        hiddenId.value = '';
        chip.style.display = 'none';
        input.style.display = '';
        input.value = '';
        results.style.display = 'none';
        newForm.style.display = 'none';
        pledgedNote.style.display = 'none';
        selectedMemberPledges = [];
        submitBtn.disabled = true;
    });
})();
</script>
@endpush
