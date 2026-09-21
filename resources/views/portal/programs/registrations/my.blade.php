@extends('layouts.admin.master')

@section('title', 'People I\'ve Invited')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>People I've Invited</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-primary" href="{{ route('my-programs.browse') }}">
                <i class="icofont icofont-plus-circle"></i> Browse Programs
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item active">People I've Invited</li>
@endcomponent

<div class="container-fluid">

<div class="card prog-card">
    <div class="card-body">
        @if($registrations->count())
        <div class="table-responsive">
        <table class="table prog-table align-middle">
        <thead><tr><th>Reference</th><th>Attendee</th><th>Church</th><th>Program</th><th>Date</th><th>Status</th><th>Payment</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($registrations as $reg)
        <tr>
            <td class="fw-semibold">{{ $reg->registration_reference }}</td>
            <td>{{ optional($reg->member)->first_name }} {{ optional($reg->member)->last_name }}</td>
            <td>{{ optional(optional($reg->member)->church)->name ?? '—' }}</td>
            <td>{{ optional($reg->program)->name }}</td>
            <td>{{ optional(optional($reg->program)->start_date)->format('d M Y') ?? '—' }}</td>
            <td><span class="badge-pill badge-status-{{ $reg->registration_status }}">{{ ucfirst($reg->registration_status) }}</span></td>
            <td><span class="badge-pill badge-payment-{{ $reg->payment_status }}">{{ ucfirst($reg->payment_status) }}</span></td>
            <td class="text-end">
                <a href="{{ route('my-programs.show', $reg->id) }}" class="btn btn-sm btn-light"><i class="icofont icofont-eye"></i></a>
                <a href="{{ route('my-programs.pdf', $reg->id) }}" class="btn btn-sm btn-light"><i class="icofont icofont-download"></i></a>
            </td>
        </tr>
        @endforeach
        </tbody>
        </table>
        </div>
        {{ $registrations->links() }}
        @else
        <div class="prog-empty"><i class="icofont icofont-listing-box"></i><p class="mb-0">You haven't invited anyone to a program yet.</p></div>
        @endif
    </div>
</div>

</div>

@endsection
