@extends('layouts.admin.master')

@section('title', 'Programs')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Programs</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('programs.export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
        <li>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProgramModal">
                New Program <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    @endslot

    <li class="breadcrumb-item">Programs &amp; Attendance</li>
    <li class="breadcrumb-item active">Programs</li>
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
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-calendar"></i></div>
                <div><p class="prog-stat-value">{{ $stats['total'] }}</p><p class="prog-stat-label">Total Programs</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value">{{ $stats['active'] }}</p><p class="prog-stat-label">Active</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-refresh"></i></div>
                <div><p class="prog-stat-value">{{ $stats['recurring'] }}</p><p class="prog-stat-label">Recurring</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-ticket"></i></div>
                <div><p class="prog-stat-value">{{ $stats['special'] }}</p><p class="prog-stat-label">Special Events</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
<div class="card prog-card">
<div class="card-body">

<form method="GET" action="{{ route('programs.index') }}" class="prog-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-3">
        <label class="form-label mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Program name">
    </div>
    <div class="col-md-3">
        <label class="form-label mb-1">Classification</label>
        <select name="classification" class="form-control">
            <option value="">All</option>
            <option value="recurring" @selected(request('classification')=='recurring')>Recurring</option>
            <option value="special" @selected(request('classification')=='special')>Special</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All</option>
            @foreach(['draft','active','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label mb-1">Access</label>
        <select name="access_type" class="form-control">
            <option value="">All</option>
            <option value="free" @selected(request('access_type')=='free')>Free</option>
            <option value="paid" @selected(request('access_type')=='paid')>Paid</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i></button>
        @if(request()->anyFilled(['q','classification','status','access_type']))
        <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary"><i class="icofont icofont-refresh"></i></a>
        @endif
    </div>
</div>
</form>

@if($programs->count())
<div class="row">
@foreach($programs as $program)
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card program-card">
        <div class="program-card-banner" @if($program->banner_path) style="background-image:url('{{ asset('storage/'.$program->banner_path) }}');background-size:cover;background-position:center;" @endif>
            @unless($program->banner_path)
                <i class="icofont icofont-{{ $program->classification === 'recurring' ? 'refresh' : 'ticket' }}"></i>
            @endunless
        </div>
        <div class="program-card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="program-card-title">{{ $program->name }}</h5>
                <span class="badge-pill badge-access-{{ $program->access_type }}">{{ $program->access_type === 'free' ? 'FREE' : 'PAID' }}</span>
            </div>
            <div class="d-flex gap-2">
                <span class="badge-pill badge-class-{{ $program->classification }}">{{ ucfirst($program->classification) }}</span>
                <span class="badge-pill badge-status-{{ $program->status }}">{{ ucfirst($program->status) }}</span>
            </div>
            <p class="program-card-desc">{{ \Illuminate\Support\Str::limit($program->description ?? '', 80) ?: 'No description provided.' }}</p>

            <div class="program-card-meta">
                <span><i class="icofont icofont-location-pin"></i> {{ $program->location ?? '—' }}</span>
                <span><i class="icofont icofont-people"></i> {{ $program->registrations_count ?? 0 }} reg.</span>
            </div>
            @if($program->scope === 'church')
            <div class="program-card-meta">
                <span><i class="icofont icofont-building-alt"></i> {{ \Illuminate\Support\Str::limit($program->churchNames(), 70) }}</span>
            </div>
            @endif
            @if($program->classification === 'recurring')
            <div class="program-card-meta">
                <span><i class="icofont icofont-refresh"></i> {{ ucfirst($program->recurrence_frequency ?? '—') }}@if($program->recurrence_days) &middot; {{ collect($program->recurrence_days)->map(fn($d)=>ucfirst($d))->implode(', ') }} @endif</span>
            </div>
            @else
            <div class="program-card-meta">
                <span><i class="icofont icofont-calendar"></i> {{ optional($program->start_date)->format('d M Y') }}</span>
            </div>
            <div class="program-card-meta">
                <span><i class="icofont icofont-clock-time"></i> {{ $program->sessions->count() }} {{ \Illuminate\Support\Str::plural('session', $program->sessions->count()) }}@if($program->sessions->isNotEmpty()): {{ $program->sessions->map(fn ($x) => $x->timeRange())->implode(', ') }}@endif</span>
                @if($program->access_type === 'paid')
                <span>{{ $program->accessLabel() }}</span>
                @endif
            </div>
            @endif

            <div class="d-flex gap-2 mt-2">
                <a href="{{ route('programs.show', $program->id) }}" class="btn btn-primary btn-sm flex-fill">
                    <i class="icofont icofont-eye"></i> View
                </a>
                <button class="btn btn-outline-secondary btn-sm edit-program-btn"
                    data-bs-toggle="modal" data-bs-target="#editProgramModal"
                    data-id="{{ $program->id }}"
                    data-name="{{ $program->name }}"
                    data-description="{{ $program->description }}"
                    data-category="{{ $program->category }}"
                    data-classification="{{ $program->classification }}"
                    data-scope="{{ $program->scope }}"
                    data-church="{{ $program->church_id }}"
                    data-churches='@json($program->churchIds())'
                    data-department="{{ $program->department_id }}"
                    data-cell="{{ $program->cell_group_id }}"
                    data-organizer="{{ $program->organizer }}"
                    data-location="{{ $program->location }}"
                    data-contact-phone="{{ $program->contact_phone }}"
                    data-start-date="{{ optional($program->start_date)->format('Y-m-d') }}"
                    data-end-date="{{ optional($program->end_date)->format('Y-m-d') }}"
                    data-sessions="{{ json_encode($program->sessionsForForm()) }}"
                    data-prices="{{ json_encode($program->designationPrices->pluck('amount', 'designation_id')) }}"
                    data-frequency="{{ $program->recurrence_frequency }}"
                    data-days='@json($program->recurrence_days ?? [])'
                    data-access="{{ $program->access_type }}"
                    data-currency="{{ $program->currency }}"
                    data-status="{{ $program->status }}"
                    data-qr="{{ $program->qr_enabled ? 1 : 0 }}"
                    data-banner="{{ $program->banner_path ? asset('storage/'.$program->banner_path) : '' }}"
                    title="Edit">
                    <i class="icofont icofont-edit"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>

{{ $programs->links() }}
@else
<div class="prog-empty">
    <i class="icofont icofont-calendar"></i>
    <p class="mb-0">No programs found @if(request()->anyFilled(['q','classification','status','access_type'])) for the selected filters @endif.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

@include('portal.programs.partials.program-form-modal', ['modalId' => 'newProgramModal', 'formAction' => route('programs.store'), 'mode' => 'new'])
@include('portal.programs.partials.program-form-modal', ['modalId' => 'editProgramModal', 'formAction' => '', 'mode' => 'edit'])

@endsection

@push('scripts')
<script>
/* Sessions editor for the Create/Edit Program forms. */
const programSessions = (function () {
    const counters = {};

    function add(mode, data) {
        data = data || {};
        const container = document.getElementById(mode + '_sessions');
        const i = counters[mode] = (counters[mode] || 0) + 1;
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 program-session-row';
        row.innerHTML =
            '<input type="hidden" name="sessions[' + i + '][id]">' +
            '<div class="col-md-5"><label class="form-label small mb-1">Session name</label>' +
            '<input type="text" name="sessions[' + i + '][name]" class="form-control" placeholder="e.g. Morning Session" required></div>' +
            '<div class="col-md-3"><label class="form-label small mb-1">Starts</label>' +
            '<input type="time" name="sessions[' + i + '][start_time]" class="form-control" required></div>' +
            '<div class="col-md-3"><label class="form-label small mb-1">Ends</label>' +
            '<input type="time" name="sessions[' + i + '][end_time]" class="form-control" required></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-light w-100" aria-label="Remove session" title="Remove session">' +
            '<i class="icofont icofont-trash"></i></button></div>';
        row.querySelector('input[name$="[id]"]').value = data.id || '';
        row.querySelector('input[name$="[name]"]').value = data.name || '';
        row.querySelector('input[name$="[start_time]"]').value = data.start_time || '';
        row.querySelector('input[name$="[end_time]"]').value = data.end_time || '';
        row.querySelector('button').addEventListener('click', function () {
            if (container.querySelectorAll('.program-session-row').length > 1) {
                row.remove();
            } else {
                alert('A program needs at least one session.');
            }
        });
        container.appendChild(row);
    }

    function fill(mode, sessions) {
        document.getElementById(mode + '_sessions').innerHTML = '';
        (sessions.length ? sessions : [{ name: 'Main Session' }]).forEach(s => add(mode, s));
    }

    document.querySelectorAll('[data-add-session]').forEach(btn => {
        btn.addEventListener('click', () => add(btn.dataset.addSession));
    });

    fill('new', [{ name: 'Morning Session' }]);

    return { add, fill };
})();

// Church tickboxes: search filter + "N selected" counter.
function updateChurchCounts() {
    document.querySelectorAll('.church-pick-count').forEach(el => {
        el.textContent = document.querySelectorAll('#' + el.dataset.target + ' input:checked').length;
    });
}
document.addEventListener('change', e => { if (e.target.matches('.church-pick input')) updateChurchCounts(); });
document.querySelectorAll('.church-pick-search').forEach(input => {
    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        document.querySelectorAll('#' + input.dataset.target + ' .church-pick-item').forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});

function progToggle(triggerId, wrapId, testFn) {
    const trigger = document.getElementById(triggerId);
    const wrap = document.getElementById(wrapId);
    function update() { wrap.style.display = testFn(trigger.value) ? '' : 'none'; }
    trigger.addEventListener('change', update);
    update();
}

['new', 'edit'].forEach(prefix => {
    progToggle(`${prefix}_classification`, `${prefix}_recurring_wrap`, v => v === 'recurring');
    progToggle(`${prefix}_classification`, `${prefix}_special_wrap`, v => v === 'special');
    progToggle(`${prefix}_frequency`, `${prefix}_days_wrap`, v => v === 'weekly');
    progToggle(`${prefix}_access`, `${prefix}_fee_wrap`, v => v === 'paid');
    progToggle(`${prefix}_access`, `${prefix}_currency_wrap2`, v => v === 'paid');
    progToggle(`${prefix}_scope`, `${prefix}_church_wrap`, v => v === 'church');
    progToggle(`${prefix}_scope`, `${prefix}_department_wrap`, v => v === 'department');
    progToggle(`${prefix}_scope`, `${prefix}_cell_wrap`, v => v === 'cell');
});

document.querySelectorAll('.edit-program-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        document.getElementById('editProgramForm').action = `/v1/programs/${id}/update`;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_description').value = this.dataset.description || '';
        document.getElementById('edit_category').value = this.dataset.category;
        document.getElementById('edit_classification').value = this.dataset.classification;
        document.getElementById('edit_scope').value = this.dataset.scope;
        const picked = JSON.parse(this.dataset.churches || '[]').map(String);
        document.querySelectorAll('#edit_churches input[type=checkbox]').forEach(cb => { cb.checked = picked.includes(cb.value); });
        updateChurchCounts();
        document.getElementById('edit_department').value = this.dataset.department || '';
        document.getElementById('edit_cell').value = this.dataset.cell || '';
        document.getElementById('edit_organizer').value = this.dataset.organizer || '';
        document.getElementById('edit_location').value = this.dataset.location || '';
        document.getElementById('edit_contact_phone').value = this.dataset.contactPhone || '';
        document.getElementById('edit_start_date').value = this.dataset.startDate || '';
        document.getElementById('edit_end_date').value = this.dataset.endDate || '';
        programSessions.fill('edit', JSON.parse(this.dataset.sessions || '[]'));
        const prices = JSON.parse(this.dataset.prices || '{}');
        document.querySelectorAll('.edit-price-input').forEach(input => {
            const amount = prices[input.dataset.designation];
            input.value = amount === undefined ? '' : Number(amount);
        });
        document.getElementById('edit_frequency').value = this.dataset.frequency || '';
        document.getElementById('edit_access').value = this.dataset.access;
        document.getElementById('edit_currency').value = this.dataset.currency;
        document.getElementById('edit_status').value = this.dataset.status;
        document.getElementById('edit_qr').checked = this.dataset.qr === '1';

        let days = [];
        try { days = JSON.parse(this.dataset.days || '[]'); } catch (e) {}
        document.querySelectorAll('.edit-day-check').forEach(cb => { cb.checked = days.includes(cb.value); });

        // Trigger visibility toggles
        ['classification', 'frequency', 'access', 'scope'].forEach(f => {
            document.getElementById(`edit_${f}`).dispatchEvent(new Event('change'));
        });

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
