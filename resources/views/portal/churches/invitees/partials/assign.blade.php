{{-- Assign an invitee to a church (+ history). Needs $invitee, $allChurches, optional $occurrence. --}}
<form method="POST" action="{{ route('invitees.assign', $invitee->id) }}" class="assign-form">
    @csrf
    @if(!empty($occurrence))<input type="hidden" name="occurrence_id" value="{{ $occurrence->id }}">@endif
    <div class="d-flex gap-1 flex-wrap">
        <select name="church_id" class="form-select form-select-sm" style="min-width:170px;max-width:240px;" required>
            @foreach($allChurches as $c)
                <option value="{{ $c->id }}" @selected($c->id === $invitee->church_id)>{{ strtoupper($c->name) }}{{ $c->physical_location ? ' - ' . \Illuminate\Support\Str::limit($c->physical_location, 25) : '' }}</option>
            @endforeach
        </select>
        <input name="note" class="form-control form-control-sm" style="max-width:170px;" placeholder="Note (optional)">
        <button class="btn btn-sm btn-primary">{{ $invitee->assignments->isEmpty() ? 'Assign' : 'Re-assign' }}</button>
    </div>
</form>
@if($invitee->assignments->isNotEmpty())
    <details class="mt-1">
        <summary class="text-muted" style="font-size:.75rem;cursor:pointer;">History ({{ $invitee->assignments->count() }})</summary>
        <ul class="list-unstyled mb-0 mt-1" style="font-size:.75rem;">
            @foreach($invitee->assignments as $a)
                <li class="mb-1">
                    <strong>{{ optional($a->created_at)->format('d M Y H:i') }}</strong>:
                    {{ optional($a->fromChurch)->name ?? '—' }} &rarr; <strong>{{ optional($a->toChurch)->name }}</strong>
                    by {{ optional($a->assignedBy)->first_name ?? 'System' }}
                    @if($a->note)<br><em>&ldquo;{{ $a->note }}&rdquo;</em>@endif
                </li>
            @endforeach
        </ul>
    </details>
@else
    <div class="text-warning" style="font-size:.75rem;">Not assigned yet</div>
@endif
