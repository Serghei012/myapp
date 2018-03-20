<?php

namespace AdoreMe\MsTest\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModels();
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Registers the class name (~factory) into the IO Container.
     * NOTE: This is split because of php-md, yelling that it had too many lines.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Register models.
     */
    protected function registerModels()
    {
    }

    /**
     * Register repositories.
     */
    protected function registerRepositories()
    {
    }

    /**
     * Register services.
     */
    protected function registerServices()
    {
    }
}
