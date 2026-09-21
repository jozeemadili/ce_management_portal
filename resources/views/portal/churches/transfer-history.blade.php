@extends('layouts.admin.master')

@section('title','Transfer History')

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Transfer History</h3>
    @endslot
    <li class="breadcrumb-item">Church</li>
    <li class="breadcrumb-item active">History</li>
@endcomponent

<div class="card">
<div class="card-body">

<h5>{{ strtoupper($church->name) }}</h5>

<table class="table table-bordered table-sm mt-3">
<thead>
<tr>
    <th>#</th>
    <th>Parent Church</th>
    <th>Start Date</th>
    <th>End Date</th>
</tr>
</thead>
<tbody>
@foreach($history as $row)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>
        {{ $row->church ? strtoupper($row->church->name) : 'ROOT' }}
    </td>
    <td>{{ $row->start_date }}</td>
    <td>{{ $row->end_date ?? 'CURRENT' }}</td>
</tr>
@endforeach
</tbody>
</table>

</div>
</div>

@endsection
