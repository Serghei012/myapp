<?php
namespace AdoreMe\Logger\Providers;

class DefaultLogger extends LoggerProviderAbstract
{
    /**
     * Defines if slack will be used to log.
     *
     * @var bool
     */
    protected $registerSlackHandler = false;

    protected $registerFileHandler = false;
    protected $registerUdpSyslogHandler = false;

    /**
     * Indicates the full class model including namespace, that uses this abstract.
     *
     * @var string
     */
    protected $fullClassLogModel = 'AdoreMe\Logger\Models\DefaultLogger';

    /**
     * Indicates the log name that uses this abstract.
     *
     * @var string
     */
    protected $logName = 'DefaultLogger';
}
