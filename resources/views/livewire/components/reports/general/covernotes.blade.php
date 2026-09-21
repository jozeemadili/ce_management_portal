<div>
    <div class="list-group">
        <span class="list-group-item list-group-item-action flex-column align-items-start active" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">Covernotes</h5>
            </div>
        </span>

        @if(count($covernotes['today']) > 0)
        @foreach ($covernotes['today'] as $today) 
        <span class="list-group-item list-group-item-action flex-column align-items-start" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $today['qnty'] }}</h6>
                <small class="text-muted"><span class="badge badge-{{ $today['status'] == 'Accepted' ? 'success' : 'danger' }}">{{ $today['status'] }} (Today)</span> </small>
            </div>
          <small class="text-muted">TZS {{ number_format($today['total'],2, '.' ,',') }}</small>
        </span>
        @endforeach
        @endif

        @if(count($covernotes['thisMonth']) > 0)
        @foreach ($covernotes['thisMonth'] as $thisMonth) 
        <span class="list-group-item list-group-item-action flex-column align-items-start" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $thisMonth['qnty'] }}</h6>
                <small class="text-muted"><span class="badge badge-{{ $thisMonth['status'] == 'Accepted' ? 'success' : 'danger' }}">{{ $thisMonth['status'] }} ({{ date('M, y')}})</span> </small>
            </div>
          <small class="text-muted">TZS {{ number_format($thisMonth['total'],2, '.' ,',') }}</small>
        </span>
        @endforeach
        @endif


        @if(count($covernotes['thisYear']) > 0)
        @foreach ($covernotes['thisYear'] as $thisYear) 
        <span class="list-group-item list-group-item-action flex-column align-items-start" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $thisYear['qnty'] }}</h6>
                <small class="text-muted"><span class="badge badge-{{ $thisYear['status'] == 'Accepted' ? 'success' : 'danger' }}">{{ $thisYear['status'] }} ({{ date('Y')}})</span> </small>
            </div>
          <small class="text-muted">TZS {{ number_format($thisYear['total'],2, '.' ,',') }}</small>
        </span>
        @endforeach
        @endif

    </div>
</div>
