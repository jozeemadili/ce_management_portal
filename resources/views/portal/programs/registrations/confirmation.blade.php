@extends('layouts.admin.master')

@section('title', 'Registration Confirmed')

@push('css')
@include('portal.programs.partials.styles')
@endpush

@section('content')

@component('components.breadcrumb')
    @slot('breadcrumb_title')
        <h3>Registration Confirmed</h3>
    @endslot
    @slot('breadcrumb_action_buttons')
        <li>
            <a class="btn btn-outline-primary" href="{{ route('my-programs.browse') }}">
                <i class="icofont icofont-plus-circle"></i> Register Another
            </a>
        </li>
    @endslot
    <li class="breadcrumb-item"><a href="{{ route('my-programs.browse') }}">Browse Programs</a></li>
    <li class="breadcrumb-item active">Confirmation</li>
@endcomponent

<div class="container-fluid">

<div class="row justify-content-center">
    <div class="col-lg-7 mb-3">
        <div class="card prog-card">
            <div class="card-body text-center py-4">
                <div class="prog-stat-icon bg-2 mx-auto mb-3" style="width:64px;height:64px;font-size:30px;">
                    <i class="icofont icofont-check-circled"></i>
                </div>
                <h5 class="mb-1">Registration Successful</h5>
                <p class="text-muted mb-4">{{ optional($registration->member)->first_name }} {{ optional($registration->member)->last_name }} is registered for {{ $program->name }}.</p>

                <span class="badge-pill badge-status-{{ $registration->registration_status }}">{{ ucfirst($registration->registration_status) }}</span>
                <span class="badge-pill badge-payment-{{ $registration->payment_status }}">{{ ucfirst($registration->payment_status) }}</span>

                <div class="d-flex justify-content-between py-2 border-bottom mt-3 text-start">
                    <span class="text-muted">Reference</span>
                    <strong>{{ $registration->registration_reference }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-start">
                    <span class="text-muted">Church</span>
                    <strong>{{ optional(optional($registration->member)->church)->name ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-start">
                    <span class="text-muted">Date</span>
                    <strong>{{ optional($program->start_date)->format('d M Y') ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 text-start">
                    <span class="text-muted">Access</span>
                    <strong>
                        @if($program->isFree())FREE
                        @else {{ $program->currency }} {{ number_format($program->registration_fee, 2) }}
                        @endif
                    </strong>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4 flex-wrap">
                    <a href="{{ route('my-programs.show', $registration->id) }}" class="btn btn-primary">
                        <i class="icofont icofont-eye"></i> View Registration
                    </a>
                    <a href="{{ route('my-programs.pdf', $registration->id) }}" class="btn btn-outline-primary">
                        <i class="icofont icofont-download"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-secondary" id="copyPdfLinkBtn" data-url="{{ route('my-programs.show', $registration->id) }}">
                        <i class="icofont icofont-link"></i> <span id="copyPdfLinkLabel">Copy Invitation Link</span>
                    </button>
                    <a href="{{ route('my-programs.browse') }}" class="btn btn-outline-primary">
                        <i class="icofont icofont-plus-circle"></i> Register Another
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

@endsection

@push('scripts')
<script>
document.getElementById('copyPdfLinkBtn').addEventListener('click', function () {
    var url = this.getAttribute('data-url');
    var label = document.getElementById('copyPdfLinkLabel');
    navigator.clipboard.writeText(url).then(function () {
        label.textContent = 'Link Copied!';
        setTimeout(function () { label.textContent = 'Copy Invitation Link'; }, 2000);
    });
});
</script>
@endpush
