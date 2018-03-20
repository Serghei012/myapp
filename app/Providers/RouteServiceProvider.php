<?php

namespace AdoreMe\MsTest\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'AdoreMe\MsTest\Http\Controllers';

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapV1ApiRoutes();

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
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "v1" routes for the application.
     *
     * @return void
     */
    protected function mapV1ApiRoutes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Route::prefix('v1')
            ->middleware('api')
            ->namespace($this->namespace . '\V1')
            ->group(base_path('routes/v1.php'));
    }
}
