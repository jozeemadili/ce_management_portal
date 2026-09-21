@extends('layouts.admin.master')

@section('title', 'Contributions / Fulfillment')

@push('css')
@include('portal.pledges.partials.styles')
<style>
    .pledge-search-results {
        border: 1px solid #e2e6ee; border-radius: 8px; max-height: 220px; overflow-y: auto;
        margin-top: 4px; display: none; background: #fff; position: relative; z-index: 5;
    }
    .pledge-search-results .item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f2f7; }
    .pledge-search-results .item:last-child { border-bottom: none; }
    .pledge-search-results .item:hover { background: #f7f9fc; }
    .selected-pledge-chip {
        display: none; align-items: center; gap: 8px; background: #eef2ff; color: #4338ca;
        padding: 8px 12px; border-radius: 8px; margin-top: 8px; font-size: .85rem;
    }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Contributions / Fulfillment</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('pledge-contributions.export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordContributionModal">
                <i class="icofont icofont-plus-circle"></i> Record Contribution
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">Contributions</li>
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
    <div class="col-md-4">
        <div class="card pledge-stat-card">
            <div class="stat-body">
                <div class="pledge-stat-icon bg-2"><i class="icofont icofont-money"></i></div>
                <div>
                    <p class="pledge-stat-value">{{ number_format($total) }}</p>
                    <p class="pledge-stat-label">Total Contributions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card pledge-card">
<div class="card-body">

<form method="GET" action="{{ route('pledge-contributions.index') }}" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-8">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Pledge reference, member, or payment reference">
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i></button>
        @if(request()->filled('q'))
        <a href="{{ route('pledge-contributions.index') }}" class="btn btn-outline-secondary"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

@if($contributions->count())
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>Pledge Ref</th>
    <th>Member</th>
    <th>Church</th>
    <th>Campaign</th>
    <th class="text-end">Amount</th>
    <th>Date</th>
    <th>Method</th>
    <th>Recorded By</th>
</tr>
</thead>
<tbody>
@foreach($contributions as $c)
<tr>
    <td class="fw-semibold">{{ optional($c->pledge)->pledge_reference }}</td>
    <td>{{ optional(optional($c->pledge)->member)->first_name }} {{ optional(optional($c->pledge)->member)->last_name }}</td>
    <td>{{ optional(optional(optional($c->pledge)->member)->church)->name ?? '—' }}</td>
    <td>{{ optional(optional($c->pledge)->campaign)->name }}</td>
    <td class="text-end">{{ optional(optional($c->pledge)->campaign)->currency }} {{ number_format($c->amount, 2) }}</td>
    <td>{{ optional($c->payment_date)->format('d M Y') }}</td>
    <td>{{ $c->payment_method ?? '—' }}</td>
    <td>{{ optional($c->recorder)->first_name ?? '—' }}</td>
</tr>
@endforeach
</tbody>
</table>

{{ $contributions->links() }}
</div>
@else
<div class="pledge-empty">
    <i class="icofont icofont-money"></i>
    <p class="mb-0">No contributions recorded yet @if(request()->filled('q')) for this search @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

{{-- ================= RECORD CONTRIBUTION MODAL ================= --}}
<div class="modal fade" id="recordContributionModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="{{ route('pledge-contributions.store') }}">
@csrf
<input type="hidden" name="pledge_id" id="contrib_pledge_id" required>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Record Contribution</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Find Pledge</p>
<div class="position-relative">
    <input type="text" class="form-control" id="pledge_search_input" autocomplete="off"
        placeholder="Search by pledge reference or member name...">
    <div class="pledge-search-results" id="pledge_search_results"></div>
</div>
<div class="selected-pledge-chip" id="selected_pledge_chip">
    <i class="icofont icofont-listing-box"></i>
    <span id="selected_pledge_label"></span>
    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="clear_selected_pledge">Change</button>
</div>

<hr class="my-3">
<p class="modal-section-label">Contribution Details</p>

<label class="form-label">Amount</label>
<input type="number" step="0.01" min="1" name="amount" class="form-control" required>

<label class="form-label mt-3">Payment Date</label>
<input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>

<div class="row">
    <div class="col-md-6 mt-3">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-control">
            <option value="">-- Select --</option>
            @foreach($paymentMethods as $m)
                <option value="{{ $m->name }}">{{ $m->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Payment Reference</label>
        <input type="text" name="payment_reference" class="form-control">
    </div>
</div>

<label class="form-label mt-3">Notes (optional)</label>
<textarea name="notes" class="form-control" rows="2"></textarea>
</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="recordContributionSubmit" disabled>Record Contribution</button>
</div>

</form>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('pledge_search_input');
    const results = document.getElementById('pledge_search_results');
    const chip = document.getElementById('selected_pledge_chip');
    const labelEl = document.getElementById('selected_pledge_label');
    const hiddenId = document.getElementById('contrib_pledge_id');
    const submitBtn = document.getElementById('recordContributionSubmit');
    const clearBtn = document.getElementById('clear_selected_pledge');
    let debounceTimer = null;

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (q.length < 2) { results.style.display = 'none'; return; }

        debounceTimer = setTimeout(function () {
            fetch(`{{ route('pledge-contributions.search-pledges') }}?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    results.innerHTML = '';
                    if (!data.length) {
                        results.innerHTML = '<div class="item text-muted">No open pledges found</div>';
                    } else {
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'item';
                            div.innerHTML = `<strong>${p.reference}</strong> &middot; ${p.member}<br><small class="text-muted">${p.campaign} &middot; Outstanding: ${p.currency} ${Number(p.outstanding).toLocaleString()}</small>`;
                            div.addEventListener('click', function () {
                                hiddenId.value = p.id;
                                labelEl.textContent = `${p.reference} - ${p.member}`;
                                chip.style.display = 'flex';
                                input.style.display = 'none';
                                results.style.display = 'none';
                                submitBtn.disabled = false;
                            });
                            results.appendChild(div);
                        });
                    }
                    results.style.display = 'block';
                });
        }, 300);
    });

    clearBtn.addEventListener('click', function () {
        hiddenId.value = '';
        chip.style.display = 'none';
        input.style.display = '';
        input.value = '';
        submitBtn.disabled = true;
    });

    document.getElementById('recordContributionModal').addEventListener('hidden.bs.modal', function () {
        hiddenId.value = '';
        chip.style.display = 'none';
        input.style.display = '';
        input.value = '';
        results.style.display = 'none';
        submitBtn.disabled = true;
    });
})();
</script>
@endpush
