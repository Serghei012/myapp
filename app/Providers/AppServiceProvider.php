<?php

namespace AdoreMe\MsTest\Providers;

use AdoreMe\Logger\Helpers\LogHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Logger as MonologLogger;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /** @var MonologLogger $monologInstance */
        /** @noinspection PhpUndefinedMethodInspection */
        //$monologModel = Log::getMonolog();
        //
        //// Register sentry into Log facade monolog model.
        //LogHelper::registerSentryToMonolog($monologModel);
        //
        //// Register udp syslog into Log facade monolog model.
        //LogHelper::registerUdpSyslogToMonolog($monologModel);
        //
        //// Load laravel ide helper, only when in local environment.
        //// Run: php artisan ide-helper:generate and php artisan ide-helper:meta
        //if ($this->app->environment() == 'local') {
        //    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        //    $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        //}
    }
}
