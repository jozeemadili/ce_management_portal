@extends('layouts.admin.master')
@section('title', 'Dashboard')

@push('css')
<style>
    .events-carousel { border-radius: 16px; overflow: hidden; box-shadow: 0 2px 14px rgba(46,90,172,.1); }
    .events-carousel .carousel-item { height: 340px; position: relative; }
    .events-slide-bg {
        position: absolute; inset: 0; background-size: cover; background-position: center;
        background: linear-gradient(135deg,#2e5aac,#4d7de0);
    }
    .events-slide-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(10,20,42,.15) 0%, rgba(8,16,34,.82) 100%);
    }
    .events-slide-content {
        position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column;
        justify-content: flex-end; padding: 28px 32px; color: #fff;
    }
    .events-slide-content h4 { margin: 0 0 6px; font-weight: 700; }
    .events-slide-content p { margin: 0 0 14px; opacity: .9; font-size: .88rem; }
    .events-countdown { display: flex; gap: 10px; margin-bottom: 16px; }
    .events-countdown .unit { background: rgba(255,255,255,.12); border-radius: 10px; padding: 8px 14px; text-align: center; min-width: 64px; }
    .events-countdown .unit .num { font-size: 1.3rem; font-weight: 700; line-height: 1; }
    .events-countdown .unit .lbl { font-size: .68rem; text-transform: uppercase; opacity: .8; margin-top: 2px; }
    .events-carousel .carousel-indicators { margin-bottom: 4px; }
    .events-empty { border-radius: 16px; padding: 60px 20px; text-align: center; color: #9aa2b1; background: #f7f9fc; }
    .events-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #c8cedb; }

    .pledge-summary-card { border: none; border-radius: 16px; box-shadow: 0 2px 14px rgba(46,90,172,.08); height: 100%; }
    .pledge-summary-stat { text-align: center; padding: 10px 4px; }
    .pledge-summary-stat .val { font-size: 1.15rem; font-weight: 700; color: #2e5aac; }
    .pledge-summary-stat .lbl { font-size: .72rem; color: #8a92a6; text-transform: uppercase; letter-spacing: .03em; }
    .pledge-recent-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f2f7; font-size: .84rem; }
    .pledge-recent-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="container-fluid dashboard-default-sec">

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="icofont icofont-calendar"></i> Coming Events</h5>
            <a href="{{ route('my-programs.browse') }}" class="btn btn-sm btn-outline-primary">
                <i class="icofont icofont-listing-box"></i> Browse All Programs
            </a>
        </div>

        @if($upcomingPrograms->count())
        <div id="eventsCarousel" class="carousel slide events-carousel" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                @foreach($upcomingPrograms as $i => $program)
                    <button type="button" data-bs-target="#eventsCarousel" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($upcomingPrograms as $i => $program)
                @php
                    $targetIso = optional($program->start_date)->format('Y-m-d') . 'T' . ($program->start_time ? \Illuminate\Support\Carbon::parse($program->start_time)->format('H:i:s') : '00:00:00');
                @endphp
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <div class="events-slide-bg" @if($program->banner_path) style="background-image:url('{{ asset('storage/'.$program->banner_path) }}');" @endif></div>
                    <div class="events-slide-overlay"></div>
                    <div class="events-slide-content">
                        <h4>{{ $program->name }}</h4>
                        <p><i class="icofont icofont-location-pin"></i> {{ $program->location ?? '—' }}
                            &middot; <i class="icofont icofont-calendar"></i> {{ optional($program->start_date)->format('d M Y') }}
                            @if($program->start_time) , {{ \Illuminate\Support\Carbon::parse($program->start_time)->format('H:i') }}@endif
                        </p>
                        <div class="events-countdown" data-countdown-target="{{ $targetIso }}">
                            <div class="unit"><div class="num days">--</div><div class="lbl">Days</div></div>
                            <div class="unit"><div class="num hours">--</div><div class="lbl">Hours</div></div>
                            <div class="unit"><div class="num minutes">--</div><div class="lbl">Mins</div></div>
                            <div class="unit"><div class="num seconds">--</div><div class="lbl">Secs</div></div>
                        </div>
                        <div>
                            <a href="{{ route('my-programs.browse') }}" class="btn btn-primary btn-sm">
                                <i class="icofont icofont-plus-circle"></i> Register
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        @else
        <div class="events-empty">
            <i class="icofont icofont-calendar"></i>
            <p class="mb-0">No upcoming events scheduled right now.</p>
        </div>
        @endif
    </div>
</div>

@if($pledgeSummary)
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card pledge-summary-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="icofont icofont-gift"></i> My Pledge Summary</h6>
                    <a href="{{ route('my-pledges.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="row">
                    <div class="col-3 pledge-summary-stat">
                        <div class="val">{{ $pledgeSummary['count'] }}</div>
                        <div class="lbl">Pledges</div>
                    </div>
                    <div class="col-3 pledge-summary-stat">
                        <div class="val">{{ number_format($pledgeSummary['total_pledged']) }}</div>
                        <div class="lbl">Pledged</div>
                    </div>
                    <div class="col-3 pledge-summary-stat">
                        <div class="val">{{ number_format($pledgeSummary['total_fulfilled']) }}</div>
                        <div class="lbl">Fulfilled</div>
                    </div>
                    <div class="col-3 pledge-summary-stat">
                        <div class="val">{{ number_format($pledgeSummary['outstanding']) }}</div>
                        <div class="lbl">Outstanding</div>
                    </div>
                </div>

                @if($pledgeSummary['recent']->count())
                <hr class="my-3">
                <p class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;">Recent Pledges</p>
                @foreach($pledgeSummary['recent'] as $pledge)
                <div class="pledge-recent-row">
                    <span>{{ optional($pledge->campaign)->name }}</span>
                    <strong>{{ optional($pledge->campaign)->currency }} {{ number_format($pledge->amount, 2) }}</strong>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card pledge-summary-card">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center h-100">
                <i class="icofont icofont-plus-circle" style="font-size:36px;color:#2e5aac;"></i>
                <h6 class="mt-2 mb-1">Make a New Pledge</h6>
                <p class="text-muted mb-3" style="font-size:.82rem;">Support an active campaign today.</p>
                <a href="{{ route('my-pledges.browse') }}" class="btn btn-primary btn-sm w-100">Browse Campaigns</a>
            </div>
        </div>
    </div>
</div>
@endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        document.querySelectorAll('[data-countdown-target]').forEach(function (el) {
            var target = new Date(el.getAttribute('data-countdown-target')).getTime();
            var now = new Date().getTime();
            var diff = target - now;

            var days = 0, hours = 0, minutes = 0, seconds = 0;
            if (diff > 0) {
                days = Math.floor(diff / (1000 * 60 * 60 * 24));
                hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                seconds = Math.floor((diff % (1000 * 60)) / 1000);
            }

            el.querySelector('.days').textContent = days;
            el.querySelector('.hours').textContent = pad(hours);
            el.querySelector('.minutes').textContent = pad(minutes);
            el.querySelector('.seconds').textContent = pad(seconds);
        });
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
