@php
    $children = $byParent->get($church->id, collect());
    $pastor = optional($church->current_head)->member;
    $accent = $designationColors[$church->designation_id] ?? '#2E5AAC';
@endphp
<li>
    <div class="org-card" style="border-top-color: {{ $accent }}"
         data-church-id="{{ $church->id }}"
         data-name="{{ $church->name }}"
         data-location="{{ $church->physical_location }}"
         data-designation="{{ $church->designation_id }}"
         data-parent="{{ $church->parent_church_id }}"
         data-head="{{ optional($church->current_head)->head_of_unit }}"
         title="Click to edit{{ $church->parent_church_id ? ' · drag onto another church to change who it reports to' : '' }}">
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
        @if($children->count())
            <button type="button" class="org-toggle no-print" title="Show / hide the {{ $children->count() }} church(es) under this one">
                <i class="icofont icofont-simple-down"></i> {{ $children->count() }}
            </button>
        @endif
    </div>

    @if($children->count())
        <ul>
            @foreach($children as $child)
                @include('portal.churches.partials.tree-node', ['church' => $child, 'byParent' => $byParent, 'designationColors' => $designationColors])
            @endforeach
        </ul>
    @endif
</li>
