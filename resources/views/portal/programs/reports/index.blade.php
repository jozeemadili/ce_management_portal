@extends('layouts.admin.master')

@section('title', 'Program Reports')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Program Reports</h3>
    @endslot
    <li class="breadcrumb-item">Programs</li>
    <li class="breadcrumb-item active">Reports</li>
@endcomponent

<div class="container-fluid">
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-1 mx-auto mb-3"><i class="icofont icofont-listing-box"></i></div>
                <h6>Program Report</h6>
                <p class="text-muted small">Every program with classification, scope, access and totals.</p>
                <a href="{{ route('programs.index') }}" class="btn btn-outline-primary btn-sm">View</a>
                <a href="{{ route('programs.export') }}" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-2 mx-auto mb-3"><i class="icofont icofont-ticket"></i></div>
                <h6>Registration Report</h6>
                <p class="text-muted small">Every registration by program, member, status and payment.</p>
                <a href="{{ route('program-reports.registrations') }}" class="btn btn-outline-primary btn-sm">View</a>
                <a href="{{ route('program-reports.registrations.export') }}" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-4 mx-auto mb-3"><i class="icofont icofont-check-circled"></i></div>
                <h6>Attendance Report</h6>
                <p class="text-muted small">Every attendance record by program, date, status and method.</p>
                <a href="{{ route('program-reports.attendance') }}" class="btn btn-outline-primary btn-sm">View</a>
                <a href="{{ route('program-reports.attendance.export') }}" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-3 mx-auto mb-3"><i class="icofont icofont-badge"></i></div>
                <h6>New Souls Report</h6>
                <p class="text-muted small">First-time visitors with follow-up status and church.</p>
                <a href="{{ route('new-souls.index') }}" class="btn btn-outline-primary btn-sm">View</a>
                <a href="{{ route('program-reports.new-souls.export') }}" class="btn btn-outline-success btn-sm">Export</a>
            </div>
        </div>
    </div>

</div>
</div>

@endsection
