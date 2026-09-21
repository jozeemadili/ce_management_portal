@extends('layouts.admin.master')

@section('title', 'Take Attendance - ' . $program->name)

@push('css')
@include('portal.programs.partials.styles')
<style>
    .att-member-row { transition: background .15s ease; }
    .att-member-row:hover { background: #f7f9fc; }
    .att-status-group .btn { border-radius: 20px !important; font-size: .74rem; padding: 4px 12px; }
    .att-status-group .btn.active-present { background:#e6f7ee; color:#0f9d58; border-color:#0f9d58; }
    .att-status-group .btn.active-absent { background:#fdecec; color:#d93025; border-color:#d93025; }
    .att-status-group .btn.active-late { background:#fff4e5; color:#b45309; border-color:#b45309; }
    .att-status-group .btn.active-excused { background:#eef2ff; color:#4338ca; border-color:#4338ca; }
    .att-summary-pill { font-size: .78rem; font-weight: 600; padding: 5px 14px; border-radius: 20px; background:#f0f3f9; color:#4b5563; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Take Attendance</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-primary" href="{{ route('programs.show', $program->id) }}">
                <i class="icofont icofont-arrow-left"></i> Back to Program
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item"><a href="{{ route('programs.index') }}">Programs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('programs.show', $program->id) }}">{{ $program->name }}</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endcomponent

<div class="container-fluid">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card prog-card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1">{{ $program->name }}</h5>
            <span class="att-summary-pill"><i class="icofont icofont-calendar"></i> {{ \Illuminate\Support\Carbon::parse($date)->format('d M Y (l)') }}</span>
            <span class="att-summary-pill ms-1">{{ $members->count() }} Eligible</span>
            <span class="att-summary-pill ms-1">{{ $existingAttendance->count() + $visitorAttendance->count() }} Recorded</span>
        </div>
        <form method="GET" action="{{ route('program-attendance.capture', $program->id) }}" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search member..." class="form-control">
            <button class="btn btn-outline-primary"><i class="icofont icofont-search"></i></button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('program-attendance.save', $program->id) }}" id="attendanceForm">
@csrf
<input type="hidden" name="date" value="{{ $date }}">

<div class="card prog-card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Members</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">Mark All Present</button>
                <button type="submit" class="btn btn-sm btn-primary"><i class="icofont icofont-save"></i> Save Attendance</button>
            </div>
        </div>

        @if($members->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>#</th><th>Member</th><th>Phone</th><th>Attendance</th></tr></thead>
        <tbody>
        @foreach($members as $i => $member)
        @php $current = $existingAttendance[$member->id] ?? null; @endphp
        <tr class="att-member-row">
            <td>{{ $i + 1 }}</td>
            <td class="fw-semibold">MEM-{{ str_pad($member->id, 4, '0', STR_PAD_LEFT) }} &middot; {{ $member->first_name }} {{ $member->last_name }}</td>
            <td>{{ $member->phone ?? '—' }}</td>
            <td>
                <div class="btn-group att-status-group member-status" data-member="{{ $member->id }}">
                    @foreach(['present'=>'Present','absent'=>'Absent','late'=>'Late','excused'=>'Excused'] as $val => $label)
                    <button type="button" class="btn btn-outline-secondary status-btn {{ $current === $val ? 'active-'.$val : '' }}" data-value="{{ $val }}">{{ $label }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="attendance[{{ $member->id }}]" class="status-input" value="{{ $current }}">
            </td>
        </tr>
        @endforeach
        </tbody>
        </table>
        </div>
        @else
        <div class="prog-empty"><i class="icofont icofont-people"></i><p class="mb-0">No eligible members found for this program's scope.</p></div>
        @endif
    </div>
</div>

</form>

<div class="card prog-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="icofont icofont-plus-circle"></i> Record a First-Time Visitor / New Soul</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addVisitorModal">
                <i class="icofont icofont-plus"></i> Add Visitor
            </button>
        </div>

        @if($visitorAttendance->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>#</th><th>Visitor</th><th>Phone</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($visitorAttendance as $i => $va)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-semibold">{{ optional($va->member)->first_name }} {{ optional($va->member)->last_name }}</td>
            <td>{{ optional($va->member)->phone ?? '—' }}</td>
            <td><span class="badge-pill badge-status-present">Present</span></td>
        </tr>
        @endforeach
        </tbody>
        </table>
        </div>
        @else
        <p class="text-muted mb-0">No visitors recorded for this date yet.</p>
        @endif
    </div>
</div>

</div>

{{-- Add Visitor Modal --}}
<div class="modal fade" id="addVisitorModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="{{ route('program-attendance.add-visitor', $program->id) }}">
@csrf
<input type="hidden" name="date" value="{{ $date }}">
<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Record a Visitor</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control">
        </div>
        <div class="col-md-6 mt-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-md-6 mt-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control">
                <option value="">-- Select --</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
        <div class="col-md-12 mt-3">
            <label class="form-label">Invited By</label>
            <input type="text" name="invited_by" class="form-control">
        </div>
        <div class="col-md-12 mt-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">Record Visitor</button>
</div>
</form>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.member-status .status-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var group = btn.closest('.member-status');
        var value = btn.getAttribute('data-value');
        group.querySelectorAll('.status-btn').forEach(function (b) {
            b.className = 'btn btn-outline-secondary status-btn';
        });
        btn.classList.add('active-' + value);
        group.querySelector('.status-input').value = value;
    });
});

function markAll(status) {
    document.querySelectorAll('.member-status').forEach(function (group) {
        group.querySelectorAll('.status-btn').forEach(function (b) {
            b.className = 'btn btn-outline-secondary status-btn';
            if (b.getAttribute('data-value') === status) {
                b.classList.add('active-' + status);
            }
        });
        group.querySelector('.status-input').value = status;
    });
}
</script>
@endpush
