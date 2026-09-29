@extends('layouts.admin.master')

@section('title', 'Check-in')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Check-in</h3>
    @endslot
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Check-in</li>
@endcomponent

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-6">

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card prog-card">
<div class="card-body text-center">
    <h4 class="mb-1">{{ trim($member->first_name . ' ' . $member->last_name) }}</h4>
    <p class="text-muted mb-3">{{ optional($member->church)->name }}</p>

    @if($error)
        <div class="alert alert-warning mb-3">{{ $error }}</div>
    @elseif($already)
        <div class="alert alert-warning mb-3">Already checked in at {{ optional($attendance->checked_in_at)->format('H:i') }} for {{ $occurrence->program->name }}.</div>
    @else
        <div class="alert alert-success mb-3">
            <strong>Checked in{{ $attendance->attendance_status === 'late' ? ' (late)' : '' }}</strong>
            at {{ optional($attendance->checked_in_at)->format('H:i') }} &middot; {{ $occurrence->program->name }}, {{ strtoupper($church->name) }}
        </div>
    @endif

    @if($occurrence && !$error)
        <p class="mb-2">Did {{ $member->first_name }} come with new souls?</p>
        <form method="POST" action="{{ route('services.new-souls', $occurrence->id) }}" class="text-start">
            @csrf
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            @for($i = 0; $i < 3; $i++)
                <div class="row g-2 mb-2">
                    <div class="col-6"><input class="form-control form-control-sm" name="people[{{ $i }}][first_name]" placeholder="First name{{ $i === 0 ? ' *' : '' }}" {{ $i === 0 ? 'required' : '' }}></div>
                    <div class="col-6"><input class="form-control form-control-sm" name="people[{{ $i }}][last_name]" placeholder="Last name"></div>
                    <div class="col-7"><input class="form-control form-control-sm" name="people[{{ $i }}][phone]" placeholder="Phone"></div>
                    <div class="col-6 order-last"><input type="email" class="form-control form-control-sm" name="people[{{ $i }}][email]" placeholder="Email"></div>
                    <div class="col-6 order-last"><input class="form-control form-control-sm" name="people[{{ $i }}][location]" placeholder="Where they live"></div>
                    <div class="col-5">
                        <select class="form-select form-select-sm" name="people[{{ $i }}][gender]">
                            <option value="">Gender</option><option value="male">Male</option><option value="female">Female</option>
                        </select>
                    </div>
                </div>
            @endfor
            <small class="text-muted d-block mb-2">Fill one row per person; leave the other rows empty.</small>
            <button class="btn btn-success w-100"><i class="icofont icofont-plus-circle"></i> Save new souls</button>
        </form>
    @endif

    <a href="{{ route('services.checkin') }}" class="btn btn-light w-100 mt-3">Back to Service Check-in</a>
</div>
</div>

</div>
</div>
</div>
@endsection
