<div>
    <div class="list-group">
        <span class="list-group-item list-group-item-action flex-column align-items-start active" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">Payments</h5>
            </div>
        </span>

        @if(count($payments['today']) > 0)
        @foreach ($payments['today'] as $today) 
        <span class="list-group-item list-group-item-action flex-column align-items-start" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $today['qnty'] }}</h6>
                <small class="text-muted"><span class="badge badge-{{ $today['status'] == 'Full Paid' ? 'success' : 'danger' }}">{{ $today['status'] }} (Today)</span> </small>
            </div>
          <small class="text-muted">TZS {{ number_format($today['total'],2, '.' ,',') }}</small>
        </span>
        @endforeach
        @endif

        @if(count($payments['thisMonth']) > 0)
        @foreach ($payments['thisMonth'] as $thisMonth) 
        <span class="list-group-item list-group-item-action flex-column align-items-start" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $thisMonth['qnty'] }}</h6>
                <small class="text-muted"><span class="badge badge-{{ $thisMonth['status'] == 'Full Paid' ? 'success' : 'danger' }}">{{ $thisMonth['status'] }} ({{ date('M, y')}})</span> </small>
            </div>
          <small class="text-muted">TZS {{ number_format($thisMonth['total'],2, '.' ,',') }}</small>
        </span>
        @endforeach
        @endif


        @if(count($payments['thisYear']) > 0)
        @foreach ($payments['thisYear'] as $thisYear) 
        <span class="list-group-item list-group-item-action flex-column align-items-start" href="javascript:void(0)" data-bs-original-title="" title="">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $thisYear['qnty'] }}</h6>
                <small class="text-muted"><span class="badge badge-{{ $thisYear['status'] == 'Full Paid' ? 'success' : 'danger' }}">{{ $thisYear['status'] }} ({{ date('Y')}})</span> </small>
            </div>
          <small class="text-muted">TZS {{ number_format($thisYear['total'],2, '.' ,',') }}</small>
        </span>
        @endforeach
        @endif

    </div>
</div>
