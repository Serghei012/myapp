<?php
namespace laravel\AdoreMe\Library\Fixtures\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Route::middleware('web')
            ->namespace('laravel\AdoreMe\Library\Fixtures\Http\Controllers')
            ->group(base_path('routes/web.php'));
    }
}
