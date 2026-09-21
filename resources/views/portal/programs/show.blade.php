@extends('layouts.admin.master')

@section('title', $program->name)

@push('css')
@include('portal.programs.partials.styles')
<style>
    .activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f2f7; font-size: .82rem; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #4d7de0; margin-top: 6px; flex-shrink: 0; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>{{ $program->name }}</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        @if(Route::has('program-attendance.capture'))
        <li>
            <a class="btn btn-outline-primary" href="{{ route('program-attendance.capture', $program->id) }}">
                <i class="icofont icofont-check-circled"></i> Take Attendance
            </a>
        </li>
        @endif
        <li>
            <a class="btn btn-primary" href="{{ route('programs.index') }}">
                <i class="icofont icofont-listing-box"></i> All Programs
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item"><a href="{{ route('programs.index') }}">Programs</a></li>
    <li class="breadcrumb-item active">{{ $program->name }}</li>
@endcomponent

<div class="container-fluid">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-12">
        <div class="card prog-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="badge-pill badge-status-{{ $program->status }}">{{ ucfirst($program->status) }}</span>
                    <span class="badge-pill badge-class-{{ $program->classification }}">{{ ucfirst($program->classification) }}</span>
                    <span class="badge-pill badge-access-{{ $program->access_type }}">{{ $program->access_type === 'free' ? 'FREE' : 'PAID' }}</span>
                    <p class="text-muted mb-0 mt-2">
                        <i class="icofont icofont-location-pin"></i> {{ $program->location ?? '—' }}
                        @if($program->classification === 'special')
                            &middot; <i class="icofont icofont-calendar"></i> {{ optional($program->start_date)->format('d M Y') }}
                        @else
                            &middot; <i class="icofont icofont-refresh"></i> {{ ucfirst($program->recurrence_frequency ?? '') }}
                            @if($program->recurrence_days) ({{ collect($program->recurrence_days)->map(fn($d)=>ucfirst($d))->implode(', ') }}) @endif
                        @endif
                        @if($program->access_type === 'paid')
                            &middot; {{ $program->currency }} {{ number_format($program->registration_fee) }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-1"><i class="icofont icofont-people"></i></div>
                <div><p class="prog-stat-value">{{ $attendanceStats['registered'] }}</p><p class="prog-stat-label">Registered</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-2"><i class="icofont icofont-check-circled"></i></div>
                <div><p class="prog-stat-value">{{ $attendanceStats['attended'] }}</p><p class="prog-stat-label">Attended</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-5"><i class="icofont icofont-close-circled"></i></div>
                <div><p class="prog-stat-value">{{ $attendanceStats['absent'] }}</p><p class="prog-stat-label">Absent</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-4"><i class="icofont icofont-bullseye"></i></div>
                <div><p class="prog-stat-value">{{ $attendanceStats['rate'] }}%</p><p class="prog-stat-label">Attendance Rate</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-3 mb-xl-0">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-3"><i class="icofont icofont-badge"></i></div>
                <div><p class="prog-stat-value">{{ $attendanceStats['new_souls'] }}</p><p class="prog-stat-label">New Souls</p></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6">
        <div class="card prog-stat-card">
            <div class="stat-body">
                <div class="prog-stat-icon bg-6"><i class="icofont icofont-money-bag"></i></div>
                <div><p class="prog-stat-value">{{ $program->currency }} {{ number_format($revenue) }}</p><p class="prog-stat-label">Revenue</p></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card prog-card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="programTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview">Overview</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-registrations">Registrations</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-occurrences">Occurrences</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">Activity</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="tab-overview">
                        @if($program->description)
                        <p>{{ $program->description }}</p>
                        @else
                        <p class="text-muted">No description provided.</p>
                        @endif
                        <table class="table prog-table align-middle">
                            <tr><td class="text-muted" width="220">Organizer</td><td>{{ $program->organizer ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Church</td><td>{{ optional($program->church)->name ?? ($program->scope === 'global' ? 'Global' : '—') }}</td></tr>
                            <tr><td class="text-muted">Department</td><td>{{ optional($program->department)->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Cell Group</td><td>{{ optional($program->cellGroup)->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">QR/Barcode Check-in</td><td>{{ $program->qrAvailable() ? 'Enabled' : 'Not enabled' }}</td></tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="tab-registrations">
                        @if($registrations->count())
                        <div class="table-responsive">
                        <table class="table prog-table align-middle">
                        <thead><tr><th>Reference</th><th>Member</th><th>Status</th><th>Payment</th><th>Registered</th></tr></thead>
                        <tbody>
                        @foreach($registrations as $reg)
                        <tr>
                            <td class="fw-semibold">{{ $reg->registration_reference }}</td>
                            <td>{{ optional($reg->member)->first_name }} {{ optional($reg->member)->last_name }}</td>
                            <td><span class="badge-pill badge-status-{{ $reg->registration_status }}">{{ ucfirst($reg->registration_status) }}</span></td>
                            <td><span class="badge-pill badge-payment-{{ $reg->payment_status }}">{{ ucfirst($reg->payment_status) }}</span></td>
                            <td>{{ optional($reg->registered_at)->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                        </div>
                        @else
                        <div class="prog-empty"><i class="icofont icofont-people"></i><p class="mb-0">No registrations yet.</p></div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="tab-occurrences">
                        @if($occurrences->count())
                        <div class="table-responsive">
                        <table class="table prog-table align-middle">
                        <thead><tr><th>Date</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                        @foreach($occurrences as $occ)
                        <tr>
                            <td>{{ $occ->occurrence_date->format('d M Y') }}</td>
                            <td><span class="badge-pill badge-status-{{ $occ->status === 'completed' ? 'completed' : 'active' }}">{{ ucfirst($occ->status) }}</span></td>
                            <td class="text-end">
                                @if(Route::has('program-attendance.capture'))
                                <a href="{{ route('program-attendance.capture', $program->id) }}?date={{ $occ->occurrence_date->format('Y-m-d') }}" class="btn btn-sm btn-light">
                                    <i class="icofont icofont-eye"></i> View Attendance
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                        </div>
                        @else
                        <div class="prog-empty"><i class="icofont icofont-calendar"></i><p class="mb-0">No occurrences recorded yet.</p></div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="tab-activity">
                        @forelse($recentActivity as $a)
                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div>
                                <span>{{ str_replace('_',' ',str_replace('program.','',$a->action)) }}</span>
                                by <strong>{{ optional($a->actor)->first_name ?? 'System' }}</strong>
                                <br><small class="text-muted">{{ $a->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No activity recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

@endsection
