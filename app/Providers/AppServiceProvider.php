<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The app's CSS is a hand-rolled design system, not Tailwind, so
        // Laravel's default pagination views (which assume Tailwind utility
        // classes) would render as unstyled links. Use a matching custom
        // view instead — see resources/views/vendor/pagination/tms.blade.php.
        Paginator::defaultView('vendor.pagination.tms');
        Paginator::defaultSimpleView('vendor.pagination.tms');
    }
}
