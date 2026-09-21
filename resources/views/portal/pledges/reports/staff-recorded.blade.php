@extends('layouts.admin.master')

@section('title', 'Staff Recorded Pledges')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Staff Recorded Pledges</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-success" href="{{ route('pledge-reports.staff-recorded.export', request()->query()) }}">
                Export Excel <i class="icofont icofont-file-excel"></i>
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item"><a href="{{ route('pledge-reports.index') }}">Pledges Reports</a></li>
    <li class="breadcrumb-item active">Staff Recorded</li>
@endcomponent

<div class="container-fluid">
<div class="row">
<div class="col-sm-12">
<div class="card pledge-card">
<div class="card-body">

<form method="GET" action="{{ route('pledge-reports.staff-recorded') }}" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-8">
        <label class="form-label mb-1">Search Member</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Member name">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i></button>
    </div>
</div>
</form>

@if($pledges->count())
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>Staff Member</th>
    <th>Member</th>
    <th>Church</th>
    <th>Campaign</th>
    <th class="text-end">Amount</th>
    <th>Date/Time</th>
</tr>
</thead>
<tbody>
@foreach($pledges as $pledge)
<tr>
    <td>{{ optional($pledge->recorder)->first_name }} {{ optional($pledge->recorder)->last_name }}</td>
    <td>{{ optional($pledge->member)->first_name }} {{ optional($pledge->member)->last_name }}</td>
    <td>{{ optional(optional($pledge->member)->church)->name ?? '—' }}</td>
    <td>{{ optional($pledge->campaign)->name }}</td>
    <td class="text-end">{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->amount, 2) }}</td>
    <td>{{ $pledge->created_at->format('d M Y, H:i') }}</td>
</tr>
@endforeach
</tbody>
</table>

{{ $pledges->links() }}
</div>
@else
<div class="pledge-empty">
    <i class="icofont icofont-badge"></i>
    <p class="mb-0">No staff-recorded pledges found.</p>
</div>
@endif

</div>
</div>
</div>
</div>
</div>

@endsection
