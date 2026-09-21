@extends('layouts.admin.master')

@section('title', 'Programs & Attendance Settings')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Programs & Attendance Settings</h3>
    @endslot
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">Settings</li>
@endcomponent

<div class="container-fluid">

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Per-Program Settings</p>
                <p class="text-muted">Most Programs settings live on each individual program, not here:</p>
                <ul class="text-muted">
                    <li>Recurring vs special, church/department/cell scope, free/paid access &mdash; on the program's <strong>Create / Edit</strong> form.</li>
                    <li>QR/barcode check-in &mdash; always on for special programs, opt-in for recurring ones via the <strong>Enable QR/Barcode Check-in</strong> switch.</li>
                </ul>
                <a href="{{ route('programs.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="icofont icofont-listing-box"></i> Go to Programs
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label">Access Permissions</p>
                <p class="text-muted">These permission codes gate Programs & Attendance actions. Assign them to a designation via the existing role/permission tables to grant non-ADMIN staff access to specific actions.</p>
                <div class="table-responsive">
                <table class="table prog-table align-middle">
                <thead><tr><th>Code</th><th>Name</th></tr></thead>
                <tbody>
                @foreach($permissions as $p)
                <tr>
                    <td><code>{{ $p->code }}</code></td>
                    <td>{{ $p->name }}</td>
                </tr>
                @endforeach
                </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

@endsection
