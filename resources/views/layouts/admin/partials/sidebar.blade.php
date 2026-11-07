<header class="main-nav">
    <div class="sidebar-user text-center">
        {{-- <a class="setting-primary" href="javascript:void(0)"><i data-feather="settings"></i></a> --}}
        <img class="img-50 rounded-circle" src="{{asset('assets/images/dashboard/1.png')}}" alt="" />
        <a href="{{ Route('home') }}"> <h6 class="mt-3 f-14 f-w-600">{{ucfirst(Auth::user()->first_name)}}</h6></a>
        <p class="mb-1 font-roboto">
            @forelse(Auth::user()->member?->member_roles ?? [] as $role)
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
                    @menuVisible('summary')
                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/dashboard') || request()->is('v1/summary')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="bar-chart"></i><span>Summary</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/dashboard') || request()->is('v1/summary')) ? 'block' : '' }};">
                            @menuVisible('summary.home')
                            <li><a href="{{route('home')}}" class="{{routeActive('home')}}"> - Summary</a></li>
                            @endmenuVisible
                        </ul>
                    </li>
                    @endmenuVisible

                    @menuVisible('church_setup')
                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/security/*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="settings"></i><span>Church Setup</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1')) ? 'block' : '' }};">
                            @menuVisible('church_setup.churches')
                            <li><a href="{{route('churches-management')}}" class="{{routeActive('churches-management')}}"> - Churches</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.churches_tree')
                            <li><a href="{{route('churches.tree')}}" class="{{routeActive('churches.tree')}}"> - Churches tree</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.members')
                            <li><a href="{{route('member.management')}}" class="{{routeActive('member.management')}}"> - Churches Member</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.member_titles')
                            <li><a href="{{route('member-titles.index')}}" class="{{routeActive('member-titles.index')}}"> - Member Titles</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.duplicates')
                            <li><a href="{{route('duplicates.index')}}" class="{{routeActive('duplicates.index')}}"> - Duplicate Members</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.invitees')
                            <li><a href="{{route('invitees.index')}}" class="{{routeActive('invitees.index')}}"> - New Invitees</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.souls_dashboard')
                            <li><a href="{{route('invitees.dashboard')}}" class="{{routeActive('invitees.dashboard')}}"> - New Souls Dashboard</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.cells')
                            <li><a href="{{route('cell.management')}}" class="{{routeActive('cell.management')}}"> - Cell Management</a></li>
                            @endmenuVisible
                            @menuVisible('church_setup.departments')
                            <li><a href="{{route('department.management')}}" class="{{routeActive('department.management')}}"> - Department Management</a></li>
                            @endmenuVisible


                            
                            {{-- <li><a href="{{route('church-hierarchy')}}" class="{{routeActive('church-hierarchy')}}"> - Church Hierarchy</a></li>
                            <li><a href="{{route('cells-management')}}" class="{{routeActive('cells-management')}}"> - Cells</a></li> --}}
                        </ul>
                    </li>
                    @endmenuVisible   

                    @menuVisible('pledges')
                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/pledges/*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="gift"></i><span>Pledges</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/pledges/*')) ? 'block' : '' }};">
                            @menuVisible('pledges.dashboard')
                            <li><a href="{{route('pledges.dashboard')}}" class="{{routeActive('pledges.dashboard')}}"> - Dashboard</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.campaigns')
                            <li><a href="{{route('pledge-campaigns.index')}}" class="{{routeActive('pledge-campaigns.index')}}"> - Campaigns</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.my_pledges')
                            <li><a href="{{route('my-pledges.browse')}}" class="{{routeActive('my-pledges.browse')}}"> - My Pledges</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.manage')
                            <li><a href="{{route('pledge-management.index')}}" class="{{routeActive('pledge-management.index')}}"> - Record / Manage Pledges</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.contributions')
                            <li><a href="{{route('pledge-contributions.index')}}" class="{{routeActive('pledge-contributions.index')}}"> - Contributions / Fulfillment</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.live')
                            <li><a href="{{route('pledge-live.select')}}" class="{{routeActive('pledge-live.select')}}"> - Live Presentation</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.reports')
                            <li><a href="{{route('pledge-reports.index')}}" class="{{routeActive('pledge-reports.index')}}"> - Reports</a></li>
                            @endmenuVisible
                            @menuVisible('pledges.settings')
                            <li><a href="{{route('pledge-settings.index')}}" class="{{routeActive('pledge-settings.index')}}"> - Settings</a></li>
                            @endmenuVisible
                        </ul>
                    </li>
                    @endmenuVisible

                    @menuVisible('services')
                    <li class="dropdown">
                        <a class="nav-link menu-title {{ request()->is('v1/services*') ? 'active' : '' }}" href="javascript:void(0)"><i data-feather="sun"></i><span>Church Services</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ request()->is('v1/services*') ? 'block' : '' }};">
                            @menuVisible('services.services')
                            <li><a href="{{route('services.index')}}" class="{{routeActive('services.index')}}"> - Services</a></li>
                            @endmenuVisible
                            @menuVisible('services.checkin')
                            <li><a href="{{route('services.checkin')}}" class="{{routeActive('services.checkin')}}"> - Service Check-in</a></li>
                            @endmenuVisible
                            @menuVisible('services.dashboard')
                            <li><a href="{{route('services.dashboard')}}" class="{{routeActive('services.dashboard')}}"> - Services Dashboard</a></li>
                            @endmenuVisible
                            @menuVisible('services.times')
                            <li><a href="{{route('services.times')}}" class="{{routeActive('services.times')}}"> - Service Times</a></li>
                            @endmenuVisible
                            @if(Auth::user()->member)
                            @menuVisible('services.my_qr')
                            <li><a href="{{route('services.my-qr')}}" class="{{routeActive('services.my-qr')}}"> - My Check-in QR</a></li>
                            @endmenuVisible
                            @endif
                        </ul>
                    </li>
                    @endmenuVisible
                    @menuVisible('programs')
                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/programs*') || request()->is('v1/new-souls*') || request()->is('v1/program-reports*') || request()->is('v1/programs-dashboard') || request()->is('v1/programs-settings') || request()->is('v1/program-payments*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="calendar"></i><span>Programs & Attendance</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/programs*') || request()->is('v1/new-souls*') || request()->is('v1/program-reports*') || request()->is('v1/programs-dashboard') || request()->is('v1/programs-settings') || request()->is('v1/programs-sms-templates')) ? 'block' : '' }};">
                            @menuVisible('programs.dashboard')
                            <li><a href="{{route('programs.dashboard')}}" class="{{routeActive('programs.dashboard')}}"> - Dashboard</a></li>
                            @endmenuVisible
                            @menuVisible('programs.programs')
                            <li><a href="{{route('programs.index')}}" class="{{routeActive('programs.index')}}"> - Programs</a></li>
                            @endmenuVisible
                            @menuVisible('programs.attendance')
                            <li><a href="{{route('program-reports.attendance')}}" class="{{routeActive('program-reports.attendance')}}"> - Attendance</a></li>
                            @endmenuVisible
                            @menuVisible('programs.my_registrations')
                            <li><a href="{{route('my-programs.browse')}}" class="{{routeActive('my-programs.browse')}}"> - My Registrations</a></li>
                            @endmenuVisible
                            @menuVisible('programs.new_souls')
                            <li><a href="{{route('new-souls.index')}}" class="{{routeActive('new-souls.index')}}"> - New Souls</a></li>
                            @endmenuVisible
                            @menuVisible('programs.payments')
                            @php $pendingProgramPayments = \App\Models\ProgramPayment::pending()->count(); @endphp
                            <li><a href="{{route('program-payments.index')}}" class="{{routeActive('program-payments.index')}}"> - Payments to Confirm @if($pendingProgramPayments)<span class="badge bg-warning text-dark ms-1">{{ $pendingProgramPayments }}</span>@endif</a></li>
                            @endmenuVisible
                            @menuVisible('programs.reports')
                            <li><a href="{{route('program-reports.index')}}" class="{{routeActive('program-reports.index')}}"> - Reports</a></li>
                            @endmenuVisible
                            @menuVisible('programs.sms')
                            <li><a href="{{route('program-sms.index')}}" class="{{routeActive('program-sms.index')}}"> - SMS Templates</a></li>
                            @endmenuVisible
                            @menuVisible('programs.settings')
                            <li><a href="{{route('program-settings.index')}}" class="{{routeActive('program-settings.index')}}"> - Settings</a></li>
                            @endmenuVisible
                        </ul>
                    </li>
                    @endmenuVisible

                    @menuVisible('security')
                    <li class="dropdown">
                        <a class="nav-link menu-title {{(request()->is('v1/security/*')) ? 'active' : ''}}" href="javascript:void(0)"><i data-feather="settings"></i><span>Security & Settings</span></a>
                        <ul class="nav-submenu menu-content" style="display: {{ (request()->is('v1/security/*')) ? 'block' : '' }};">
                            @menuVisible('security.profile')
                            <li><a href="{{route('security-user-profile')}}" class="{{routeActive('security-user-profile')}}"> - Your Profile</a></li>
                            @endmenuVisible
                            @menuVisible('security.users')
                            <li><a href="{{route('portal-users')}}" class="{{routeActive('portal-users')}}"> - System Users</a></li>
                            @endmenuVisible
                            @menuVisible('security.access_control')
                            <li><a href="{{route('access-control.index')}}" class="{{routeActive('access-control.index')}}"> - Access Control</a></li>
                            @endmenuVisible
                        </ul>
                    </li>
                    @endmenuVisible   
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </div>  
    </nav>
</header>
