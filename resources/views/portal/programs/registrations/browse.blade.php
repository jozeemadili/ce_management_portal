@extends('layouts.admin.master')

@section('title', 'Programs Open for Registration')

@push('css')
@include('portal.programs.partials.styles')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select2.css') }}">
<style>
    .register-church-hint { font-size: .78rem; }
    .register-mode-group .btn { flex: 1; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Programs Open for Registration</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('my-programs.index') }}">
                <i class="icofont icofont-listing-box"></i> People I've Invited
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item active">Browse Programs</li>
@endcomponent

<div class="container-fluid">

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $error }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endforeach
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($programs->count())
<div class="row">
    @foreach($programs as $program)
    @php $alreadyRegistered = $myRegisteredIds->contains($program->id); @endphp
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="program-card">
            <div class="program-card-banner" @if($program->banner_path) style="background-image:url('{{ asset('storage/'.$program->banner_path) }}');background-size:cover;background-position:center;" @endif>
                @if(!$program->banner_path)<i class="icofont icofont-calendar"></i>@endif
            </div>
            <div class="program-card-body">
                <h5 class="program-card-title">{{ $program->name }}</h5>
                <p class="program-card-desc">{{ $program->description ? \Illuminate\Support\Str::limit($program->description, 90) : 'No description.' }}</p>
                <div class="program-card-meta">
                    <span><i class="icofont icofont-location-pin"></i> {{ $program->location ?? '—' }}</span>
                </div>
                <div class="program-card-meta mt-1">
                    <span><i class="icofont icofont-calendar"></i>
                        {{ optional($program->start_date)->format('d M Y') ?? '—' }}
                        @if($program->end_date && !$program->end_date->equalTo($program->start_date))
                            &ndash; {{ $program->end_date->format('d M Y') }}
                        @endif
                    </span>
                </div>
                @if($program->sessions->isNotEmpty())
                <div class="program-card-meta mt-1">
                    <span><i class="icofont icofont-clock-time"></i> Daily:
                        {{ $program->sessions->map(fn ($x) => $x->name . ' ' . $x->timeRange())->implode(', ') }}
                    </span>
                </div>
                @endif
                <span class="badge-pill badge-access-{{ $program->access_type }} mt-2 d-inline-block">
                    {{ $program->accessLabel() }}
                </span>
                @if(!$program->isFree() && $currentMember)
                    @php $myPrice = $program->priceFor($currentMember); @endphp
                    <div class="small mt-1">
                        Your price: <strong>{{ $myPrice['amount'] > 0 ? $program->currency . ' ' . number_format($myPrice['amount']) : 'FREE' }}</strong>
                        @if($myPrice['designation']) <span class="text-muted">({{ ucwords($myPrice['designation']->name) }})</span>@endif
                    </div>
                @endif

                @if($alreadyRegistered)
                <p class="text-success small mb-1 mt-2"><i class="icofont icofont-check-circled"></i> You're already registered</p>
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal{{ $program->id }}">
                    <i class="icofont icofont-plus-circle"></i> Register Someone Else
                </button>
                @elseif($currentMember)
                {{-- Self-registration: one click, no modal - reuses the same
                     store() endpoint via a plain hidden member_id field. --}}
                <form method="POST" action="{{ route('my-programs.register', $program->id) }}" class="mt-2">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $currentMember->id }}">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="icofont icofont-plus-circle"></i> Register
                    </button>
                </form>
                @else
                <button type="button" class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#registerModal{{ $program->id }}">
                    <i class="icofont icofont-plus-circle"></i> Register
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Registration desk modals - kept OUTSIDE the .row grid above (Bootstrap's
     .row is display:flex; a .modal placed inside it as a flex item can end
     up mispositioned instead of centered, even though position:fixed should
     normally escape flex flow). One modal per program, matched by id. --}}
@foreach($programs as $program)
@php $alreadyRegistered = $myRegisteredIds->contains($program->id); @endphp
<div class="modal fade" id="registerModal{{ $program->id }}">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<form method="POST" action="{{ route('my-programs.register', $program->id) }}" class="program-register-form">
@csrf
<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-plus-circle"></i> Register for {{ $program->name }}</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <p class="text-muted small">
        @if($alreadyRegistered)
            You're already registered for this program &mdash; register someone else below.
        @else
            Register yourself, or anyone else attending.
        @endif
    </p>

    <label class="form-label">Church</label>
    <select name="church_id" class="form-control register-church-select" required>
        <option value="">-- Select Church --</option>
        @foreach($churches as $church)
            <option value="{{ $church->id }}" @selected(optional($currentMember)->church_id == $church->id)>{{ strtoupper($church->name) }}</option>
        @endforeach
    </select>
    <p class="text-muted mt-1 mb-3 register-church-hint"></p>

    <div class="btn-group register-mode-group w-100 mb-3" role="group">
        <input type="radio" class="btn-check register-mode-radio" name="mode" id="modeExisting{{ $program->id }}" value="existing" checked>
        <label class="btn btn-outline-primary btn-sm" for="modeExisting{{ $program->id }}">Existing Church Member</label>
        <input type="radio" class="btn-check register-mode-radio" name="mode" id="modeNew{{ $program->id }}" value="new">
        <label class="btn btn-outline-primary btn-sm" for="modeNew{{ $program->id }}">First-Time Visitor</label>
        <input type="radio" class="btn-check register-mode-radio" name="mode" id="modeUpload{{ $program->id }}" value="upload">
        <label class="btn btn-outline-primary btn-sm" for="modeUpload{{ $program->id }}"><i class="icofont icofont-file-excel"></i> Upload Excel</label>
    </div>

    <div class="register-existing-wrap">
        <label class="form-label">Search Member</label>
        <select class="form-control register-member-select" name="member_id" style="width:100%">
            <option value="">-- Type to search --</option>
        </select>
    </div>

    <div class="register-new-wrap" style="display:none;">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control register-new-input" value="{{ $alreadyRegistered ? '' : optional($currentMember)->first_name }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control register-new-input" value="{{ $alreadyRegistered ? '' : optional($currentMember)->last_name }}" disabled>
            </div>
            <div class="col-md-12 mt-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control register-new-input" value="{{ $alreadyRegistered ? '' : optional($currentMember)->phone }}" disabled>
            </div>
        </div>
        <p class="text-muted mt-2 mb-0 register-new-hint" style="font-size:.78rem;"></p>
    </div>
    <div class="register-upload-wrap" style="display:none;" data-preview-url="{{ route('my-programs.visitors.preview', $program->id) }}">
        <div class="register-upload-step1">
            <div class="alert alert-info py-2 mb-3 register-upload-hint" style="font-size:.85rem;"></div>
            <p class="mb-2" style="font-size:.85rem;">
                Upload many <strong>first-time visitors</strong> at once.
                <a href="{{ route('my-programs.visitor-template') }}"><i class="icofont icofont-download"></i> Download the template</a>
                &mdash; <strong>First Name</strong> and <strong>Phone</strong> are required; Last Name, Gender and Invited By are optional.
            </p>
            <input type="file" class="form-control register-upload-file" accept=".xlsx,.xls,.csv">
            <div class="text-danger small mt-2 register-upload-error" style="display:none;"></div>
        </div>
        <div class="register-upload-step2" style="display:none;">
            <div class="alert mb-3 register-upload-summary"></div>
            <div class="register-upload-existing-wrap mb-3" style="display:none;">
                <h6 class="mb-2" style="font-size:.9rem;"><i class="icofont icofont-info-circle text-primary"></i> <span class="register-upload-existing-title"></span></h6>
                <div class="table-responsive" style="max-height:170px;">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.8rem;">
                        <thead class="table-light"><tr><th style="width:55px;">Row</th><th>Name</th><th>Note</th></tr></thead>
                        <tbody class="register-upload-existing-rows"></tbody>
                    </table>
                </div>
            </div>
            <div class="register-upload-skipped-wrap" style="display:none;">
                <h6 class="mb-2" style="font-size:.9rem;"><i class="icofont icofont-warning text-warning"></i> <span class="register-upload-skipped-title"></span></h6>
                <div class="table-responsive" style="max-height:170px;">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.8rem;">
                        <thead class="table-light"><tr><th style="width:55px;">Row</th><th>Name</th><th>Reason</th></tr></thead>
                        <tbody class="register-upload-skipped-rows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary register-submit-btn">Register</button>
    <button type="button" class="btn btn-light register-upload-back" style="display:none;"><i class="icofont icofont-arrow-left"></i> Choose Another File</button>
    <button type="button" class="btn btn-primary register-upload-check" style="display:none;"><i class="icofont icofont-search-document"></i> Check File</button>
    <button type="button" class="btn btn-primary register-upload-confirm" style="display:none;"></button>
</div>
</form>
{{-- Separate form (nested forms aren't allowed) that registers a checked upload. --}}
<form method="POST" action="{{ route('my-programs.visitors.register', $program->id) }}" class="register-upload-submit d-none">
    @csrf
    <input type="hidden" name="token">
    <input type="hidden" name="church_id">
</form>
</div>
</div>
</div>
@endforeach

@else
<div class="card prog-card"><div class="card-body prog-empty">
    <i class="icofont icofont-calendar"></i>
    <p class="mb-0">No programs are currently open for registration.</p>
</div></div>
@endif

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
(function () {
    var $ = window.jQuery;
    if (!$) return;

    function updateChurchHint($form) {
        var $sel = $form.find('.register-church-select');
        var text = $sel.find('option:selected').text();
        var hasChurch = !!$sel.val();
        $form.find('.register-church-hint').text(hasChurch ? 'Searching members within ' + text + '.' : '');
        $form.find('.register-new-hint').text(hasChurch ? 'This new soul will belong to ' + text + '.' : 'Select a church above first.');
    }

    function applyMode($form) {
        var mode = $form.find('.register-mode-radio:checked').val();
        var $memberSelect = $form.find('.register-member-select');
        var $newInputs = $form.find('.register-new-input');
        var upload = mode === 'upload';

        $form.find('.register-existing-wrap').toggle(mode === 'existing');
        $form.find('.register-church-hint').toggle(mode === 'existing');
        $form.find('.register-new-wrap').toggle(mode === 'new');
        $form.find('.register-upload-wrap').toggle(upload);
        $form.find('.register-submit-btn').toggle(!upload);

        $memberSelect.prop('disabled', mode !== 'existing');
        if (mode !== 'existing' && $memberSelect.hasClass('select2-hidden-accessible')) {
            $memberSelect.val(null).trigger('change');
        }
        $newInputs.prop('disabled', mode !== 'new');

        if (upload) {
            showUploadStep($form, 1);
        } else {
            $form.find('.register-upload-check, .register-upload-back, .register-upload-confirm').hide();
        }
    }

    /* --------------------------------
     | EXCEL UPLOAD OF FIRST-TIME VISITORS: check the file (preview), show how
     | many will be registered for this program and which church new people
     | will belong to, then register them.
     |---------------------------------*/
    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function selectedChurchName($form) {
        var $sel = $form.find('.register-church-select');
        return $sel.val() ? $sel.find('option:selected').text().trim() : '';
    }

    function updateUploadHint($form) {
        var church = selectedChurchName($form);
        $form.find('.register-upload-hint').html(church
            ? 'New first-time visitors in the file will belong to <strong>' + escapeHtml(church) + '</strong> (the church selected above).'
            : 'Select the church these visitors belong to above first.');
    }

    function showUploadStep($form, step) {
        $form.find('.register-upload-step1').toggle(step === 1);
        $form.find('.register-upload-step2').toggle(step === 2);
        $form.find('.register-upload-check').toggle(step === 1);
        $form.find('.register-upload-back, .register-upload-confirm').toggle(step === 2);
        if (step === 1) {
            $form.find('.register-upload-file').val('');
            $form.find('.register-upload-error').hide();
            updateUploadHint($form);
        }
    }

    function plural(n, one, many) {
        return n + ' ' + (n === 1 ? one : many);
    }

    $(document).on('click', '.register-upload-back', function () {
        showUploadStep($(this).closest('form'), 1);
    });

    $(document).on('click', '.register-upload-check', function () {
        var $form = $(this).closest('form');
        var $btn = $(this);
        var $error = $form.find('.register-upload-error');
        var file = $form.find('.register-upload-file')[0].files[0];
        var churchId = $form.find('.register-church-select').val();

        $error.hide();
        if (!churchId) { $error.text('Select the church these visitors belong to first.').show(); return; }
        if (!file) { $error.text('Choose an Excel file to upload.').show(); return; }

        var data = new FormData();
        data.append('_token', $form.find('input[name="_token"]').val());
        data.append('church_id', churchId);
        data.append('file', file);

        var btnHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Checking...');

        fetch($form.find('.register-upload-wrap').data('preview-url'), {
            method: 'POST',
            body: data,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (body) {
                if (!res.ok) {
                    var message = body.message || 'The file could not be checked.';
                    if (body.errors) { message = Object.values(body.errors).flat().join(' '); }
                    throw new Error(message);
                }
                return body;
            });
        })
        .then(function (d) {
            var count = d.register_count;
            var church = escapeHtml(String(d.church).toUpperCase());
            var program = escapeHtml(d.program);

            $form.data('upload', { token: d.token, churchId: churchId });
            $form.find('.register-upload-summary')
                .removeClass('alert-success alert-warning')
                .addClass(count > 0 ? 'alert-success' : 'alert-warning')
                .html(count > 0
                    ? '<i class="icofont icofont-check-circled"></i> <strong>' + plural(count, 'person', 'people') +
                      '</strong> from this Excel will be registered for <strong>' + program + '</strong>.' +
                      (d.new_count > 0 ? '<br><strong>' + plural(d.new_count, 'new first-time visitor', 'new first-time visitors') +
                      '</strong> will belong to <strong>' + church + '</strong>.' : '') +
                      ' <span style="opacity:.85;">(' + plural(d.total_rows, 'row', 'rows') + ' in the file)</span>'
                    : '<i class="icofont icofont-warning"></i> Nobody in this Excel can be registered for <strong>' + program + '</strong>.');

            var existing = d.existing || [];
            $form.find('.register-upload-existing-wrap').toggle(existing.length > 0);
            $form.find('.register-upload-existing-title').text(plural(existing.length, 'person is', 'people are') + ' already in the system (no duplicate will be created)');
            $form.find('.register-upload-existing-rows').html(existing.map(function (r) {
                return '<tr><td>' + r.row + '</td><td>' + escapeHtml(r.name) + '</td><td>' + escapeHtml(r.note) + '</td></tr>';
            }).join(''));

            var skipped = d.skipped || [];
            $form.find('.register-upload-skipped-wrap').toggle(skipped.length > 0);
            $form.find('.register-upload-skipped-title').text(plural(skipped.length, 'row', 'rows') + ' will be skipped');
            $form.find('.register-upload-skipped-rows').html(skipped.map(function (r) {
                return '<tr><td>' + r.row + '</td><td>' + escapeHtml(r.name) + '</td><td>' + escapeHtml(r.reason) + '</td></tr>';
            }).join(''));

            $form.find('.register-upload-confirm')
                .prop('disabled', count === 0)
                .html('<i class="icofont icofont-plus-circle"></i> Register ' + plural(count, 'Person', 'People'));

            showUploadStep($form, 2);
        })
        .catch(function (err) {
            $error.text(err.message).show();
        })
        .finally(function () {
            $btn.prop('disabled', false).html(btnHtml);
        });
    });

    $(document).on('click', '.register-upload-confirm', function () {
        var $form = $(this).closest('form');
        var upload = $form.data('upload');
        if (!upload) return;
        var $submit = $form.closest('.modal-content').find('form.register-upload-submit');
        $submit.find('input[name="token"]').val(upload.token);
        $submit.find('input[name="church_id"]').val(upload.churchId);
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Registering...');
        $submit.trigger('submit');
    });

    $(document).on('change', '.register-mode-radio', function () {
        applyMode($(this).closest('form'));
    });

    $(document).on('change', '.register-church-select', function () {
        var $form = $(this).closest('form');
        updateChurchHint($form);
        if ($form.find('.register-mode-radio:checked').val() === 'upload') {
            showUploadStep($form, 1);
        }
        var $memberSelect = $form.find('.register-member-select');
        if ($memberSelect.hasClass('select2-hidden-accessible')) {
            $memberSelect.val(null).trigger('change');
        }
    });

    if ($.fn.select2) {
        $('.register-member-select').each(function () {
            var $el = $(this);
            var $modal = $el.closest('.modal');
            var $form = $el.closest('form');
            $el.select2({
                placeholder: 'Search by name or phone...',
                minimumInputLength: 2,
                width: '100%',
                dropdownParent: $modal,
                ajax: {
                    url: '{{ route('my-programs.search-members') }}',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return { q: params.term, church_id: $form.find('.register-church-select').val() };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                }
            });
        });
    }

    $(document).on('shown.bs.modal', '.modal', function () {
        var $form = $(this).find('form.program-register-form');
        if (!$form.length) return;
        updateChurchHint($form);
        applyMode($form);
    });
})();
</script>
@endpush
