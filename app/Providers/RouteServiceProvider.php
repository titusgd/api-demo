<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->loadApiRoutes();

        $this->loadWebRoutes();
    }

    private function loadApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(function () {
                $apiRoutes = [
                    'api.php',
                    'api_doc.php',
                    'hr.php',
                    'travel_invoice.php',
                    'kkday.php',
                    'bevis.php',
                    'payment.php',
                    'administration.php',
                    'accounting.php',
                    'ticket.php',
                    'system.php'
                ];
                foreach ($apiRoutes as $route) {
                    require base_path('routes/' . $route);
                }
            });
    }

    private function loadWebRoutes()
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }


    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
