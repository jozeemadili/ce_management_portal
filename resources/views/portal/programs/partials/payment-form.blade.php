{{-- Payment form. $action; $maxAmount (what can still be paid); $currency;
     $paymentMethods; $proofRequired (attendee) or optional (staff);
     $idPrefix keeps ids unique when several forms are on one page. --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-2">
        <div class="col-sm-6">
            <label class="form-label" for="{{ $idPrefix }}_amount">Amount ({{ $currency }})</label>
            <input type="number" step="0.01" min="1" max="{{ $maxAmount }}" name="amount" id="{{ $idPrefix }}_amount" class="form-control" value="{{ old('amount', $maxAmount) }}" required>
            <small class="text-muted">Full ({{ number_format($maxAmount) }}) or part of it.</small>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="{{ $idPrefix }}_date">Payment Date</label>
            <input type="date" name="payment_date" id="{{ $idPrefix }}_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="{{ $idPrefix }}_method">Method</label>
            <select name="payment_method" id="{{ $idPrefix }}_method" class="form-control">
                <option value="">-- Select --</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->name }}" @selected(old('payment_method') === $method->name)>{{ $method->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="{{ $idPrefix }}_ref">Reference</label>
            <input type="text" name="payment_reference" id="{{ $idPrefix }}_ref" class="form-control" value="{{ old('payment_reference') }}" placeholder="e.g. M-Pesa code">
        </div>
        <div class="col-12">
            <label class="form-label" for="{{ $idPrefix }}_proof">Proof of Payment @if(!$proofRequired)<span class="text-muted">(optional)</span>@endif</label>
            <input type="file" name="proof" id="{{ $idPrefix }}_proof" class="form-control" accept="image/*,.pdf" @if($proofRequired) required @endif>
            <small class="text-muted">Photo or screenshot of the receipt / M-Pesa message, or a PDF (max 5 MB).</small>
        </div>
        <div class="col-12">
            <label class="form-label" for="{{ $idPrefix }}_notes">Notes</label>
            <textarea name="notes" id="{{ $idPrefix }}_notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>
    <button class="btn btn-success w-100 mt-3"><i class="icofont icofont-money"></i> {{ $submitLabel }}</button>
</form>
