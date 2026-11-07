<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // One church scope per request (who sees which churches).
        $this->app->singleton(\App\Services\ChurchScope::class);

        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // @menuVisible('pledges.campaigns') ... @endmenuVisible - shows a
        // sidebar menu only to users given it (Security & Settings > Access Control).
        \Illuminate\Support\Facades\Blade::if('menuVisible', fn (string $key) => app(\App\Services\MenuAccess::class)->can(\Illuminate\Support\Facades\Auth::user(), $key));

        // $this->app->bind('GuzzleHttp\Client', function () {
        //     $config['curl'] = [
        //         CURLOPT_SSL_VERIFYPEER => false,
        //         CURLOPT_SSL_VERIFYHOST => false,
        //     ];
        //     return new \GuzzleHttp\Client(['curl' => $config['curl']]);
        // });

        //URL::forceScheme('https');

        $project_title = '| '.Config('constatnts.solution.name');
        View::share('title', $project_title);  
        Paginator::useBootstrap(); 
    }
}
