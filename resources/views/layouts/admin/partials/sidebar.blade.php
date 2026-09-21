<header class="main-nav">
    <div class="sidebar-user text-center">
        {{-- <a class="setting-primary" href="javascript:void(0)"><i data-feather="settings"></i></a> --}}
        <img class="img-50 rounded-circle" src="{{asset('assets/images/dashboard/1.png')}}" alt="" />
        <a href="{{ Route('home') }}"> <h6 class="mt-3 f-14 f-w-600">{{ucfirst(Auth::user()->first_name)}}</h6></a>
        <p class="mb-1 font-roboto">
            @forelse(Auth::user()->member?->member_roles as $role)
                <span class="badge bg-info me-1">
                    {{ $role->member_designation->name }}
                </span>
            @empty
                <span class="text-muted">No designation assigned</span>
            @endforelse
        </p>
        
        <p class="mb-0 font-roboto"> <small>{{Auth::user()->Company->name}}</small></p>
        <!-- <p class="mb-0 font-roboto"><small>{{substr(strtoupper(Auth::user()->company->name), 0, 34)}}</small></p> -->
    </div>
    <nav>
        <div class="main-navbar">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="mainnav">
                <ul class="nav-menu custom-scrollbar">
                    <li class="back-btn">
                        <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                    </li>
                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/dashboard') || request()->is('v1/summary')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="bar-chart"></i><span>Summary</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/dashboard') || request()->is('v1/summary')) ? 'block' : '' }};">
                            <li><a href="{{route('home')}}" class="{{routeActive('home')}}"> - Summary</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/security/*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="settings"></i><span>Church Setup</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1')) ? 'block' : '' }};">
                            <li><a href="{{route('churches-management')}}" class="{{routeActive('churches-management')}}"> - Churches</a></li>
                            <li><a href="{{route('churches.tree')}}" class="{{routeActive('churches.tree')}}"> - Churches tree</a></li>
                            <li><a href="{{route('member.management')}}" class="{{routeActive('member.management')}}"> - Churches Member</a></li>
                            <li><a href="{{route('cell.management')}}" class="{{routeActive('cell.management')}}"> - Cell Management</a></li>
                            <li><a href="{{route('department.management')}}" class="{{routeActive('department.management')}}"> - Department Management</a></li>


                            
                            {{-- <li><a href="{{route('church-hierarchy')}}" class="{{routeActive('church-hierarchy')}}"> - Church Hierarchy</a></li>
                            <li><a href="{{route('cells-management')}}" class="{{routeActive('cells-management')}}"> - Cells</a></li> --}}
                        </ul>
                    </li>   

                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/pledges/*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="gift"></i><span>Pledges</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/pledges/*')) ? 'block' : '' }};">
                            <li><a href="{{route('pledges.dashboard')}}" class="{{routeActive('pledges.dashboard')}}"> - Dashboard</a></li>
                            <li><a href="{{route('pledge-campaigns.index')}}" class="{{routeActive('pledge-campaigns.index')}}"> - Campaigns</a></li>
                            <li><a href="{{route('my-pledges.browse')}}" class="{{routeActive('my-pledges.browse')}}"> - My Pledges</a></li>
                            <li><a href="{{route('pledge-management.index')}}" class="{{routeActive('pledge-management.index')}}"> - Record / Manage Pledges</a></li>
                            <li><a href="{{route('pledge-contributions.index')}}" class="{{routeActive('pledge-contributions.index')}}"> - Contributions / Fulfillment</a></li>
                            <li><a href="{{route('pledge-live.select')}}" class="{{routeActive('pledge-live.select')}}"> - Live Presentation</a></li>
                            <li><a href="{{route('pledge-reports.index')}}" class="{{routeActive('pledge-reports.index')}}"> - Reports</a></li>
                            <li><a href="{{route('pledge-settings.index')}}" class="{{routeActive('pledge-settings.index')}}"> - Settings</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/programs*') || request()->is('v1/new-souls*') || request()->is('v1/program-reports*') || request()->is('v1/programs-dashboard') || request()->is('v1/programs-settings')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="calendar"></i><span>Programs & Attendance</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/programs*') || request()->is('v1/new-souls*') || request()->is('v1/program-reports*') || request()->is('v1/programs-dashboard') || request()->is('v1/programs-settings')) ? 'block' : '' }};">
                            <li><a href="{{route('programs.dashboard')}}" class="{{routeActive('programs.dashboard')}}"> - Dashboard</a></li>
                            <li><a href="{{route('programs.index')}}" class="{{routeActive('programs.index')}}"> - Programs</a></li>
                            <li><a href="{{route('programs.index')}}?classification=recurring" class="{{ request()->is('v1/programs') && request('classification')==='recurring' ? 'active' : '' }}"> - Recurring Services</a></li>
                            <li><a href="{{route('program-reports.attendance')}}" class="{{routeActive('program-reports.attendance')}}"> - Attendance</a></li>
                            <li><a href="{{route('my-programs.browse')}}" class="{{routeActive('my-programs.browse')}}"> - My Registrations</a></li>
                            <li><a href="{{route('new-souls.index')}}" class="{{routeActive('new-souls.index')}}"> - New Souls</a></li>
                            <li><a href="{{route('program-reports.index')}}" class="{{routeActive('program-reports.index')}}"> - Reports</a></li>
                            <li><a href="{{route('program-settings.index')}}" class="{{routeActive('program-settings.index')}}"> - Settings</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/security/*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="settings"></i><span>Security & Settings</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/security/*')) ? 'block' : '' }};">
                            <li><a href="{{route('security-user-profile')}}" class="{{routeActive('security-user-profile')}}"> - Your Profile</a></li>
                            @if(Auth::user()->role == 'ADMIN')
                                <li><a href="{{route('portal-users')}}" class="{{routeActive('portal-users')}}"> - System Users</a></li>
                            @endif
                        </ul>
                    </li>   
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </div>  
    </nav>
</header>
