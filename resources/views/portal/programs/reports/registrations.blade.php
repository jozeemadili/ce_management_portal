@extends('layouts.admin.master')

@section('title', 'Registration Report')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .badge-type-new_soul { background: #fff4e0; color: #8a5300; }
    .badge-type-member { background: #e6effd; color: #1f4a94; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Registration Report</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-success" href="{{ route('program-reports.registrations.export', request()->query()) }}">
                <i class="icofont icofont-download"></i> Export Excel
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item"><a href="{{ route('program-reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Registrations</li>
@endcomponent

<div class="container-fluid">

<div class="card prog-card mb-3">
    <div class="card-body">
        <form method="GET" class="prog-filter-bar row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Program</label>
                <select name="program_id" class="form-control">
                    <option value="">-- All --</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" @selected(request('program_id')==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Attendee Type</label>
                <select name="attendee_type" class="form-control">
                    <option value="">-- All --</option>
                    <option value="new_soul" @selected(request('attendee_type')==='new_soul')>First-time visitors</option>
                    <option value="member" @selected(request('attendee_type')==='member')>Members</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All --</option>
                    <option value="registered" @selected(request('status')==='registered')>Registered</option>
                    <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment</label>
                <select name="payment_status" class="form-control">
                    <option value="">-- All --</option>
                    @foreach(['free'=>'Free','paid'=>'Paid','pending'=>'Pending','failed'=>'Failed','refunded'=>'Refunded'] as $val=>$label)
                        <option value="{{ $val }}" @selected(request('payment_status')===$val)>{{ $label }}</option>
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
            <div class="col-md-1">
                <button class="btn btn-primary w-100"><i class="icofont icofont-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card prog-card">
    <div class="card-body">
        @if($registrations->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Reference</th><th>Attendee</th><th>Type</th><th>Church</th><th>Program</th><th>Invited By</th><th>Status</th><th>Payment</th><th>Registered</th></tr></thead>
        <tbody>
        @foreach($registrations as $reg)
        <tr>
            <td class="fw-semibold">{{ $reg->registration_reference }}</td>
            <td>
                {{ optional($reg->member)->first_name }} {{ optional($reg->member)->last_name }}
                @if(optional($reg->member)->phone)<div class="text-muted small">{{ $reg->member->phone }}</div>@endif
            </td>
            <td><span class="badge-pill badge-type-{{ optional($reg->member)->member_type === 'new_soul' ? 'new_soul' : 'member' }}">{{ $reg->attendeeTypeLabel() }}</span></td>
            <td>{{ optional(optional($reg->member)->church)->name ?? '—' }}</td>
            <td>{{ optional($reg->program)->name }}</td>
            <td>{{ $reg->invitedByLabel() }}</td>
            <td><span class="badge-pill badge-status-{{ $reg->registration_status }}">{{ ucfirst($reg->registration_status) }}</span></td>
            <td><span class="badge-pill badge-payment-{{ $reg->payment_status }}">{{ ucfirst($reg->payment_status) }}</span></td>
            <td>{{ optional($reg->registered_at)->format('d M Y') }}</td>
        </tr>
        @endforeach
        </tbody>
        </table>
        </div>
        {{ $registrations->links() }}
        @else
        <div class="prog-empty"><i class="icofont icofont-ticket"></i><p class="mb-0">No registrations match these filters.</p></div>
        @endif
    </div>
</div>

</div>

@endsection
