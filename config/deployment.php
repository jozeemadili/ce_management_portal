<?php

/*
|--------------------------------------------------------------------------
| Deployment (php artisan deploy)
|--------------------------------------------------------------------------
|
| Settings for App\Console\Commands\DeployCommand. See DEPLOYMENT.md.
|
| No secrets belong here. Paths are fixed configuration, never user input;
| the root-owned sudo helper (scripts/deploy/ce-deploy-helper) keeps its
| OWN copy of the paths, baked in at install time, so editing this file can
| never widen what sudo is allowed to do. The deploy command checks the two
| agree before changing anything.
|
*/

return [

    // Git working copy that is pulled, then synchronised to LIVE.
    'git_path' => env('DEPLOY_GIT_PATH', '/var/www/html/ce_management_portal_git'),

    // The LIVE Laravel application (where `php artisan deploy` runs).
    'live_path' => env('DEPLOY_LIVE_PATH', '/var/www/html/event.christembassytanzania.org'),

    'remote' => env('DEPLOY_REMOTE', 'origin'),
    'branch' => env('DEPLOY_BRANCH', 'main'),

    // Never copied from Git and never deleted from LIVE by rsync --delete.
    // (vendor/ is deliberately NOT here: it is committed to Git and must be
    // synchronised.)
    'protected_paths' => [
        '.env',
        '.git/',
        'storage/',
        'public/storage',
        'bootstrap/cache/',
    ],

    // Must be writable by the web server after every deployment.
    'writable_paths' => [
        'storage',
        'bootstrap/cache',
    ],

    // Root-owned helper allowed through sudo (installed by
    // scripts/deploy/install-deploy-helper.sh). It is the ONLY thing the
    // deploy user may run with sudo.
    'sudo_helper' => env('DEPLOY_SUDO_HELPER', '/usr/local/sbin/ce-deploy-helper'),

    // false (recommended): rsync runs as the deploy user, which must own the
    // LIVE code files. true: rsync runs through the sudo helper.
    'rsync_use_sudo' => (bool) env('DEPLOY_RSYNC_USE_SUDO', false),

    // Service names are determined on the server by the helper installer
    // (e.g. php8.2-fpm); these are only used for display and must match.
    // null = ask the helper which service it was installed for.
    'php_fpm_service' => env('DEPLOY_PHP_FPM_SERVICE'),
    'nginx_service' => env('DEPLOY_NGINX_SERVICE', 'nginx'),

    // Caches rebuilt after `optimize:clear`. Inspected for this project:
    //  - config: safe - env() is only used inside config/.
    //  - route: safe - `route:cache` succeeds.
    //  - view: OFF - `view:cache` fails on the unused leftover view
    //    resources/views/livewire/datatables/filters/editable.blade.php
    //    (component [icons.x-circle] does not exist). Views still compile
    //    on first use. Remove/fix that view before turning this on.
    'cache' => [
        'config' => true,
        'route' => true,
        'view' => false,
    ],

    // flock()-based lock: only one deployment at a time.
    'lock_file' => env('DEPLOY_LOCK_FILE', '/tmp/ce_management_portal_deploy.lock'),

    // Seconds.
    'timeouts' => [
        'git' => 300,
        'rsync' => 900,
        'artisan' => 600,
        'sudo' => 300,
    ],

];
