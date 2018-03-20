<?php
namespace AdoreMe\Logger\Traits;

use AdoreMe\Common\Helpers\ProviderHelper;
use AdoreMe\Logger\Helpers\LogHelper;
use AdoreMe\Logger\Interfaces\LoggerInterface;
use Illuminate\Container\Container;
use Illuminate\Log\Writer;
use Psr\Log\LoggerTrait as PsrLoggerTrait;
use Psr\Log\LogLevel;

trait LoggerTrait
{
    use PsrLoggerTrait;

    /** @var Writer|null */
    protected $logger;

    /** @var bool */
    protected $debugMode;

    /**
     * Pass the logger and debug mode,
     * from given object, to the current object.
     *
     * @param LoggerInterface $model
     */
    public function inheritLogger(LoggerInterface $model)
    {
        $this
            ->setLogger($model->getLogger())
            ->setDebugMode($model->isDebugMode());
    }

    /**
     * Set the logger.
     *
     * @param Writer|null $logger
     * @return $this
     */
    public function setLogger(Writer $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get the logger.
     *
     * @return null|Writer
     */
    public function getLogger()
    {
        // Set a default logger if does not exists.
        if (is_null($this->logger)) {
            $this->logger = Container::getInstance()->make('DefaultLogger.log');
        }

        return $this->logger;
    }

    /**
     * Enable/disable debug mode.
     *
     * @param bool $value
     * @return $this
     */
    public function setDebugMode(bool $value = true)
    {
        $this->debugMode = $value;

        return $this;
    }

    /**
     * Return if debug mode is enabled or not.
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        if (is_null($this->debugMode)) {
            // Read the value from env.
            $this->debugMode = ProviderHelper::env('APP_DEBUG', false);
        }

        return $this->debugMode;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        // Do not log, if the logger was not set.
        if (! is_object($this->getLogger())) {
            return;
        }

        // If debug mode is false, do not log debugs.
        if ($level == LogLevel::DEBUG && $this->isDebugMode() == false) {
            return;
        }

        // If the message is an exception, then we log the exception.
        if (LogHelper::isExceptionModel($message)) {
            LogHelper::logException($this->getLogger(), $level, $message, $context);

            return;
        }

        $this->getLogger()->log($level, $message, $context);
    }
}
