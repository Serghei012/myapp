<?php
namespace AdoreMe\Logger\Traits;

use AdoreMe\Common\Models\NonPersistentModel;
use AdoreMe\Logger\Models\Entry as LogEntry;
use AdoreMe\Common\Helpers\ObjectHelper;
use AdoreMe\Logger\Helpers\LogHelper;
use AdoreMe\Logger\Models\Entry;
use AdoreMe\Logger\Models\Log;
use Psr\Log\LoggerTrait as PsrLoggerTrait;
use Illuminate\Support\Collection;
use Psr\Log\LogLevel;

/**
 * @property Log logs
 */
trait LoggerNonPersistentModel
{
    use PsrLoggerTrait;

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function emergency($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, $type);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function alert($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::ALERT, $message, $context, $type);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function critical($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::CRITICAL, $message, $context, $type);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function error($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::ERROR, $message, $context, $type);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function warning($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::WARNING, $message, $context, $type);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function notice($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::NOTICE, $message, $context, $type);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function info($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::INFO, $message, $context, $type);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function debug($message, array $context = [], $type = Log::PERSISTENT)
    {
        $this->log(LogLevel::DEBUG, $message, $context, $type);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function log($level, $message, array $context = [], $type = Log::PERSISTENT)
    {
        /** @var Collection $logType */
        $logType = $this->logs->getAttribute($type);

        $logType->push(LogEntry::staticNewInstance([
            LogEntry::MESSAGE => $message,
            LogEntry::LEVEL   => $level,
            LogEntry::CONTEXT => $context,
        ]));
    }

    /**
     * Return the highest log level from the model.
     *
     * @return null|string
     */
    public function getHighestLogLevel()
    {
        $levels = [];

        foreach (Log::LOG_TYPES as $type) {
            $level = $this->getHighestLogLevelPositionFromCollection($this->logs->getAttribute($type));

            if (is_null($level)) {
                continue;
            }

            $levels[] = $level;
        }

        if (empty($levels)) {
            return null;
        }

        return LogHelper::LEVELS[min($levels)] ?? null;
    }

    /**
     * Return the highest log level position, from the given collection.
     *
     * @param Collection $collection
     * @return null|string
     */
    public function getHighestLogLevelPositionFromCollection(Collection $collection)
    {
        $highestLevelPosition = null;

        /** @var Entry $entry */
        foreach($collection as $entry) {
            $logLevelPosition = array_search($entry->level, LogHelper::LEVELS);

            // Do nothing if somehow the level is not in described levels.
            if ($logLevelPosition === false) {
                continue;
            }

            if (
                is_null($highestLevelPosition)
                || (
                    $highestLevelPosition > $logLevelPosition
                )
            ) {
                $highestLevelPosition = $logLevelPosition;
            }
        }

        return $highestLevelPosition;
    }

    /**
     * Return if the model has any logs considered to be blocking errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        $highestLogLevel = $this->getHighestLogLevel();

        return in_array($highestLogLevel, LogHelper::ERROR_LEVELS)
            ? true
            : false;
    }

    /**
     * Return if the model has any logs considered to be blocking errors.
     *
     * @return bool
     */
    public function hasErrorsWarningOrNotices(): bool
    {
        $highestLogLevel = $this->getHighestLogLevel();

        return in_array($highestLogLevel, LogHelper::ERRORS_WARNING_NOTICE_LEVELS)
            ? true
            : false;
    }

    /**
     * Add an error to the model.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return LoggerNonPersistentModel|NonPersistentModel|static
     */
    public function addError(string $message, array $context = [], $type = Log::PERSISTENT): self
    {
        $this->error($message, $context, $type);

        return $this;
    }

    /**
     * Remove log entry from model, by matching context.
     *
     * @param array $context
     */
    public function removeLogEntryByContext(array $context = [])
    {
        foreach (Log::LOG_TYPES as $type) {
            /** @var Collection $logType */
            $logType = $this->logs->getAttribute($type);

            $this->logs->setAttribute($type, $logType->reject(function ($logEntry) use ($context) {
                /** @var Entry $logEntry */
                return $logEntry->context == $context;
            }));
        }
    }

    /**
     * Get logs attribute.
     *
     * @param $value
     * @return Log
     */
    public function getLogsAttribute($value): Log
    {
        // If the errors attribute is null, cast it into Collection, and save in model.
        if (is_null ($value)) {
            $value = ObjectHelper::castIntoObjectClass([], Log::class);
            /** @noinspection PhpUndefinedFieldInspection */
            $this->attributes[LogHelper::LOGS_ATTRIBUTE_NAME] = $value;
        }

        return $value;
    }

    /**
     * Set logs attribute.
     *
     * @param $value
     * @return LoggerNonPersistentModel|NonPersistentModel|static
     */
    public function setLogsAttribute($value): self
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->attributes[LogHelper::LOGS_ATTRIBUTE_NAME] = ObjectHelper::castIntoObjectClass($value, Log::class);

        return $this;
    }
}
