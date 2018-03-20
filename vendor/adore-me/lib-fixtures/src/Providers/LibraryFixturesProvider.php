<?php
namespace AdoreMe\Library\Fixtures\Providers;

use AdoreMe\Library\Fixtures\Services\LibraryFixturesService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class LibraryFixturesProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Bind the service only if environment is not production.
        if ($this->app->environment() != 'production') {
            // Define the routes for this library.
            /** @noinspection PhpUndefinedMethodInspection */
            Route::prefix('tools/fixtures')
                ->namespace('AdoreMe\Library\Fixtures\Http\Controllers')
                ->group(__DIR__ . '/../../routes/tools_fixtures.php');

            // Load the translation file, for this library.
            $this->loadTranslationsFrom(realpath(__DIR__ . '/../../resources/lang'), 'lib-fixtures');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the service only if environment is not production.
        if ($this->app->environment() != 'production') {
            $this->app->singleton(
                LibraryFixturesService::class,
                function (Application $app) {
                    /** @var ValidationFactory $validationFactory */
                    $validationFactory = $app->make(ValidationFactory::class);
                    $service           = new LibraryFixturesService($app, $validationFactory);

                    return $service;
                }
            );
        }
    }

    /**
     * Registers the class name (~factory) into the IO Container
     *
     * @return array
     */
    public function provides()
    {
        // Bind the service only if environment is not production.
        if ($this->app->environment() != 'production') {
            return [
                LibraryFixturesService::class,
            ];
        }

        return [];
    }
}
