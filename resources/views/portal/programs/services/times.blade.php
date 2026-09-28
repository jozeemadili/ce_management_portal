@extends('layouts.admin.master')

@section('title', 'Service Times')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .times-table th, .times-table td { vertical-align: middle; }
    .time-pair { display: flex; gap: 4px; align-items: center; min-width: 210px; }
    .time-pair input { max-width: 105px; }
    .time-pair input.is-custom { border-color: #4d7de0; background: #eef4ff; }
    .default-time { font-size: .75rem; color: #8a92a6; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Service Times</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('services.checkin') }}">
                <i class="icofont icofont-qr-code"></i> Service Check-in
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">Service Times</li>
@endcomponent

<div class="container-fluid">

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">{{ $error }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endforeach
@endif
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card prog-card">
<div class="card-body">

@if($services->isEmpty())
    <div class="prog-empty">
        <i class="icofont icofont-building-alt"></i>
        No church services yet. Create a <strong>recurring</strong> program (e.g. "Sunday Service") with scope
        <strong>Global</strong> (every church) or <strong>Church</strong>, frequency <strong>Weekly</strong>, its day(s)
        and a default start/end time, then set each church's own time here.
        <div class="mt-3"><a href="{{ route('programs.index', ['classification' => 'recurring']) }}" class="btn btn-primary btn-sm">Go to Recurring Services</a></div>
    </div>
@else
    <p class="text-muted" style="font-size:.85rem;">
        Each church uses the service's <strong>default time</strong> unless you set its own here. Leave a church's boxes
        empty to use the default. Check-in opens {{ \App\Services\ChurchServices::OPEN_BEFORE_MINUTES }} minutes before the start,
        arriving more than {{ \App\Services\ChurchServices::LATE_AFTER_MINUTES }} minutes after the start counts as <strong>late</strong>,
        and members not checked in by the end are marked <strong>absent</strong>.
    </p>

    <form method="POST" action="{{ route('services.times.save') }}">
        @csrf
        <div class="table-responsive">
        <table class="table prog-table times-table">
            <thead>
                <tr>
                    <th>Church</th>
                    @foreach($services as $service)
                        <th>
                            {{ $service->name }}<br>
                            <span class="default-time">
                                {{ $service->recurrence_frequency === 'daily' ? 'Every day' : collect($service->recurrence_days)->map(fn ($d) => ucfirst($d))->implode(', ') }}
                                &middot; default {{ substr($service->start_time, 0, 5) }}&ndash;{{ substr($service->end_time, 0, 5) }}
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($churches as $church)
                <tr>
                    <td><strong>{{ strtoupper($church->name) }}</strong></td>
                    @foreach($services as $service)
                        @php $o = $overrides->get($service->id . '-' . $church->id); @endphp
                        <td>
                            @if($service->scope === 'church' && (int) $service->church_id !== (int) $church->id)
                                <span class="text-muted">&mdash;</span>
                            @else
                                <div class="time-pair">
                                    <input type="time" class="form-control form-control-sm {{ $o ? 'is-custom' : '' }}"
                                           name="times[{{ $service->id }}][{{ $church->id }}][start]"
                                           value="{{ $o ? substr($o->start_time, 0, 5) : '' }}"
                                           placeholder="{{ substr($service->start_time, 0, 5) }}"
                                           title="Start (default {{ substr($service->start_time, 0, 5) }})">
                                    <span>&ndash;</span>
                                    <input type="time" class="form-control form-control-sm {{ $o ? 'is-custom' : '' }}"
                                           name="times[{{ $service->id }}][{{ $church->id }}][end]"
                                           value="{{ $o ? substr($o->end_time, 0, 5) : '' }}"
                                           placeholder="{{ substr($service->end_time, 0, 5) }}"
                                           title="End (default {{ substr($service->end_time, 0, 5) }})">
                                </div>
                                @unless($o)<span class="default-time">Default</span>@endunless
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <button class="btn btn-primary"><i class="icofont icofont-save"></i> Save Times</button>
    </form>
@endif

</div>
</div>
</div>
@endsection
