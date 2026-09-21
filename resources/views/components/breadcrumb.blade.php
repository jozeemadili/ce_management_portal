<div class="container-fluid">
    <div class="page-header">
      <div class="row">
        <div class="col-lg-6">
          {{ $breadcrumb_title ?? '' }}
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{Config('custom.constants.solution.name')}}</a></li>
              {{ $slot ?? ''}}
          </ol>
        </div>
        <div class="col-lg-6">
          <!-- Bookmark Start-->
          <div class="bookmark">
            <ul>
               {{ $breadcrumb_action_buttons ?? ''  }}
              {{-- <li><a href="javascript:void(0)" data-container="body" data-bs-toggle="popover" data-placement="top" title="" data-original-title="Tables"><i data-feather="printer"></i></a></li> --}}
            </ul>
          </div>
          <!-- Bookmark Ends-->
        </div>
      </div>
    </div>
</div>