@extends('layouts.admin.master')

@section('title', 'My Pledges')

@push('css')
@include('portal.pledges.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>My Pledges</h3>
    @endslot

    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-primary" href="{{ route('my-pledges.browse') }}">
                <i class="icofont icofont-gift"></i> Make a Pledge
            </a>
        </li>
    @endslot

    <li class="breadcrumb-item">Pledges</li>
    <li class="breadcrumb-item active">My Pledges</li>
@endcomponent

<div class="container-fluid">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
<div class="col-sm-12">
<div class="card pledge-card">
<div class="card-body">

<form method="GET" action="{{ route('my-pledges.index') }}" class="pledge-filter-bar">
<div class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-control">
            <option value="">All Status</option>
            @foreach(['pledged','partially_fulfilled','fulfilled','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit"><i class="icofont icofont-search"></i> Filter</button>
    </div>
</div>
</form>

@if($pledges->count())
<div class="table-responsive">
<table class="table pledge-table align-middle">
<thead>
<tr>
    <th>Campaign</th>
    <th>Reference</th>
    <th class="text-end">Amount</th>
    <th class="text-end">Paid</th>
    <th class="text-end">Outstanding</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
@foreach($pledges as $pledge)
<tr class="pledge-row" style="cursor:pointer" onclick="window.location='{{ route('my-pledges.show', $pledge->id) }}'">
    <td>{{ optional($pledge->campaign)->name }}</td>
    <td><span class="fw-semibold">{{ $pledge->pledge_reference }}</span></td>
    <td class="text-end">{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->amount, 2) }}</td>
    <td class="text-end">{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->totalFulfilled(), 2) }}</td>
    <td class="text-end">{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->outstanding(), 2) }}</td>
    <td><span class="badge-pill badge-status-{{ $pledge->status }}">{{ ucfirst(str_replace('_',' ',$pledge->status)) }}</span></td>
</tr>
@endforeach
</tbody>
</table>

{{ $pledges->links() }}
</div>
@else
<div class="pledge-empty">
    <i class="icofont icofont-gift"></i>
    <p class="mb-0">You haven't made any pledges yet.</p>
</div>
@endif

</div>
</div>
</div>
</div>

</div>

@endsection
