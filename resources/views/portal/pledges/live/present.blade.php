@extends('layouts.presentation')

@section('title', $campaign->name)

@push('css')
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body {
        height: 100%;
        background: radial-gradient(circle at top, #1c3a6e 0%, #0d1b33 65%, #060d1c 100%);
        color: #fff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        overflow: hidden;
    }
    /* Campaign banner, if uploaded, sits behind everything - the gradient
       overlay on top keeps it low-contrast so the stage text stays legible,
       rather than a plain flat background. */
    .bg-image {
        position: fixed; inset: 0; z-index: 0;
        background-size: cover; background-position: center;
        opacity: .45;
    }
    .bg-gradient-overlay {
        position: fixed; inset: 0; z-index: 1;
        background: linear-gradient(180deg, rgba(10,20,42,.60) 0%, rgba(8,16,34,.68) 55%, rgba(6,13,28,.75) 100%);
    }
    .stage {
        position: relative; z-index: 2;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4vh 6vw;
    }
    .stage-campaign {
        font-size: clamp(1.4rem, 3vw, 2.4rem);
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #a9c2ff;
        margin-bottom: 2vh;
    }
    .stage-amount {
        font-size: clamp(3.5rem, 10vw, 8rem);
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(90deg,#ffffff,#c7d7ff);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: transform .25s ease;
    }
    .stage-amount.pulse { transform: scale(1.04); }
    .stage-of-target { font-size: clamp(1.2rem, 2.4vw, 1.8rem); color: #9fb3dd; margin-top: 1vh; }
    .stage-percent { font-size: clamp(2rem, 4.5vw, 3.5rem); font-weight: 800; color: #4d9fff; margin-top: 2vh; }

    .stage-progress-wrap { width: min(70vw, 900px); margin-top: 2vh; }
    .stage-progress { height: 22px; border-radius: 30px; background: rgba(255,255,255,.12); overflow: hidden; }
    .stage-progress-bar { height: 100%; border-radius: 30px; background: linear-gradient(90deg,#4d7de0,#7ea6ff); transition: width .6s ease; }

    .stage-pledgers { margin-top: 3vh; font-size: clamp(1.1rem, 2vw, 1.5rem); color: #cdd9f5; }
    .stage-pledgers strong { font-size: clamp(1.6rem, 3vw, 2.4rem); color: #fff; display: block; }

    .latest-panel {
        position: fixed; z-index: 2; right: 3vw; bottom: 4vh; width: min(28vw, 380px);
        background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
        border-radius: 16px; padding: 18px; backdrop-filter: blur(6px);
    }
    .latest-panel h6 {
        font-size: .8rem; letter-spacing: .08em; text-transform: uppercase; color: #9fb3dd; margin-bottom: 10px;
    }
    .latest-row {
        display: flex; justify-content: space-between; padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,.08); font-size: .95rem;
        animation: fadeIn .5s ease;
    }
    .latest-row:last-child { border-bottom: none; }
    .latest-row .amt { color: #7ea6ff; font-weight: 700; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

    .new-pledge-toast {
        position: fixed; z-index: 2; top: 4vh; left: 50%; transform: translate(-50%, -30px);
        background: linear-gradient(135deg,#1fa971,#34d399); color: #fff; padding: 14px 28px;
        border-radius: 40px; font-weight: 700; font-size: 1.1rem; opacity: 0;
        transition: all .4s ease; box-shadow: 0 10px 30px rgba(0,0,0,.3);
    }
    .new-pledge-toast.show { opacity: 1; transform: translate(-50%, 0); }
</style>
@endpush

@section('content')
@if($campaign->banner_path)
<div class="bg-image" style="background-image:url('{{ asset('storage/' . $campaign->banner_path) }}');"></div>
<div class="bg-gradient-overlay"></div>
@endif
<div class="stage">
    <div class="stage-campaign">{{ $campaign->name }}</div>

    <div class="stage-amount" id="stageAmount">{{ $campaign->currency }} 0</div>

    <div class="stage-of-target" id="stageTarget" style="display:none">
        of {{ $campaign->currency }} <span id="stageTargetValue">0</span>
    </div>

    <div class="stage-percent" id="stagePercent">0%</div>

    <div class="stage-progress-wrap" id="stageGraphWrap" style="display:none">
        <div class="stage-progress"><div class="stage-progress-bar" id="stageProgressBar" style="width:0%"></div></div>
    </div>

    <div class="stage-pledgers" id="stagePledgersWrap" style="display:none">
        <strong id="stagePledgers">0</strong> Pledgers
    </div>
</div>

<div class="latest-panel" id="latestPanel" style="display:none">
    <h6>Latest Pledges</h6>
    <div id="latestList"></div>
</div>

<div class="new-pledge-toast" id="newPledgeToast"></div>
@endsection

@push('scripts')
<script>
const dataUrl = "{{ route('pledge-live.data', $campaign->id) }}";
let previousPledged = null;
let knownLatestKeys = new Set();
let firstLoad = true;

function formatAmount(n) {
    return Number(n).toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function showToast(message) {
    const toast = document.getElementById('newPledgeToast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
}

function pulseAmount() {
    const el = document.getElementById('stageAmount');
    el.classList.add('pulse');
    setTimeout(() => el.classList.remove('pulse'), 300);
}

function render(data) {
    document.getElementById('stageAmount').textContent = data.currency + ' ' + formatAmount(data.pledged);
    document.getElementById('stagePercent').textContent = data.percent + '%';

    if (data.show.target) {
        document.getElementById('stageTarget').style.display = '';
        document.getElementById('stageTargetValue').textContent = formatAmount(data.target);
    }

    if (data.show.graph) {
        document.getElementById('stageGraphWrap').style.display = '';
        document.getElementById('stageProgressBar').style.width = data.percent + '%';
    }

    if (data.show.pledgers) {
        document.getElementById('stagePledgersWrap').style.display = '';
        document.getElementById('stagePledgers').textContent = data.pledgers;
    }

    if (data.show.latest && data.latest.length) {
        const panel = document.getElementById('latestPanel');
        panel.style.display = '';
        const list = document.getElementById('latestList');
        list.innerHTML = '';
        data.latest.forEach(p => {
            const row = document.createElement('div');
            row.className = 'latest-row';
            row.innerHTML = `<span>${p.name}</span><span class="amt">${data.currency} ${formatAmount(p.amount)}</span>`;
            list.appendChild(row);
        });

        if (!firstLoad) {
            const newKeys = new Set(data.latest.map(p => p.at + p.amount));
            let hasNew = false;
            newKeys.forEach(k => { if (!knownLatestKeys.has(k)) hasNew = true; });
            if (hasNew) {
                pulseAmount();
                const top = data.latest[0];
                showToast(`New Pledge: ${data.currency} ${formatAmount(top.amount)}`);
            }
            knownLatestKeys = newKeys;
        } else {
            knownLatestKeys = new Set(data.latest.map(p => p.at + p.amount));
        }
    }

    previousPledged = data.pledged;
    firstLoad = false;
}

function poll() {
    fetch(dataUrl, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(render)
        .catch(() => {});
}

poll();
setInterval(poll, 4000);
</script>
@endpush
