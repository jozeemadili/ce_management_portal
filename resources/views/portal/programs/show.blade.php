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
        @if($program->access_type === 'paid')
        <li>
            <a class="btn btn-outline-warning" href="{{ route('program-payments.index') }}">
                <i class="icofont icofont-money"></i> Payments to Confirm
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
                            @if($program->end_date && !$program->end_date->equalTo($program->start_date)) &ndash; {{ $program->end_date->format('d M Y') }} @endif
                        @else
                            &middot; <i class="icofont icofont-refresh"></i> {{ ucfirst($program->recurrence_frequency ?? '') }}
                            @if($program->recurrence_days) ({{ collect($program->recurrence_days)->map(fn($d)=>ucfirst($d))->implode(', ') }}) @endif
                        @endif
                        @if($program->access_type === 'paid')
                            &middot; {{ $program->accessLabel() }}
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
                <div>
                    <p class="prog-stat-value">{{ $program->currency }} {{ number_format($revenue) }}</p>
                    <p class="prog-stat-label">Collected @if($outstanding > 0)&middot; {{ number_format($outstanding) }} due @endif</p>
                </div>
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
                            <tr><td class="text-muted">QR/Barcode Check-in</td><td>{{ $program->qrAvailable() ? 'Enabled (per session)' : 'Not enabled' }}</td></tr>
                            <tr>
                                <td class="text-muted">Sessions (each day)</td>
                                <td>
                                    @forelse($program->sessions as $session)
                                        <div><strong>{{ $session->name }}</strong> &middot; {{ $session->timeRange() }}</div>
                                    @empty
                                        —
                                    @endforelse
                                </td>
                            </tr>
                            @if($program->access_type === 'paid')
                            <tr>
                                <td class="text-muted">Price per Group</td>
                                <td>
                                    @foreach($program->designationPrices->sortBy('designation_id') as $price)
                                        <div>{{ ucwords(optional($price->designation)->name) }}: <strong>{{ $program->currency }} {{ number_format($price->amount) }}</strong></div>
                                    @endforeach
                                    <small class="text-muted">Most senior group applies; no group (e.g. first-time visitors) = free.</small>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    <div class="tab-pane fade" id="tab-registrations">
                        @if($registrations->count())
                        <div class="table-responsive">
                        <table class="table prog-table align-middle">
                        <thead><tr><th>Reference</th><th>Member</th><th>Status</th><th>Payment</th>@if($program->access_type === 'paid')<th class="text-end">Due</th><th class="text-end">Paid</th><th class="text-end">Balance</th>@endif<th>Registered</th>@if($program->access_type === 'paid')<th></th>@endif</tr></thead>
                        <tbody>
                        @foreach($registrations as $reg)
                        <tr>
                            <td class="fw-semibold">{{ $reg->registration_reference }}</td>
                            <td>{{ optional($reg->member)->first_name }} {{ optional($reg->member)->last_name }}</td>
                            <td><span class="badge-pill badge-status-{{ $reg->registration_status }}">{{ ucfirst($reg->registration_status) }}</span></td>
                            <td><span class="badge-pill badge-payment-{{ $reg->payment_status }}">{{ $reg->paymentLabel() }}</span></td>
                            @if($program->access_type === 'paid')
                            <td class="text-end">
                                {{ number_format($reg->amount_due ?? 0) }}
                                @if($reg->pricedDesignation)<div class="text-muted small">{{ ucwords($reg->pricedDesignation->name) }}</div>@endif
                            </td>
                            <td class="text-end">{{ number_format($reg->totalPaid()) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($reg->balance()) }}</td>
                            @endif
                            <td>{{ optional($reg->registered_at)->format('d M Y') }}</td>
                            @if($program->access_type === 'paid')
                            <td class="text-end">
                                @if($reg->pendingPaymentsTotal() > 0)
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $reg->id }}">
                                    <i class="icofont icofont-eye"></i> Review Proof
                                </button>
                                @elseif(!$reg->isSettled() && $reg->registration_status === 'registered')
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $reg->id }}">
                                    <i class="icofont icofont-money"></i> Record Payment
                                </button>
                                @elseif($reg->payments->count())
                                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $reg->id }}">
                                    <i class="icofont icofont-listing-box"></i> Payments
                                </button>
                                @endif
                            </td>
                            @endif
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

{{-- Payment modals (paid programs): record a payment, review proofs --}}
@if($program->access_type === 'paid')
@foreach($registrations as $reg)
@if($reg->payments->count() || (!$reg->isSettled() && $reg->registration_status === 'registered'))
<div class="modal fade" id="paymentModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content">
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="icofont icofont-money"></i> Payments &middot; {{ $reg->registration_reference }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <p class="mb-3">
            <strong>{{ optional($reg->member)->first_name }} {{ optional($reg->member)->last_name }}</strong>
            @if($reg->pricedDesignation) &middot; {{ ucwords($reg->pricedDesignation->name) }} @endif
            <br>
            <span class="text-muted">Due {{ $program->currency }} {{ number_format($reg->amount_due) }}
                &middot; confirmed {{ number_format($reg->totalPaid()) }}
                @if($reg->pendingPaymentsTotal() > 0) &middot; awaiting {{ number_format($reg->pendingPaymentsTotal()) }} @endif
                &middot; balance <strong>{{ number_format($reg->balance()) }}</strong></span>
        </p>

        @include('portal.programs.partials.payment-list', [
            'payments' => $reg->payments,
            'currency' => $program->currency,
            'canReview' => true,
        ])

        @if($reg->registration_status === 'registered' && $reg->payableAmount() > 0)
        <hr>
        <p class="modal-section-label mb-2">Record a Payment</p>
        @include('portal.programs.partials.payment-form', [
            'action' => route('program-payments.store', $reg->id),
            'maxAmount' => $reg->payableAmount(),
            'currency' => $program->currency,
            'paymentMethods' => $paymentMethods,
            'proofRequired' => false,
            'idPrefix' => 'pay' . $reg->id,
            'submitLabel' => 'Record Payment',
        ])
        @endif
    </div>
</div>
</div>
</div>
@endif
@endforeach
@endif

@endsection
