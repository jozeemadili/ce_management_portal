@extends('layouts.admin.master')

@section('title', 'Attendance Report')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Attendance Report</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-success" href="{{ route('program-reports.attendance.export', request()->query()) }}">
                <i class="icofont icofont-download"></i> Export Excel
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item"><a href="{{ route('program-reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endcomponent

<div class="container-fluid">

<div class="card prog-card mb-3">
    <div class="card-body">
        <form method="GET" class="prog-filter-bar row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Program</label>
                <select name="program_id" class="form-control" id="report_program_select">
                    <option value="">-- All --</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" @selected(request('program_id')==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All --</option>
                    @foreach(['present'=>'Present','absent'=>'Absent','late'=>'Late','excused'=>'Excused'] as $val=>$label)
                        <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="icofont icofont-search"></i> Filter</button>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="downloadPdfForProgram()" title="Printable PDF for selected program (most recent date)">
                    <i class="icofont icofont-file-pdf"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card prog-card">
    <div class="card-body">
        @if($attendances->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Program</th><th>Date</th><th>Attendee</th><th>Type</th><th>Church</th><th>Status</th><th>Method</th></tr></thead>
        <tbody>
        @foreach($attendances as $att)
        <tr>
            <td>{{ optional($att->program)->name }}</td>
            <td>{{ optional(optional($att->occurrence)->occurrence_date)->format('d M Y') }}</td>
            <td class="fw-semibold">{{ $att->attendeeName() }}</td>
            <td>{{ $att->isNewSoul() ? 'Visitor' : 'Member' }}</td>
            <td>{{ optional(optional($att->member)->church)->name ?? '—' }}</td>
            <td><span class="badge-pill badge-status-{{ $att->attendance_status }}">{{ ucfirst($att->attendance_status) }}</span></td>
            <td><span class="badge-pill badge-method-{{ $att->check_in_method }}">{{ $att->check_in_method === 'qr' ? 'QR Scan' : 'Manual' }}</span></td>
        </tr>
        @endforeach
        </tbody>
        </table>
        </div>
        {{ $attendances->links() }}
        @else
        <div class="prog-empty"><i class="icofont icofont-check-circled"></i><p class="mb-0">No attendance records match these filters.</p></div>
        @endif
    </div>
</div>

</div>

@endsection

@push('scripts')
<script>
function downloadPdfForProgram() {
    var select = document.getElementById('report_program_select');
    if (!select.value) {
        alert('Select a program first to download its printable attendance PDF.');
        return;
    }
    var url = '{{ route('program-reports.pdf-attendance', ['program' => '__ID__']) }}'.replace('__ID__', select.value);
    window.location.href = url;
}
</script>
@endpush
