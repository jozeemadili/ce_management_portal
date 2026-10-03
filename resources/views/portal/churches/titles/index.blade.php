@extends('layouts.admin.master')

@section('title', 'Member Titles')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Member Titles</h3>
    @endslot
    <li class="breadcrumb-item">Church Setup</li>
    <li class="breadcrumb-item active">Member Titles</li>
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
<div class="col-lg-8 mb-3">
<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Titles</p>
    <p class="text-muted" style="font-size:.85rem;">Shown in the Title dropdown when registering or editing a member, and accepted in the Title column of the Excel upload. Lower order numbers appear first. Switch a title off to hide it without changing members who have it.</p>
    <div class="table-responsive">
    <table class="table prog-table">
        <thead><tr><th style="width:90px;">Order</th><th>Title</th><th>Members</th><th>On</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @foreach($titles as $t)
            <tr>
                <td colspan="4" class="p-0">
                    <form method="POST" action="{{ route('member-titles.update', $t->id) }}" class="d-flex gap-2 align-items-center flex-wrap py-2" id="titleForm{{ $t->id }}">
                        @csrf
                        <input type="number" name="sort_order" value="{{ $t->sort_order }}" class="form-control form-control-sm" style="width:80px;" min="0">
                        <input name="name" value="{{ $t->name }}" class="form-control form-control-sm" style="max-width:220px;" required maxlength="50">
                        <span class="text-muted" style="min-width:90px;font-size:.85rem;">{{ $t->members_count }} member(s)</span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($t->is_active)>
                        </div>
                    </form>
                </td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <button class="btn btn-sm btn-primary" form="titleForm{{ $t->id }}">Save</button>
                        <form method="POST" action="{{ route('member-titles.destroy', $t->id) }}" onsubmit="return confirm('Delete the title {{ addslashes($t->name) }}?')">
                            @csrf <button class="btn btn-sm btn-light text-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
</div>
</div>

<div class="col-lg-4 mb-3">
<div class="card prog-card">
<div class="card-body">
    <p class="modal-section-label">Add a title</p>
    <form method="POST" action="{{ route('member-titles.store') }}">
        @csrf
        <label class="form-label">Title</label>
        <input name="name" class="form-control mb-2" placeholder="e.g. Minister" required maxlength="50">
        <label class="form-label">Order (optional)</label>
        <input type="number" name="sort_order" class="form-control mb-3" min="0" placeholder="Added at the end">
        <button class="btn btn-primary w-100"><i class="icofont icofont-plus-circle"></i> Add title</button>
    </form>
</div>
</div>
</div>
</div>

</div>
@endsection
