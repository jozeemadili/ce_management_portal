@extends('layouts.admin.master')

@section('title', 'Duplicate Members')

@push('css')
@include('portal.programs.partials.styles')
<style>
    .dup-group { border: 1px solid #e3e8f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; background: #fff; }
    .dup-reason { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; margin-right: 4px; background: #fff4e5; color: #b45309; text-transform: uppercase; }
    .dup-table td, .dup-table th { vertical-align: middle; font-size: .85rem; }
    .dup-table tr.keep-row { background: #eef8f1; }
</style>
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Duplicate Members</h3>
    @endslot
    <li class="breadcrumb-item">Church Setup</li>
    <li class="breadcrumb-item active">Duplicate Members</li>
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

<div class="card prog-card mb-3">
<div class="card-body">
    <p class="mb-1"><strong>{{ $groups->count() }}</strong> group(s) of people who may be registered more than once (same name, same phone in any format, or same email).</p>
    <p class="text-muted mb-0" style="font-size:.85rem;">
        For each group choose the record to <strong>keep</strong> (suggested: the one with a login and the most activity), tick the record(s)
        that are the <strong>same person</strong>, and merge. Their phone numbers become extra numbers of the kept record, and their attendance,
        registrations, payments, pledges, roles, cells, departments, invitees and login move to it. A merge cannot be undone, but a snapshot of
        every removed record is kept in the activity log. Different people who only share a name? Use <em>Not the same person</em>.
    </p>
</div>
</div>

@forelse($groups as $gi => $group)
    <form method="POST" action="{{ route('duplicates.merge') }}" class="dup-group" data-group
          onsubmit="return confirm('Merge the ticked record(s) into the one you keep? This cannot be undone.')">
        @csrf
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <strong>Group {{ $gi + 1 }}</strong> &middot; same
                @foreach($group['reasons'] as $r)<span class="dup-reason">{{ $r }}</span>@endforeach
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary"><i class="icofont icofont-ui-copy"></i> Merge ticked into kept</button>
                <button type="submit" class="btn btn-sm btn-light" form="ignore{{ $gi }}" onclick="return confirm('Mark these as different people?')">Not the same person</button>
            </div>
        </div>
        <div class="table-responsive">
        <table class="table dup-table mb-0">
            <thead><tr><th>Keep</th><th>Merge</th><th>Name</th><th>Church</th><th>Phones</th><th>Email</th><th>Login</th><th>Activity</th><th>Added</th></tr></thead>
            <tbody>
            @foreach($group['members'] as $mi => $m)
                <tr class="{{ $mi === 0 ? 'keep-row' : '' }}">
                    <td><input class="form-check-input keep-radio" type="radio" name="keep_id" value="{{ $m->id }}" @checked($mi === 0)></td>
                    <td><input class="form-check-input merge-box" type="checkbox" name="merge_ids[]" value="{{ $m->id }}" @disabled($mi === 0)></td>
                    <td>
                        <strong>{{ $m->titledName() }}</strong> <span class="text-muted">#{{ $m->id }}</span>
                        <div class="text-muted" style="font-size:.75rem;">{{ $m->member_type === 'member' ? 'Member' : 'First-time visitor' }}{{ $m->location ? ' · ' . $m->location : '' }}</div>
                    </td>
                    <td>{{ optional($m->church)->name ?? '—' }}</td>
                    <td>{!! collect($m->allPhones())->map(fn ($p) => e($p))->implode('<br>') ?: '—' !!}</td>
                    <td>{{ $m->email ?: '—' }}</td>
                    <td>{{ $m->user_id ? 'Yes' : '—' }}</td>
                    <td style="font-size:.78rem;">{{ $m->activity['attended'] }} attended<br>{{ $m->activity['registrations'] }} reg. &middot; {{ $m->activity['pledges'] }} pledges</td>
                    <td>{{ optional($m->created_at)->format('d M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </form>
    <form method="POST" action="{{ route('duplicates.ignore') }}" id="ignore{{ $gi }}" class="d-none">
        @csrf
        @foreach($group['members'] as $m)<input type="hidden" name="member_ids[]" value="{{ $m->id }}">@endforeach
    </form>
@empty
    <div class="card prog-card"><div class="card-body prog-empty"><i class="icofont icofont-check-circled"></i>No likely duplicates. 🎉</div></div>
@endforelse

</div>
@endsection

@push('scripts')
<script>
// The kept record can't also be merged away.
document.querySelectorAll('[data-group]').forEach(function (group) {
    group.addEventListener('change', function (e) {
        if (!e.target.classList.contains('keep-radio')) return;
        group.querySelectorAll('tbody tr').forEach(function (row) {
            var keep = row.querySelector('.keep-radio').checked;
            var box = row.querySelector('.merge-box');
            row.classList.toggle('keep-row', keep);
            box.disabled = keep;
            if (keep) box.checked = false;
        });
    });
});
</script>
@endpush
