<?php
namespace AdoreMe\Logger\Helpers;

use AdoreMe\Common\Helpers\ProviderHelper;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RavenHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\WebProcessor;
use AdoreMe\Logger\Models\Monolog\Handler\SlackWebhookHandler;
use Psr\Log\LoggerInterface;
use Raven_Client;
use Psr\Log\LogLevel;

class LogHelper
{
    // Sorted descending by the severity. First ones are the most severe, while the last ones are the least severe.
    const LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];
    const ERROR_LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
    ];
    const ERRORS_WARNING_NOTICE_LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
    ];
    // Attribute name to be used on models.
    const LOGS_ATTRIBUTE_NAME = 'logs';

    protected static $loggers = [];

    /** @var WebProcessor */
    protected static $webProcessorInstance;

    /** @var int */
    protected static $logLineNumber = 0;

    /**
     * Register file handler into monolog instance.
     *
     * @param MonologLogger $monologModel
     * @param string $logFileName The name that will be prepended in the log file. Must have no extension.
     * @param int $minimumLevel
     */
    public static function registerFileHandlerToMonolog(
        MonologLogger $monologModel,
        string $logFileName = 'laravel',
        int $minimumLevel = MonologLogger::DEBUG
    ) {
        // Do nothing if file handler is not enabled.
        if (ProviderHelper::env('LOG_HANDLER_FILE_ENABLED', true) == false) {
            return;
        }

        $filename = ProviderHelper::storagePath()
            . DIRECTORY_SEPARATOR . 'logs'
            . DIRECTORY_SEPARATOR . $logFileName
            . '.log';

        $handler = new RotatingFileHandler(
            $filename,
            0,
            $minimumLevel
        );

        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $handler->pushProcessor(self::formatExceptionContextFromRecordProcessorCallable(true, true));

        $monologModel->pushHandler($handler);
    }

    /**
     * Register sentry into monolog instance.
     *
     * @param MonologLogger $monologModel
     * @param int $minimumLevel
     */
    public static function registerSentryToMonolog(
        MonologLogger $monologModel,
        int $minimumLevel = MonologLogger::ERROR
    ) {
        // Do nothing if sentry is not enabled nor registered.
        if (
            ProviderHelper::env('LOG_HANDLER_SENTRY_ENABLED', false) == false
            || ProviderHelper::app()->bound('sentry') == false
        ) {
            return;
        }

        /** @var Raven_Client $sentry */
        $sentry  = ProviderHelper::app('sentry');
        $handler = new RavenHandler(
            $sentry,
            $minimumLevel
        );

        $handler->setFormatter(new LineFormatter("%message%\n"));
        $handler->pushProcessor(self::getWebProcessor());
        $handler->pushProcessor(self::removeStackTraceFromMessageProcessorCallable());

        $monologModel->pushHandler($handler);
    }

    /**
     * Register udp syslog into monolog instance.
     *
     * @param MonologLogger $monologModel
     * @param int $minimumLevel
     */
    public static function registerUdpSyslogToMonolog(
        MonologLogger $monologModel,
        int $minimumLevel = MonologLogger::DEBUG
    ) {
        /* Logstash config example:
        input {
            udp {
                port => 5568
                type => "syslog_5424line"
                buffer_size => 65536
            }
        }
        filter {
            if [type] == "syslog_5424line" {
                grok {
                      match => { "message" => "%{SYSLOG5424LINE}" }
                }
                json {
                    source => "syslog5424_msg"
                }
                mutate {
                    remove_field => [ "syslog5424_proc", "syslog5424_msgid", "syslog5424_pri", "syslog5424_sd", "syslog5424_app", "syslog5424_ver", "syslog5424_msg" ]
                }
            }
        }
        output {
            elasticsearch {
                hosts => "127.0.0.1:9200"
            }
        }
        */

        // Do nothing if the syslog udp is not enabled.
        if (ProviderHelper::env('LOG_HANDLER_SYSLOG_UDP_ENABLED', false) == false) {
            return;
        }

        $handler = new SyslogUdpHandler(
            ProviderHelper::env('LOG_HANDLER_SYSLOG_UDP_HOST', '127.0.0.1'),
            ProviderHelper::env('LOG_HANDLER_SYSLOG_UDP_PORT', '514'),
            LOG_USER,
            $minimumLevel
        );

        $handler->setFormatter(
            new LogstashFormatter(
                'nawe',
                null,
                null,
                'context_'
            )
        );
        $handler->pushProcessor(self::getWebProcessor());
        $handler->pushProcessor(self::removeStackTraceFromMessageProcessorCallable());
        $handler->pushProcessor(self::formatExceptionContextFromRecordProcessorCallable(true));
        $handler->pushProcessor(self::addLogLineNumberProcessorCallable());

        $monologModel->pushHandler($handler);
    }

    /**
     * Register syslog logstash into monolog instance.
     *
     * @param MonologLogger $monologModel
     * @param int $minimumLevel
     */
    public static function registerSyslogToMonolog(
        MonologLogger $monologModel,
        int $minimumLevel = MonologLogger::DEBUG
    ) {
        /* Logstash config example, green.adoreme.com compatible with syslog existing filters.
        input {
            file {
                type => "syslog"
                path => [ "/var/log/syslog" ]
            }
        }
        filter {
            if [type] == "syslog" {
                grok {
                    match => { "message" => "%{SYSLOGTIMESTAMP:syslog_timestamp} %{SYSLOGHOST:syslog_hostname} %{DATA:syslog_program}(?:\[%{POSINT:syslog_pid}\])?: %{GREEDYDATA:syslog_message}" }
                }
                syslog_pri { }
                date {
                    match => [ "syslog_timestamp", "MMM  d HH:mm:ss", "MMM dd HH:mm:ss" ]
                }
                if !("_grokparsefailure" in [tags]) {
                    mutate {
                        replace => [ "@host", "%{syslog_hostname}" ]
                        replace => [ "@message", "%{syslog_message}" ]
                    }
                }
                if [syslog_program] == "nawe_syslog_json" { #this if, is new, comparing to configuration from green.adoreme.com
                    json {
                        source => "syslog_message"
                    }
                }
                mutate {
                    remove_field => [ "syslog_hostname", "syslog_message", "syslog_timestamp" ]
                    replace => [ "@time", "%{syslog_timestamp}" ]
                }
            }
        }
        */

        // Do nothing if the syslog is not enabled.
        if (ProviderHelper::env('LOG_HANDLER_SYSLOG_ENABLED', false) == false) {
            return;
        }

        $handler = new SyslogHandler(
            'nawe_syslog_json',
            LOG_USER,
            $minimumLevel
        );

        $handler->setFormatter(
            new LogstashFormatter(
                'nawe',
                null,
                null,
                'context_'
            )
        );
        $handler->pushProcessor(self::getWebProcessor());
        $handler->pushProcessor(self::removeStackTraceFromMessageProcessorCallable());
        $handler->pushProcessor(self::formatExceptionContextFromRecordProcessorCallable(true));
        $handler->pushProcessor(self::addLogLineNumberProcessorCallable());

        $monologModel->pushHandler($handler);
    }

    /**
     * Register slack webhook handler into monolog instance.
     *
     * @param MonologLogger $monologModel
     * @param int $minimumLevel
     */
    public static function registerSlackToMonolog(
        MonologLogger $monologModel,
        int $minimumLevel = MonologLogger::ERROR
    ) {
        // Do nothing if the slack log is not enabled.
        if (ProviderHelper::env('LOG_HANDLER_SLACK_ENABLED', false) == false) {
            return;
        }

        $handler = new SlackWebhookHandler(
            ProviderHelper::env('LOG_HANDLER_SLACK_WEBHOOK_URL'),
            ProviderHelper::env('LOG_HANDLER_SLACK_CHANNEL'),
            ProviderHelper::env('LOG_HANDLER_SLACK_USERNAME'),
            true,
            ProviderHelper::env('LOG_HANDLER_SLACK_ICON_EMOJI'),
            false,
            true,
            $minimumLevel,
            true,
            ProviderHelper::env('LOG_HANDLER_SLACK_QUEUE_NAME', 'slack_logger_queue'),
            ProviderHelper::env('LOG_HANDLER_SLACK_QUEUE_DRIVER', ProviderHelper::env('QUEUE_DRIVER', 'default'))
        );

        $handler->setFormatter(new LineFormatter("%message%"));
        $handler->pushProcessor(
            self::getCustomWebProcessor(
                [
                    'url',
                    'http_method',
                    'raw_request_content',
                    'referrer',
                    'ip',
                    'x_forwarded_for',
                    'device_code',
                    'user_agent',
                    'unique_session_id',
                ]
            )
        );
        $handler->pushProcessor(self::removeStackTraceFromMessageProcessorCallable());
        $handler->pushProcessor(self::formatExceptionContextFromRecordProcessorCallable());

        $monologModel->pushHandler($handler);
    }

    /**
     * Create a processor which removes the stack trace from message.
     */
    public static function removeStackTraceFromMessageProcessorCallable()
    {
        return function (array $record) {
            if (
                array_key_exists('message', $record)
                && ! empty ($message = $record['message'])
                && preg_match('/Stack trace\:/i', $message)
            ) {
                $record['message'] = strstr($message, "Stack trace:\n", true) ? : $message;
            }

            return $record;
        };
    }

    /**
     * Create a processor which removes the stack trace from message and the ['context']['exception'] from record.
     *
     * @param bool $addExceptionStackTrace
     * @param bool $replaceMessageWithStackTrace
     * @return \Closure
     */
    public static function formatExceptionContextFromRecordProcessorCallable(
        bool $addExceptionStackTrace = false,
        bool $replaceMessageWithStackTrace = false
    ) {
        return function (array $record) use ($addExceptionStackTrace, $replaceMessageWithStackTrace) {
            if (array_key_exists('context', $record) && array_key_exists('exception', $record['context'])) {
                /** @var \Throwable|\Exception $exception */
                $exception = $record['context']['exception'];
                unset ($record['context']['exception']);

                if (self::isExceptionModel($exception)) {
                    $stackTrace        = (string) $exception;
                    $record['message'] = strstr($stackTrace, "Stack trace:\n", true) ? : '';

                    if ($addExceptionStackTrace && $replaceMessageWithStackTrace) {
                        $record['message'] = $stackTrace;
                    } else if ($addExceptionStackTrace) {
                        $record['extra']['exception'] = $stackTrace;
                    }
                }
            }

            return $record;
        };
    }

    /**
     * Return if the value passed as parameter, is an exception.
     *
     * @param $exception
     * @return bool
     */
    public static function isExceptionModel($exception): bool
    {
        return $exception instanceof \Exception
            || (
                PHP_VERSION_ID >= 70000
                && $exception instanceof \Throwable
            );
    }

    /**
     * Create a processor which adds an incremental line number to the logs, so it be easier to sort.
     */
    public static function addLogLineNumberProcessorCallable()
    {
        return function (array $record) {
            $record['extra']             = $record['extra'] ?? [];
            $record['extra']['log_line'] = ++self::$logLineNumber;

            return $record;
        };
    }

    /**
     * Instantiate a singleton instance of WebProcessor.
     *
     * @return WebProcessor
     */
    public static function getWebProcessor(): WebProcessor
    {
        if (is_null(self::$webProcessorInstance)) {
            self::$webProcessorInstance = self::getCustomWebProcessor();
        }

        return self::$webProcessorInstance;
    }

    /**
     * Instantiate a instance of a custom WebProcessor.
     *
     * @param array $filteredOutFields
     * @return WebProcessor
     */
    public static function getCustomWebProcessor(array $filteredOutFields = []): WebProcessor
    {
        $fields = [
            'url'                 => 'REQUEST_URI',
            'http_method'         => 'REQUEST_METHOD',
            'raw_request_content' => 'CUSTOM_WEB_PROCESSOR_RAW_REQUEST_CONTENT',
            'referrer'            => 'HTTP_REFERER',
            'ip'                  => 'REMOTE_ADDR',
            'x_forwarded_for'     => 'CUSTOM_WEB_PROCESSOR_X_FORWARDED_FOR',
            'device_code'         => 'CUSTOM_WEB_PROCESSOR_DEVICE_CODE',
            'user_agent'          => 'CUSTOM_WEB_PROCESSOR_USER_AGENT',
            'unique_session_id'   => 'CUSTOM_WEB_PROCESSOR_UNIQUE_SESSION_ID',
        ];

        $filtered = array_filter(
            $fields,
            function ($key) use ($filteredOutFields) {
                return ! in_array($key, $filteredOutFields);
            },
            ARRAY_FILTER_USE_KEY
        );

        $_SERVER['CUSTOM_WEB_PROCESSOR_X_FORWARDED_FOR']     = HttpHelper::getXForwardedFor();
        $_SERVER['CUSTOM_WEB_PROCESSOR_DEVICE_CODE']         = HttpHelper::getDeviceCode();
        $_SERVER['CUSTOM_WEB_PROCESSOR_USER_AGENT']          = HttpHelper::getUserAgent();
        $_SERVER['CUSTOM_WEB_PROCESSOR_RAW_REQUEST_CONTENT'] = HttpHelper::getRawRequestContent();
        $_SERVER['CUSTOM_WEB_PROCESSOR_UNIQUE_SESSION_ID']   = md5(
            uniqid(microtime())
            . ($_SERVER['REMOTE_ADDR'] ?? '')
            . ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

        return new WebProcessor(
            $_SERVER,
            $filtered
        );
    }

    /**
     * Log the exception.
     *
     * @param LoggerInterface $logger
     * @param string $level
     * @param \Exception $exception
     * @param array $context
     */
    public static function logException(
        LoggerInterface $logger,
        $level = LogLevel::ERROR,
        \Exception $exception,
        array $context = []
    ) {
        $context['exception'] = $exception;
        $message              = $exception->getMessage();

        $logger->log($level, $message, $context);
    }
}
