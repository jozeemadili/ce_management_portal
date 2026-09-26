{{-- Payments of one registration. $payments, $currency; $canReview shows
     Confirm / Reject on payments awaiting confirmation (staff). --}}
@php $canReview = $canReview ?? false; @endphp
@if($payments->count())
<div class="table-responsive">
<table class="table table-sm align-middle mb-0">
    <thead class="table-light">
        <tr><th>Date</th><th class="text-end">Amount</th><th>Method / Reference</th><th>Status</th><th>Proof</th>@if($canReview)<th></th>@endif</tr>
    </thead>
    <tbody>
    @foreach($payments->sortByDesc('created_at') as $pay)
        <tr>
            <td>{{ $pay->payment_date->format('d M Y') }}</td>
            <td class="text-end fw-semibold">{{ $currency }} {{ number_format($pay->amount) }}</td>
            <td>
                {{ $pay->payment_method ?? '—' }}
                @if($pay->payment_reference)<div class="text-muted small">{{ $pay->payment_reference }}</div>@endif
            </td>
            <td>
                <span class="badge-pill {{ $pay->status === 'confirmed' ? 'badge-payment-paid' : ($pay->status === 'pending' ? 'badge-payment-pending' : 'badge-payment-failed') }}">{{ $pay->statusLabel() }}</span>
                @if($pay->status === 'rejected' && $pay->review_note)<div class="text-danger small mt-1">{{ $pay->review_note }}</div>@endif
            </td>
            <td>
                @if($pay->proof_path)
                    <a href="{{ route('program-payments.proof', $pay->id) }}" target="_blank" rel="noopener"><i class="icofont icofont-attachment"></i> View</a>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            @if($canReview)
            <td class="text-end" style="min-width: 210px;">
                @if($pay->isPending())
                <form method="POST" action="{{ route('program-payments.confirm', $pay->id) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-success"><i class="icofont icofont-check"></i> Confirm</button>
                </form>
                <form method="POST" action="{{ route('program-payments.reject', $pay->id) }}" class="d-inline-flex gap-1 mt-1">
                    @csrf
                    <input type="text" name="review_note" class="form-control form-control-sm" placeholder="Reason" required style="width: 110px;">
                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                </form>
                @endif
            </td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@else
<p class="text-muted mb-0">No payments yet.</p>
@endif
