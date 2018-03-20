<?php
namespace AdoreMe\Logger\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use AdoreMe\Logger\Helpers\LogHelper;
use Monolog\Logger as MonologLogger;

abstract class LoggerProviderAbstract extends ServiceProvider
{
    /**
     * Defines if files will be used to log.
     *
     * @var bool
     */
    protected $registerFileHandler = true;

    /**
     * Define to ignore log levels under defined level, for file handler.
     *
     * @var int
     */
    protected $minimumLevelForFileHandler = MonologLogger::DEBUG;

    /**
     * Defines if sentry will be implemented in monolog as handler.
     *
     * @var bool
     */
    protected $registerSentryHandler = true;

    /**
     * Define to ignore log levels under defined level, for sentry.
     *
     * @var int
     */
    protected $minimumLevelForSentryHandler = MonologLogger::ERROR;

    /**
     * Define if udp syslog will be implemented in monolog as handler.
     *
     * @var bool
     */
    protected $registerUdpSyslogHandler = true;

    /**
     * Define to ignore log levels under defined level, for udp syslog handler.
     *
     * @var int
     */
    protected $minimumLevelForUdpSyslogHandler = MonologLogger::DEBUG;

    /**
     * Define if syslog will be implemented in monolog as handler.
     *
     * @var bool
     */
    protected $registerSyslogHandler = false;

    /**
     * Define to ignore log levels under defined level, for file syslog handler.
     *
     * @var int
     */
    protected $minimumLevelForSyslogHandler = MonologLogger::DEBUG;

    /**
     * Defines if slack will be used to log.
     *
     * @var bool
     */
    protected $registerSlackHandler = false;

    /**
     * Define to ignore log levels under defined level, for file handler.
     *
     * @var int
     */
    protected $minimumLevelForSlackHandler = MonologLogger::ERROR;

    /**
     * Indicates the full class model including namespace, that uses this abstract.
     *
     * @var string
     */
    protected $fullClassLogModel;

    /**
     * Indicates the log name that uses this abstract.
     *
     * @var string
     */
    protected $logName;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Create a new service provider instance.
     *
     * @param Application|\Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        if (is_null($this->logName)) {
            $this->logName = substr(static::class, strrpos(static::class, '\\') + 1);
        }

        if (is_null($this->fullClassLogModel)) {
            $this->fullClassLogModel = __NAMESPACE__ . '\\Models\\Log\\' . $this->logName;
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            $this->fullClassLogModel,
            function () {
                $monologModel = new MonologLogger($this->logName);

                if ($this->registerFileHandler === true) {
                    // Register file handler into this monolog model.
                    LogHelper::registerFileHandlerToMonolog(
                        $monologModel,
                        $this->logName,
                        $this->minimumLevelForFileHandler
                    );
                }

                if ($this->registerSentryHandler === true) {
                    // Register sentry into this monolog model.
                    LogHelper::registerSentryToMonolog($monologModel, $this->minimumLevelForSentryHandler);
                }

                if ($this->registerUdpSyslogHandler === true) {
                    // Register udp syslog into this monolog model.
                    LogHelper::registerUdpSyslogToMonolog($monologModel, $this->minimumLevelForUdpSyslogHandler);
                }

                if ($this->registerSyslogHandler === true) {
                    // Register syslog into this monolog model.
                    LogHelper::registerSyslogToMonolog($monologModel, $this->minimumLevelForSyslogHandler);
                }

                if ($this->registerSlackHandler === true) {
                    // Register slack into this monolog model.
                    LogHelper::registerSlackToMonolog($monologModel, $this->minimumLevelForSlackHandler);
                }
                // Always add the stderr output for errors over WARNING level.
                /**
                 * @var $monologModel Monolog\Logger
                 */
                $monologModel->setHandlers([]);
                $monologModel->pushHandler(
                    new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::WARNING)
                );
                $ip         = env('NODE_IP', '10.101.100.144');
                $port       = env('FLUENTD_PORT', 32225);
                $connection = sprintf('tcp://%s:%s', $ip, $port);

                $socketHandler = new \Monolog\Handler\SocketHandler($connection);
                $socketHandler->setPersistent(true);
                $socketHandler->setFormatter(new \Monolog\Formatter\FluentdFormatter());
                $monologModel->pushHandler(
                    $socketHandler
                );
                $monologModel->pushProcessor(
                    function ($record) {
                        if (! array_key_exists('kubernetes', $record)) {
                            $record['kubernetes'] = ['labels' => []];
                        }
                        $record['kubernetes']['pod_name'] = env('POD_NAME', 'local');
                        $record['kubernetes']['namespace_name'] = env('POD_NAMESPACE','local');
                        $record['kubernetes']['host'] = env('NODE_NAME','local');
                        $record['kubernetes']['labels']['app'] = env('APP_NAME','tap');
                        $record['kubernetes']['labels']['version'] = env('APP_VERSION', '0.0.X');

                        return $record;
                    }
                );

                return new Writer($monologModel, $this->app['events']);
            }
        );
        $this->app->alias($this->fullClassLogModel, $this->logName . '.log');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            $this->fullClassLogModel,
            $this->logName . '.log',
        ];
    }
}
