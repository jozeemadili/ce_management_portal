@extends('layouts.admin.master')

@section('title', 'Access Control')

@push('css')
@include('portal.programs.partials.styles')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select2.css') }}">
<style>
    .select2-container .select2-selection--single { height: 42px; border: 1px solid #e2e6ee; border-radius: 8px; padding: 6px 4px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    .ac-menu { border: 1px solid #e3e8f0; border-radius: 12px; margin-bottom: 12px; overflow: hidden; }
    .ac-menu-head { background: #f7f9fc; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .ac-menu-head .form-check-label { font-weight: 700; }
    .ac-subs { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 4px 12px; padding: 10px 14px; }
    .ac-menu.ac-whole { border-color: #9fd3b4; }
    .ac-menu.ac-whole .ac-menu-head { background: #e6f7ee; }
    .ac-pill { font-size: .7rem; font-weight: 700; border-radius: 20px; padding: 2px 8px; background: #eef0f3; color: #6b7280; }
    .ac-app { background: #eaf1ff; color: #3563c9; cursor: help; }
    .ac-user-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f2f7; font-size: .88rem; }
    .ac-user-row:last-child { border-bottom: 0; }
    .ac-user-row.active { font-weight: 700; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Access Control</h3>
    @endslot
    <li class="breadcrumb-item">Security & Settings</li>
    <li class="breadcrumb-item active">Access Control</li>
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
<div class="col-lg-4 mb-3">
    <div class="card prog-card mb-3">
    <div class="card-body">
        <p class="modal-section-label">Choose a user</p>
        <form method="GET" action="{{ route('access-control.index') }}" id="pickUser">
            <select name="user" id="userPicker" class="form-control">
                @if($selected)
                    <option value="{{ $selected->id }}" selected>{{ trim($selected->first_name . ' ' . $selected->last_name) }}</option>
                @endif
            </select>
        </form>
        <p class="text-muted mt-2 mb-0" style="font-size:.82rem;">
            Search by name, phone or email. Users see only the menus ticked for them, and can't open the pages of other menus.
            The same menus apply in the <strong>mobile app</strong> (marked 📱 App).
            Everyone has <strong>Summary</strong> and <strong>Your Profile</strong>; ADMIN users see everything.
        </p>
    </div>
    </div>

    <div class="card prog-card">
    <div class="card-body">
        <p class="modal-section-label">Users with access ({{ $withAccess->count() }})</p>
        @forelse($withAccess as $u)
            <div class="ac-user-row {{ $selected && $selected->id === $u->id ? 'active' : '' }}">
                <a href="{{ route('access-control.index', ['user' => $u->id]) }}">{{ trim($u->first_name . ' ' . $u->last_name) }}</a>
                <span class="ac-pill">{{ $u->menu_grants_count }} menu(s)</span>
            </div>
        @empty
            <div class="text-muted" style="font-size:.85rem;">Nobody has been given menus yet.</div>
        @endforelse
        @if($admins->isNotEmpty())
            <p class="modal-section-label mt-3 mb-1">Admins (see everything)</p>
            <div style="font-size:.85rem;">{{ $admins->map(fn ($a) => trim($a->first_name . ' ' . $a->last_name))->implode(', ') }}</div>
        @endif
    </div>
    </div>
</div>

<div class="col-lg-8 mb-3">
    <div class="card prog-card">
    <div class="card-body">
    @if(!$selected)
        <div class="prog-empty"><i class="icofont icofont-lock"></i>Choose a user to see and change the menus they can use.</div>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h5 class="mb-0">{{ trim($selected->first_name . ' ' . $selected->last_name) }}</h5>
                <div class="text-muted" style="font-size:.85rem;">
                    {{ $selected->mobile ? '0' . $selected->mobile : '' }}{{ $selected->email ? ' · ' . $selected->email : '' }}{{ $selected->role ? ' · ' . $selected->role : '' }}
                </div>
            </div>
            @unless($locked)
            <form method="GET" action="{{ route('access-control.index') }}" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="user" value="{{ $selected->id }}">
                <select name="copy" class="form-select form-select-sm" style="min-width:200px;" onchange="this.form.submit()">
                    <option value="">Copy menus from...</option>
                    @foreach($withAccess->where('id', '!=', $selected->id) as $u)
                        <option value="{{ $u->id }}" @selected(optional($copyFrom)->id === $u->id)>{{ trim($u->first_name . ' ' . $u->last_name) }}</option>
                    @endforeach
                </select>
            </form>
            @endunless
        </div>

        @if($locked)
            <div class="alert alert-info">{{ $locked }}</div>
        @else
            @if($copyFrom)
                <div class="alert alert-warning py-2">Showing the menus of <strong>{{ trim($copyFrom->first_name . ' ' . $copyFrom->last_name) }}</strong>. Press <strong>Save access</strong> to give them to {{ $selected->first_name }}.</div>
            @endif
            @unless($iAmAdmin)
                <div class="alert alert-light border py-2" style="font-size:.85rem;">You can only give or remove menus that you have yourself.</div>
            @endunless

            <form method="POST" action="{{ route('access-control.save', $selected->id) }}" id="accessForm">
                @csrf
                <div class="mb-2 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" data-tick="all">Tick all</button>
                    <button type="button" class="btn btn-sm btn-light" data-tick="none">Untick all</button>
                </div>

                @foreach($catalog as $mainKey => $menu)
                    @php
                        $mainAlways = collect(array_keys($menu['children']))->every(fn ($k) => in_array($k, $always, true));
                        $whole = in_array($mainKey, $ticked, true);
                        $canMain = $iAmAdmin || in_array($mainKey, $myKeys, true);
                    @endphp
                    <div class="ac-menu {{ $whole || $mainAlways ? 'ac-whole' : '' }}" data-menu>
                        <div class="ac-menu-head">
                            <label class="form-check mb-0">
                                @if($mainAlways)
                                    <input class="form-check-input" type="checkbox" checked disabled>
                                @else
                                    <input class="form-check-input ac-main" type="checkbox" name="menus[]" value="{{ $mainKey }}" @checked($whole) @disabled(!$canMain)>
                                @endif
                                <span class="form-check-label">{{ $menu['label'] }}</span>
                            </label>
                            <span class="ac-pill">{{ $mainAlways ? 'Everyone' : 'Whole menu = all sub-menus' }}</span>
                        </div>
                        <div class="ac-subs">
                            @foreach($menu['children'] as $key => $label)
                                @php $isAlways = in_array($key, $always, true); $canSub = $iAmAdmin || in_array($key, $myKeys, true); @endphp
                                <label class="form-check mb-0">
                                    @if($isAlways)
                                        <input class="form-check-input" type="checkbox" checked disabled>
                                        <span class="form-check-label">{{ $label }} <span class="ac-pill">Everyone</span></span>
                                    @else
                                        <input class="form-check-input ac-sub" type="checkbox" name="menus[]" value="{{ $key }}"
                                               data-own="{{ in_array($key, $ticked, true) ? 1 : 0 }}"
                                               @checked($whole || in_array($key, $ticked, true)) @disabled(!$canSub)>
                                        <span class="form-check-label">{{ $label }}
                                            @if($appLabel = config('menu_access.app.' . $key))<span class="ac-pill ac-app" title="Also in the mobile app: {{ $appLabel }}">📱 App</span>@endif
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button class="btn btn-primary"><i class="icofont icofont-save"></i> Save access</button>
            </form>
        @endif
    @endif
    </div>
    </div>
</div>
</div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
(function () {
    var $ = window.jQuery;
    if ($ && $.fn.select2) {
        $('#userPicker').select2({
            width: '100%',
            placeholder: 'Search a user...',
            minimumInputLength: 2,
            ajax: {
                url: @json(route('access-control.users')),
                dataType: 'json',
                delay: 300,
                data: function (p) { return { q: p.term }; },
                processResults: function (data) { return data; }
            }
        }).on('select2:select', function () { document.getElementById('pickUser').submit(); });
    }

    // A ticked main menu covers all its sub-menus: show them ticked and locked.
    function sync(menu) {
        var main = menu.querySelector('.ac-main');
        if (!main) return;
        menu.classList.toggle('ac-whole', main.checked);
        menu.querySelectorAll('.ac-sub').forEach(function (sub) {
            if (sub.dataset.locked === undefined) sub.dataset.locked = sub.disabled ? '1' : '0';
            if (main.checked) {
                sub.checked = true;
                sub.disabled = true;
            } else {
                sub.disabled = sub.dataset.locked === '1';
            }
        });
    }
    document.querySelectorAll('[data-menu]').forEach(function (menu) {
        sync(menu);
        var main = menu.querySelector('.ac-main');
        if (main) main.addEventListener('change', function () {
            if (!main.checked) menu.querySelectorAll('.ac-sub').forEach(function (s) { s.checked = s.dataset.own === '1'; });
            sync(menu);
        });
    });

    document.querySelectorAll('[data-tick]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var on = btn.dataset.tick === 'all';
            document.querySelectorAll('[data-menu]').forEach(function (menu) {
                var main = menu.querySelector('.ac-main');
                if (main && !main.disabled) main.checked = on;
                menu.querySelectorAll('.ac-sub').forEach(function (s) { if (s.dataset.locked !== '1') { s.disabled = false; s.checked = on; } });
                sync(menu);
            });
        });
    });
})();
</script>
@endpush
