@extends('layouts.admin.master')

@section('title', 'My Check-in QR')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .my-qr { display: inline-block; background: #fff; padding: 14px; border-radius: 16px; box-shadow: 0 2px 12px rgba(46,90,172,.12); }
    .my-qr svg { display: block; width: 240px; height: 240px; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>My Check-in QR</h3>
    @endslot
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">My Check-in QR</li>
@endcomponent

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-5">
<div class="card prog-card">
<div class="card-body text-center">
    <h4 class="mb-1">{{ trim($member->first_name . ' ' . $member->last_name) }}</h4>
    <p class="text-muted">{{ optional($member->church)->name }}</p>
    <div class="my-qr mb-3">{!! $qrSvg !!}</div>
    <p class="text-muted mb-0" style="font-size:.88rem;">
        Show this code at the church entrance during a service. The usher scans it and you are marked as attended.
        It is yours alone &mdash; don't share it.
    </p>
</div>
</div>
</div>
</div>
</div>
@endsection
