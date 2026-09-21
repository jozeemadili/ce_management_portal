@php
    $children = $byParent->get($church->id, collect());
    $pastor = optional($church->current_head)->member;
    $accent = $designationColors[$church->designation_id] ?? '#2E5AAC';
@endphp
<li>
    <div class="org-card" data-church-id="{{ $church->id }}" style="border-top-color: {{ $accent }}">
        <div class="org-card-name">{{ strtoupper($church->name) }}</div>
        <span class="org-card-badge" style="color: {{ $accent }}; background: {{ $accent }}1a">
            {{ strtoupper($church->church_designation->name ?? '') }}
        </span>
        <div class="org-card-head">
            <i class="icofont icofont-crown"></i>
            @if($pastor)
                {{ $pastor->first_name }} {{ $pastor->last_name }}
            @else
                <span class="org-card-nohead">No head assigned</span>
            @endif
        </div>
        <div class="org-card-meta">
            <span><i class="icofont icofont-location-pin"></i> {{ \Illuminate\Support\Str::limit($church->physical_location, 20) }}</span>
            <span><i class="icofont icofont-people"></i> {{ $church->members_count ?? 0 }}</span>
        </div>
    </div>

    @if($children->count())
        <ul>
            @foreach($children as $child)
                @include('portal.churches.partials.tree-node', ['church' => $child, 'byParent' => $byParent, 'designationColors' => $designationColors])
            @endforeach
        </ul>
    @endif
</li>
