@extends('layouts.admin.master')

@section('title', 'Service Report')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .rep-count { background: #f7f9fc; border-radius: 12px; padding: 14px; text-align: center; }
    .rep-count .n { font-size: 1.6rem; font-weight: 800; line-height: 1.1; }
    .rep-count .l { font-size: .72rem; color: #8a92a6; text-transform: uppercase; letter-spacing: .03em; }
    .rep-closed { background: #e6f7ee; color: #0f9d58; border-radius: 10px; padding: 10px 14px; }
    .rep-open { background: #fff4e5; color: #92400e; border-radius: 10px; padding: 10px 14px; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Service Report</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li><a class="btn btn-outline-primary" href="{{ route('services.dashboard', ['church' => $occurrence->church_id]) }}"><i class="icofont icofont-chart-bar-graph"></i> Dashboard</a></li>
    @endslot
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Service Report</li>
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

<div class="card prog-card mb-3">
<div class="card-body">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-0">{{ $occurrence->program->name }}</h4>
            <div class="text-muted">
                {{ strtoupper($occurrence->church->name) }} &middot; {{ $occurrence->occurrence_date->format('l d M Y') }}
                &middot; {{ substr($occurrence->start_time, 0, 5) }}&ndash;{{ substr($occurrence->end_time, 0, 5) }}
                @if($occurrence->program->is_training)<span class="badge-pill ms-1" style="background:#fff4e5;color:#b45309;">TRAINING</span>@endif
            </div>
            @if($occurrence->church->physical_location)<div class="text-muted" style="font-size:.85rem;"><i class="icofont icofont-location-pin"></i> {{ $occurrence->church->physical_location }}</div>@endif
        </div>
        <div>
            @if($occurrence->isReportClosed())
                <div class="rep-closed">
                    <i class="icofont icofont-lock"></i> Report closed {{ $occurrence->report_closed_at->format('d M Y H:i') }}
                    by {{ optional($occurrence->reportClosedBy)->first_name ?? '—' }}
                    <form method="POST" action="{{ route('services.report.reopen', $occurrence->id) }}" class="d-inline" onsubmit="return confirm('Reopen this report? The numbers will be live again until you close it.')">
                        @csrf <button class="btn btn-sm btn-link p-0 ms-2">Reopen</button>
                    </form>
                </div>
            @elseif(!$occurrence->isClosed())
                <div class="rep-open"><i class="icofont icofont-clock-time"></i> Service still open for check-in &mdash; close the report after it ends.</div>
            @else
                @php $unassigned = $inviteeList->filter(fn ($m) => $m->assignments->isEmpty())->count(); @endphp
                <form method="POST" action="{{ route('services.report.close', $occurrence->id) }}"
                      onsubmit="return confirm('{{ $unassigned ? $unassigned . ' new invitee(s) are not assigned to a church yet. ' : '' }}Close the report and freeze these numbers?')">
                    @csrf
                    <button class="btn btn-success"><i class="icofont icofont-lock"></i> Close report</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-2">
        @foreach([['Attended', $counts['attended'] ?? 0], ['On time', $counts['present'] ?? 0], ['Late', $counts['late'] ?? 0], ['Absent', $occurrence->isClosed() ? ($counts['absent'] ?? 0) : '—'], ['New souls', $counts['new_souls'] ?? 0]] as [$label, $value])
            <div class="col-6 col-md"><div class="rep-count"><div class="n">{{ $value }}</div><div class="l">{{ $label }}</div></div></div>
        @endforeach
    </div>
    @if($occurrence->isReportClosed() && !empty($counts['invitees_by_church']))
        <p class="text-muted mt-2 mb-0" style="font-size:.85rem;">
            New invitees by church:
            @foreach($counts['invitees_by_church'] as $name => $n) <strong>{{ $name }}</strong> {{ $n }}@if(!$loop->last), @endif @endforeach
        </p>
    @endif
</div>
</div>

<div class="card prog-card mb-3">
<div class="card-body">
    <p class="modal-section-label">New invitees ({{ $inviteeList->count() }})</p>
    <p class="text-muted" style="font-size:.85rem;">
        First-time visitors at this service. Assign each one to the church nearest where they live &mdash; that church follows
        them up under <a href="{{ route('invitees.index') }}">Church Setup &rarr; New Invitees</a>. Every assignment is kept in the history.
    </p>
    @if($inviteeList->isEmpty())
        <div class="prog-empty py-3"><i class="icofont icofont-heart-alt"></i>No new invitees at this service.</div>
    @else
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th>Name</th><th>Contact</th><th>Lives in</th><th>Invited by</th><th>Church (assign)</th></tr></thead>
        <tbody>
        @foreach($inviteeList as $invitee)
            <tr>
                <td>
                    <strong>{{ trim($invitee->first_name . ' ' . $invitee->last_name) }}</strong>
                    <div class="text-muted" style="font-size:.75rem;">{{ ucfirst($invitee->gender ?? '') }}{{ $invitee->member_type === 'member' ? ' · now a member' : '' }}</div>
                </td>
                <td style="font-size:.85rem;">{{ $invitee->phone ?: '—' }}<br>{{ $invitee->email ?: '' }}</td>
                <td>{{ $invitee->location ?: '—' }}</td>
                <td>{{ optional($invitee->invitedByMember)->first_name ? trim($invitee->invitedByMember->first_name . ' ' . $invitee->invitedByMember->last_name) : ($invitee->invited_by ?: '—') }}</td>
                <td>@include('portal.churches.invitees.partials.assign', ['invitee' => $invitee, 'occurrence' => $occurrence])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
</div>

<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Check-ins ({{ $checkIns->count() }})</p>
    @if($checkIns->isEmpty())
        <div class="prog-empty py-3"><i class="icofont icofont-users-alt-4"></i>Nobody checked in.</div>
    @else
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th>Time</th><th>Name</th><th>Status</th><th>Program &amp; date</th><th>Lives in</th><th>Phone</th><th>Email</th><th>Method</th></tr></thead>
        <tbody>
        @foreach($checkIns as $a)
            <tr>
                <td>{{ optional($a->checked_in_at)->format('H:i') }}</td>
                <td>{{ trim(optional($a->member)->first_name . ' ' . optional($a->member)->last_name) }}
                    @if(optional($a->member)->member_type === 'new_soul')<span class="badge-pill badge-status-new ms-1">New soul</span>@endif</td>
                <td><span class="badge-pill badge-status-{{ $a->attendance_status }}">{{ ucfirst($a->attendance_status) }}</span></td>
                <td>{{ $occurrence->program->name }}, {{ $occurrence->occurrence_date->format('d M Y') }}</td>
                <td>{{ optional($a->member)->location ?: '—' }}</td>
                <td>{{ optional($a->member)->phone ?: '—' }}</td>
                <td>{{ optional($a->member)->email ?: '—' }}</td>
                <td>{{ $a->check_in_method === 'qr' ? 'QR' : 'Manual' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
</div>

</div>
@endsection
