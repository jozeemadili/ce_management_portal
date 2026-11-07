<?php

/*
|--------------------------------------------------------------------------
| Menu access (Security & Settings > Access Control)
|--------------------------------------------------------------------------
|
| Each user sees only the menus given to them (ADMIN users see everything).
| Giving a main menu gives all its sub-menus, including ones added later;
| giving one sub-menu also shows its main menu header.
|
| 'routes' maps route names (wildcards allowed) to the menu key(s) that open
| them - a page is blocked unless the user has ANY of the listed keys, so a
| hidden link can't be opened by typing its URL. Routes not listed here
| (logout, shared look-ups, own-profile actions) need only a login.
|
| Adding a page: add its sub-menu under 'menus', its route(s) under 'routes',
| and wrap the sidebar link in @menuVisible('<key>').
*/

return [

    // Everyone with a login gets these (their data is only their own, or
    // read-only like the list of services).
    'always' => [
        'summary.home',
        'security.profile',
        'pledges.my_pledges',
        'services.my_qr',
        'services.services',
        'programs.my_registrations',
    ],

    // The current pastor / head of a church gets these automatically, for
    // that church and every church under it (App\Services\ChurchScope).
    // A main menu key brings all its sub-menus.
    'heads' => [
        'church_setup',
        'pledges.dashboard',
        'pledges.campaigns',
        'pledges.contributions',
    ],

    'menus' => [
        'summary' => [
            'label' => 'Summary',
            'children' => [
                'summary.home' => 'Summary',
            ],
        ],
        'church_setup' => [
            'label' => 'Church Setup',
            'children' => [
                'church_setup.churches' => 'Churches',
                'church_setup.churches_tree' => 'Churches tree',
                'church_setup.members' => 'Churches Member',
                'church_setup.member_titles' => 'Member Titles',
                'church_setup.duplicates' => 'Duplicate Members',
                'church_setup.invitees' => 'New Invitees',
                'church_setup.souls_dashboard' => 'New Souls Dashboard',
                'church_setup.cells' => 'Cell Management',
                'church_setup.departments' => 'Department Management',
            ],
        ],
        'pledges' => [
            'label' => 'Pledges',
            'children' => [
                'pledges.dashboard' => 'Dashboard',
                'pledges.campaigns' => 'Campaigns',
                'pledges.my_pledges' => 'My Pledges',
                'pledges.manage' => 'Record / Manage Pledges',
                'pledges.contributions' => 'Contributions / Fulfillment',
                'pledges.live' => 'Live Presentation',
                'pledges.reports' => 'Reports',
                'pledges.settings' => 'Settings',
            ],
        ],
        'services' => [
            'label' => 'Church Services',
            'children' => [
                'services.services' => 'Services',
                'services.checkin' => 'Service Check-in',
                'services.dashboard' => 'Services Dashboard',
                'services.times' => 'Service Times',
                'services.my_qr' => 'My Check-in QR',
                'services.manage' => 'Manage services (quick setup, training, activate)',
            ],
        ],
        'programs' => [
            'label' => 'Programs & Attendance',
            'children' => [
                'programs.dashboard' => 'Dashboard',
                'programs.programs' => 'Programs',
                'programs.attendance' => 'Attendance',
                'programs.my_registrations' => 'My Registrations',
                'programs.new_souls' => 'New Souls',
                'programs.payments' => 'Payments to Confirm',
                'programs.reports' => 'Reports',
                'programs.sms' => 'SMS Templates',
                'programs.settings' => 'Settings',
            ],
        ],
        'security' => [
            'label' => 'Security & Settings',
            'children' => [
                'security.profile' => 'Your Profile',
                'security.users' => 'System Users',
                'security.access_control' => 'Access Control',
            ],
        ],
    ],

    'routes' => [
        // Summary
        'home' => 'summary.home',
        'general' => 'summary.home',

        // Church Setup
        'churches-*' => 'church_setup.churches',
        'churches.transfer.history' => 'church_setup.churches',
        'churches.tree' => 'church_setup.churches_tree',
        'churches.by.designation' => ['church_setup.churches', 'church_setup.members'],
        'member.management' => 'church_setup.members',
        'members-export' => 'church_setup.members',
        'members.import*' => 'church_setup.members',
        'members.update' => 'church_setup.members',
        'member-titles.*' => 'church_setup.member_titles',
        'duplicates.*' => 'church_setup.duplicates',
        'invitees.index' => 'church_setup.invitees',
        'invitees.update' => 'church_setup.invitees',
        'invitees.assign' => ['church_setup.invitees', 'church_setup.souls_dashboard', 'services.dashboard'],
        'invitees.make-member' => 'church_setup.invitees',
        'invitees.dashboard' => 'church_setup.souls_dashboard',
        'cell.management' => 'church_setup.cells',
        'cells-export' => 'church_setup.cells',
        'cells.*' => 'church_setup.cells',
        'department.management' => 'church_setup.departments',
        'departments-export' => 'church_setup.departments',
        'departments.*' => 'church_setup.departments',

        // Pledges
        'pledges.dashboard' => 'pledges.dashboard',
        'pledge-campaigns.*' => 'pledges.campaigns',
        'my-pledges.*' => 'pledges.my_pledges',
        'pledge-scan.show' => ['pledges.contributions', 'pledges.manage'],
        'pledge-management.*' => 'pledges.manage',
        'pledge-contributions.*' => 'pledges.contributions',
        'pledge-live.*' => 'pledges.live',
        'pledge-reports.*' => 'pledges.reports',
        'pledge-settings.*' => 'pledges.settings',

        // Church Services
        'services.index' => 'services.services',
        'services.setup' => 'services.manage',
        'services.training.*' => 'services.manage',
        'services.times' => 'services.times',
        'services.times.save' => 'services.times',
        'services.checkin' => 'services.checkin',
        'services.checkin.*' => 'services.checkin',
        'services.members' => 'services.checkin',
        'services.new-souls' => 'services.checkin',
        'services.scan' => 'services.checkin',
        'services.dashboard' => 'services.dashboard',
        'services.followup' => 'services.dashboard',
        'services.report' => ['services.dashboard', 'services.checkin'],
        'services.report.*' => 'services.dashboard',
        'services.my-qr' => 'services.my_qr',

        // Programs & Attendance
        'programs.dashboard' => 'programs.dashboard',
        'programs.index' => 'programs.programs',
        'programs.export' => 'programs.programs',
        'programs.store' => 'programs.programs',
        'programs.update' => 'programs.programs',
        'programs.status' => ['programs.programs', 'services.manage'],
        'programs.show' => ['programs.programs', 'services.services'],
        'programs.checkin-poster' => ['programs.programs', 'services.services'],
        'program-attendance.*' => ['programs.programs', 'programs.attendance'],
        'program-scan.*' => ['programs.attendance', 'programs.programs'],
        'my-programs.*' => 'programs.my_registrations',
        'program-payments.submit' => 'programs.my_registrations',
        'program-payments.proof' => ['programs.payments', 'programs.my_registrations'],
        'program-payments.*' => 'programs.payments',
        'new-souls.*' => 'programs.new_souls',
        'program-reports.attendance*' => 'programs.attendance',
        'program-reports.pdf-attendance' => 'programs.attendance',
        'program-reports.*' => 'programs.reports',
        'program-sms.*' => 'programs.sms',
        'program-settings.*' => 'programs.settings',

        // Security & Settings
        'security-user-profile' => 'security.profile',
        'security-user-change_password' => 'security.profile',
        'portal-users*' => 'security.users',
        'manage-user-profile' => 'security.users',
        'product-status-update' => 'security.users',
        'security-system-*' => 'security.users',
        'access-control.*' => 'security.access_control',
        'properties-reports' => 'security.users', // old template page

        // Mobile app API (routes/api.php, named api.*) - the same menus, so
        // one assignment covers the portal and the app. Not listed (login
        // only): api.dashboard, api.me.phones.* (own numbers).
        'api.me.checkin-qr' => 'services.my_qr',
        'api.services.*' => 'services.checkin',
        'api.invitees.*' => 'church_setup.invitees',
        'api.pledges.members.search' => 'pledges.manage',
        'api.pledges.*' => ['pledges.my_pledges', 'pledges.manage'],
        'api.programs.scan.*' => ['programs.attendance', 'programs.programs'],
        'api.programs.*' => 'programs.my_registrations',
    ],

    // Menus that also control screens of the mobile app (shown on the
    // Access Control page so the admin knows one tick covers both).
    'app' => [
        'church_setup.invitees' => 'Follow-up',
        'pledges.my_pledges' => 'Campaigns, My Pledges, Make a Pledge',
        'pledges.manage' => 'Pledge on behalf of someone',
        'services.checkin' => 'Service Check-in',
        'services.my_qr' => 'My Check-in QR',
        'programs.my_registrations' => 'Events, My Registrations',
        'programs.attendance' => 'Scan Event Registration QR',
        'programs.programs' => 'Scan Event Registration QR',
    ],
];
