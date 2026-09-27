@extends('layouts.admin.master')

@section('title', 'SMS Templates')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .sms-type-help { font-size: .82rem; color: #6b7280; margin-bottom: 12px; }
    .sms-template { border: 1px solid #eef0f3; border-radius: 12px; padding: 14px 16px; margin-bottom: 12px; }
    .sms-template.inactive { opacity: .65; }
    .sms-body { white-space: pre-wrap; font-size: .88rem; margin: 8px 0; }
    .sms-preview { background: #f7f9fc; border-radius: 10px; padding: 10px 12px; font-size: .84rem; white-space: pre-wrap; }
    .sms-meta { font-size: .75rem; color: #8a92a6; }
    .sms-counter { font-size: .8rem; }
    .sms-counter .warn { color: #b45309; font-weight: 600; }
    .placeholder-chip { font-size: .75rem; margin: 0 4px 6px 0; }
    .sms-balance { background: #e6f7ee; color: #0f9d58; font-weight: 600; border-radius: 20px; padding: 6px 14px; font-size: .82rem; }
    .sms-balance.unknown { background: #fff4e5; color: #b45309; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>SMS Templates</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <button class="btn btn-primary" type="button" id="newTemplateBtn">
                New Template <i class="icofont icofont-plus-circle"></i>
            </button>
        </li>
    @endslot
    <li class="breadcrumb-item">Programs & Attendance</li>
    <li class="breadcrumb-item active">SMS Templates</li>
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

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    @if($balance !== null)
        <span class="sms-balance"><i class="icofont icofont-ui-message"></i> SMS balance: {{ number_format($balance) }}</span>
    @else
        <span class="sms-balance unknown"><i class="icofont icofont-warning"></i> SMS balance unavailable - check the SMS gateway settings in .env</span>
    @endif
    <span class="text-muted" style="font-size:.82rem;">
        A template for one program replaces the "All programs" template of the same type for that program.
        Turn a template off to stop that SMS.
    </span>
</div>

<div class="row">
@foreach(\App\Models\ProgramSmsTemplate::TYPES as $type => $label)
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card prog-card h-100">
            <div class="card-body">
                <p class="modal-section-label mb-1">{{ $label }}</p>
                <p class="sms-type-help">{{ \App\Models\ProgramSmsTemplate::TYPE_HELP[$type] }}</p>

                @forelse($templates->get($type, collect()) as $t)
                    <div class="sms-template {{ $t->is_active ? '' : 'inactive' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong style="font-size:.85rem;">{{ $t->program ? $t->program->name : 'All programs' }}</strong><br>
                                <span class="badge-pill {{ $t->is_active ? 'badge-status-active' : 'badge-status-draft' }}">{{ $t->is_active ? 'On' : 'Off' }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-light edit-template-btn" title="Edit"
                                    data-action="{{ route('program-sms.update', $t->id) }}"
                                    data-type="{{ $t->type }}"
                                    data-program="{{ $t->program_id }}"
                                    data-body="{{ $t->body }}"
                                    data-active="{{ $t->is_active ? 1 : 0 }}">
                                    <i class="icofont icofont-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('program-sms.destroy', $t->id) }}" onsubmit="return confirm('Delete this SMS template?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete"><i class="icofont icofont-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="sms-body">{{ $t->body }}</div>
                        <div class="sms-preview">{{ \App\Models\ProgramSmsTemplate::example($t->body) }}</div>
                        <div class="sms-meta mt-1 sms-static-count" data-text="{{ \App\Models\ProgramSmsTemplate::example($t->body) }}"></div>
                    </div>
                @empty
                    <div class="prog-empty py-4">
                        <i class="icofont icofont-ui-message"></i>
                        No template - this SMS is not sent.
                    </div>
                @endforelse

                <button type="button" class="btn btn-outline-primary btn-sm new-of-type-btn" data-type="{{ $type }}">
                    <i class="icofont icofont-plus"></i> Add {{ strtolower($label) }} template
                </button>
            </div>
        </div>
    </div>
@endforeach
</div>

</div>

{{-- Create / edit --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" method="POST" id="templateForm" action="{{ route('program-sms.store') }}">
            @csrf
            <input type="hidden" name="_editing" id="tplEditing">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">New SMS Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="tplType" class="form-select" required>
                            @foreach(\App\Models\ProgramSmsTemplate::TYPES as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="tplTypeHelp"></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Applies to</label>
                        <select name="program_id" id="tplProgram" class="form-select">
                            <option value="">All programs (default)</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}{{ $p->start_date ? ' - ' . $p->start_date->format('d M Y') : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="form-label">Message</label>
                <div class="mb-1">
                    @foreach(\App\Models\ProgramSmsTemplate::PLACEHOLDERS as $ph => [$desc, $example])
                        <button type="button" class="btn btn-light btn-sm placeholder-chip" data-ph="{{ $ph }}" title="{{ $desc }} (e.g. {{ $example }})">{{ $ph }}</button>
                    @endforeach
                </div>
                <textarea name="body" id="tplBody" class="form-control" rows="4" maxlength="1000" required
                    placeholder="Dear {first_name}, you are registered for {program}. Your code is {code}."></textarea>
                <div class="sms-counter mt-1" id="tplCounter"></div>

                <label class="form-label mt-3">Preview (with example values)</label>
                <div class="sms-preview" id="tplPreview"></div>
                <div class="sms-counter mt-1" id="tplPreviewCounter"></div>
                <small class="text-muted d-block mt-1">Real messages use each person's own name, code and amounts, so their length varies a little.</small>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tplActive" checked>
                    <label class="form-check-label" for="tplActive">On - send this SMS</label>
                </div>

                <hr>
                <label class="form-label">Send a test SMS <span class="text-muted">(uses the example values above)</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="tplTestPhone" placeholder="e.g. 0712345678">
                    <button type="button" class="btn btn-outline-secondary" id="tplTestBtn"><i class="icofont icofont-paper-plane"></i> Send test</button>
                </div>
                <small id="tplTestResult" class="d-block mt-1"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var PLACEHOLDERS = @json(collect(\App\Models\ProgramSmsTemplate::PLACEHOLDERS)->map(fn ($p) => $p[1]));
    var TYPE_HELP = @json(\App\Models\ProgramSmsTemplate::TYPE_HELP);
    var STORE_URL = @json(route('program-sms.store'));
    var TEST_URL = @json(route('program-sms.test'));

    // GSM 03.38: basic characters cost 1, extension characters 2; anything
    // else (emoji, curly quotes, ...) switches the SMS to Unicode (70 chars).
    var GSM = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
    var GSM_EXT = "^{}\\[~]|€";

    function smsInfo(text) {
        var length = 0, unicode = false;
        for (var ch of text) {
            if (GSM.indexOf(ch) !== -1) length += 1;
            else if (GSM_EXT.indexOf(ch) !== -1) length += 2;
            else unicode = true;
        }
        if (unicode) length = text.length;
        var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
        var parts = length === 0 ? 0 : (length <= single ? 1 : Math.ceil(length / multi));
        return { length: length, parts: parts, unicode: unicode };
    }

    function describe(info, withParts) {
        var s = info.length + ' characters';
        if (withParts) {
            s += ' · ' + info.parts + ' SMS' + (info.unicode ? ' (Unicode)' : '');
            if (info.parts > 1) s += ' <span class="warn">- costs ' + info.parts + ' credits per person</span>';
            if (info.unicode) s += ' <span class="warn">- special characters (e.g. emoji, curly quotes) shorten each SMS to 70</span>';
        }
        return s;
    }

    function example(body) {
        return body.split(/(\{[a-z_]+\})/).map(function (part) {
            return Object.prototype.hasOwnProperty.call(PLACEHOLDERS, part) ? PLACEHOLDERS[part] : part;
        }).join('').replace(/[ \t]{2,}/g, ' ').trim();
    }

    var modalEl = document.getElementById('templateModal');
    var modal = new bootstrap.Modal(modalEl);
    var form = document.getElementById('templateForm');
    var body = document.getElementById('tplBody');
    var type = document.getElementById('tplType');

    function refresh() {
        var preview = example(body.value);
        document.getElementById('tplCounter').innerHTML = 'Template: ' + describe(smsInfo(body.value), false);
        document.getElementById('tplPreview').textContent = preview || '—';
        document.getElementById('tplPreviewCounter').innerHTML = 'Example SMS: ' + describe(smsInfo(preview), true);
        document.getElementById('tplTypeHelp').textContent = TYPE_HELP[type.value] || '';
    }

    function open(opts) {
        form.action = opts.action || STORE_URL;
        document.getElementById('tplEditing').value = opts.action || '';
        document.getElementById('templateModalTitle').textContent = opts.action ? 'Edit SMS Template' : 'New SMS Template';
        type.value = opts.type || 'registration';
        document.getElementById('tplProgram').value = opts.program || '';
        body.value = opts.body || '';
        document.getElementById('tplActive').checked = opts.active !== '0';
        document.getElementById('tplTestResult').textContent = '';
        refresh();
        modal.show();
    }

    document.getElementById('newTemplateBtn').addEventListener('click', function () { open({}); });
    document.querySelectorAll('.new-of-type-btn').forEach(function (b) {
        b.addEventListener('click', function () { open({ type: b.dataset.type }); });
    });
    document.querySelectorAll('.edit-template-btn').forEach(function (b) {
        b.addEventListener('click', function () {
            open({ action: b.dataset.action, type: b.dataset.type, program: b.dataset.program, body: b.dataset.body, active: b.dataset.active });
        });
    });

    document.querySelectorAll('.placeholder-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var start = body.selectionStart, end = body.selectionEnd, ph = chip.dataset.ph;
            body.value = body.value.slice(0, start) + ph + body.value.slice(end);
            body.focus();
            body.selectionStart = body.selectionEnd = start + ph.length;
            refresh();
        });
    });

    body.addEventListener('input', refresh);
    type.addEventListener('change', refresh);

    document.querySelectorAll('.sms-static-count').forEach(function (el) {
        el.innerHTML = describe(smsInfo(el.dataset.text), true);
    });

    document.getElementById('tplTestBtn').addEventListener('click', function () {
        var btn = this, out = document.getElementById('tplTestResult');
        btn.disabled = true;
        out.className = 'd-block mt-1 text-muted';
        out.textContent = 'Sending...';
        fetch(TEST_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ phone: document.getElementById('tplTestPhone').value, body: body.value })
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
          .then(function (res) {
              out.className = 'd-block mt-1 ' + (res.ok ? 'text-success' : 'text-danger');
              out.textContent = res.ok ? 'Test SMS sent.' : (res.j.message || 'Sending failed.');
          })
          .catch(function () { out.className = 'd-block mt-1 text-danger'; out.textContent = 'Sending failed.'; })
          .finally(function () { btn.disabled = false; });
    });

    @if($errors->any() && old('body'))
        open({ action: @json(old('_editing')) || null, type: @json(old('type')), program: @json(old('program_id')), body: @json(old('body')), active: @json(old('is_active') ? '1' : '0') });
    @endif
})();
</script>
@endpush

@endsection
