<?php
namespace laravel\AdoreMe\Library\Fixtures\Providers;

use AdoreMe\Logger\Helpers\LogHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Logger as MonologLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /** @var MonologLogger $monologInstance */
        /** @noinspection PhpUndefinedMethodInspection */
        $monologModel = Log::getMonolog();

        // Register sentry into Log facade monolog model.
        LogHelper::registerSentryToMonolog($monologModel);

        // Register udp syslog into Log facade monolog model.
        LogHelper::registerUdpSyslogToMonolog($monologModel);
    }
}
