@extends('layouts.admin.master')

@section('title', 'Church Services')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .svc-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 0; border-bottom: 1px solid #f0f2f7; }
    .svc-row:last-child { border-bottom: 0; }
    .svc-name { font-weight: 700; }
    .svc-meta { font-size: .8rem; color: #6b7280; }
    .problem { background: #fff8eb; border: 1px solid #fde7b8; border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; }
    .setup-row { background: #f7f9fc; border-radius: 12px; padding: 12px; margin-bottom: 10px; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Church Services</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li><a class="btn btn-primary" href="{{ route('services.checkin') }}"><i class="icofont icofont-qr-code"></i> Service Check-in</a></li>
        <li><a class="btn btn-outline-primary" href="{{ route('programs.index', ['classification' => 'recurring']) }}"><i class="icofont icofont-plus-circle"></i> New / Edit Service</a></li>
    @endslot
    <li class="breadcrumb-item">Church Services</li>
    <li class="breadcrumb-item active">Services</li>
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

<div class="row">
<div class="col-lg-7 mb-3">
    <div class="card prog-card h-100">
    <div class="card-body">
        <p class="modal-section-label">Running services</p>
        <p class="text-muted" style="font-size:.85rem;">
            These open for check-in automatically {{ \App\Services\ChurchServices::OPEN_BEFORE_MINUTES }} minutes before they start
            in each church, and mark absentees when they end.
        </p>
        @forelse($services as $service)
            <div class="svc-row">
                <div>
                    <div class="svc-name">{{ $service->name }}</div>
                    <div class="svc-meta">
                        <i class="icofont icofont-calendar"></i>
                        {{ $service->recurrence_frequency === 'daily' ? 'Every day' : collect($service->recurrence_days)->map(fn ($d) => ucfirst($d))->implode(', ') }}
                        &middot; default {{ substr($service->start_time, 0, 5) }}&ndash;{{ substr($service->end_time, 0, 5) }}
                        &middot; {{ $service->scope === 'global' ? 'All churches' : 'One church' }}
                        @if($customTimes->get($service->id))
                            &middot; {{ $customTimes->get($service->id) }} church(es) with their own time
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-light" href="{{ route('programs.show', $service->id) }}">Details</a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('services.times') }}">Church times</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('programs.checkin-poster', $service->id) }}" target="_blank"><i class="icofont icofont-print"></i> QR poster</a>
                </div>
            </div>
        @empty
            <div class="prog-empty py-4">
                <i class="icofont icofont-building-alt"></i>
                No service is running yet. Use <strong>Quick setup</strong>, or fix the ones listed under <strong>Not running</strong>.
            </div>
        @endforelse
    </div>
    </div>
</div>

<div class="col-lg-5 mb-3">
    @if($notRunning->isNotEmpty())
    <div class="card prog-card mb-3">
    <div class="card-body">
        <p class="modal-section-label text-warning"><i class="icofont icofont-warning"></i> Not running</p>
        <p class="text-muted" style="font-size:.85rem;">These recurring programs don't open for check-in. Fix the reason and they start running.</p>
        @foreach($notRunning as $row)
            <div class="problem">
                <strong>{{ $row->program->name }}</strong>
                <div style="font-size:.84rem;" class="mb-2">{{ $row->reason }}</div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($row->program->status !== 'active' && str_starts_with($row->reason, 'Status'))
                        <form method="POST" action="{{ route('programs.status', [$row->program->id, 'active']) }}">
                            @csrf
                            <button class="btn btn-sm btn-success">Activate</button>
                        </form>
                    @endif
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('programs.index', ['classification' => 'recurring']) }}">Edit in Programs</a>
                </div>
            </div>
        @endforeach
    </div>
    </div>
    @endif

    <div class="card prog-card mb-3" style="border: 2px dashed #f0b429;">
    <div class="card-body">
        <p class="modal-section-label" style="color:#b45309;"><i class="icofont icofont-graduate-alt"></i> Training mode</p>
        <p class="text-muted" style="font-size:.85rem;">
            A <strong>training service</strong> lets ushers practise any day: it is <strong>open for check-in all day</strong>
            (its start time still decides who is <em>late</em>), <strong>never marks anyone absent</strong>, <strong>sends no SMS</strong>
            and is <strong>left out of the dashboard and reports</strong>. <em>Reset</em> clears what was recorded.
        </p>

        @foreach($training as $t)
            <div class="setup-row">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <strong>{{ $t->name }}</strong>
                        <span class="badge-pill ms-1" style="background:#fff4e5;color:#b45309;">TRAINING</span>
                        <div class="svc-meta">
                            {{ $t->church ? strtoupper($t->church->name) : 'All churches' }}
                            &middot; start {{ substr($t->start_time, 0, 5) }} (late after {{ \Carbon\Carbon::parse($t->start_time)->addMinutes(\App\Services\ChurchServices::LATE_AFTER_MINUTES)->format('H:i') }})
                            @if($t->status !== 'active') &middot; {{ ucfirst($t->status) }} @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <a class="btn btn-sm btn-primary" href="{{ route('services.checkin', array_filter(['church' => $t->church_id, 'service' => $t->id])) }}">Practise check-in</a>
                    <form method="POST" action="{{ route('services.training.reset', $t->id) }}" onsubmit="return confirm('Remove all check-ins and practice new souls recorded in {{ addslashes($t->name) }}?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-warning">Reset</button>
                    </form>
                    <form method="POST" action="{{ route('services.training.remove', $t->id) }}" onsubmit="return confirm('Delete {{ addslashes($t->name) }} and everything recorded in it?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </div>
            </div>
        @endforeach

        <form method="POST" action="{{ route('services.training.create') }}" class="mt-2">
            @csrf
            <div class="row g-2">
                <div class="col-12"><input class="form-control form-control-sm" name="name" value="{{ old('name', 'Training Service') }}" placeholder="Name" required></div>
                <div class="col-12">
                    <select name="church_id" class="form-select form-select-sm">
                        @foreach($churches as $c)
                            <option value="{{ $c->id }}" @selected((int) old('church_id', optional(Auth::user()->member)->church_id) === $c->id)>{{ strtoupper($c->name) }}</option>
                        @endforeach
                        <option value="">All churches</option>
                    </select>
                </div>
                <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Starts (for "late")</label><input type="time" class="form-control form-control-sm" name="start" value="{{ old('start', now()->format('H:00')) }}" required></div>
                <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Ends</label><input type="time" class="form-control form-control-sm" name="end" value="{{ old('end', '23:00') }}" required></div>
            </div>
            <button class="btn btn-warning btn-sm w-100 mt-2"><i class="icofont icofont-plus-circle"></i> Create training service</button>
        </form>
    </div>
    </div>

    @if($missing)
    <div class="card prog-card">
    <div class="card-body">
        <p class="modal-section-label">Quick setup</p>
        <p class="text-muted" style="font-size:.85rem;">
            Create the weekly services that don't exist yet, held in <strong>every church</strong>. Set the default time;
            churches with a different time set their own under Service Times.
        </p>
        <form method="POST" action="{{ route('services.setup') }}">
            @csrf
            @foreach($missing as $day)
                @php [$name, $start, $end] = \App\Services\ChurchServices::STANDARD_SERVICES[$day]; @endphp
                <div class="setup-row">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="create[]" value="{{ $day }}" id="create_{{ $day }}" checked>
                        <label class="form-check-label fw-bold" for="create_{{ $day }}">{{ ucfirst($day) }}</label>
                    </div>
                    <div class="row g-2">
                        <div class="col-12"><input class="form-control form-control-sm" name="name[{{ $day }}]" value="{{ old('name.' . $day, $name) }}" placeholder="Name"></div>
                        <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Starts</label><input type="time" class="form-control form-control-sm" name="start[{{ $day }}]" value="{{ old('start.' . $day, $start) }}"></div>
                        <div class="col-6"><label class="form-label mb-0" style="font-size:.75rem;">Ends</label><input type="time" class="form-control form-control-sm" name="end[{{ $day }}]" value="{{ old('end.' . $day, $end) }}"></div>
                    </div>
                </div>
            @endforeach
            <button class="btn btn-primary w-100"><i class="icofont icofont-plus-circle"></i> Create ticked services</button>
        </form>
    </div>
    </div>
    @endif
</div>
</div>

</div>
@endsection
