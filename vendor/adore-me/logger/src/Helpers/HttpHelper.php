<?php
namespace AdoreMe\Logger\Helpers;

use AdoreMe\Logger\Models\Entry as LogEntry;
use AdoreMe\Logger\Models\Log;

use Illuminate\Support\Collection;

class HttpHelper extends \AdoreMe\Common\Helpers\HttpHelper
{
    /**
     * Handle all error messages. Output them in the same response format
     *
     * @param array|string $message
     * @param integer $httpCode
     *
     * @return array
     */
    public static function handleErrorMessage($message, int $httpCode): array
    {
        if ($message instanceof Log) {
            return self::handleLogErrorMessage($message, $httpCode);
        }

        return parent::handleErrorMessage($message, $httpCode);
    }

    /**
     * Handle messages that are Log.
     *
     * @param Log $log
     * @param int $httpCode
     * @return array
     */
    public static function handleLogErrorMessage(Log $log, int $httpCode): array
    {
        $errors = [];
        foreach (Log::LOG_TYPES as $type) {
            /** @var Collection $logType */
            $logType = $log->getAttribute($type);

            $errors = array_merge($errors, self::getErrorsFromCollection($logType));
        }

        return [
            'error' => [
                'code'    => $httpCode,
                'message' => self::returnHighestLevelMessage($log) ?? reset($errors),
                'errors'  => $errors,
            ],
        ];
    }

    /**
     * Return the highest level message from log.
     *
     * @param Log $log
     * @return string|null
     */
    public static function returnHighestLevelMessage(Log $log)
    {
        $level = null;
        $error = null;
        foreach (Log::LOG_TYPES as $type) {
            /** @var Collection $collection */
            $collection = $log->getAttribute($type);

            foreach ($collection as $item) {
                if (! $item instanceof LogEntry) {
                    continue;
                }

                $itemLevel = array_search($item->level, LogHelper::LEVELS);

                if ($itemLevel === false) {
                    continue;
                }

                if (is_null($level) || $itemLevel < $level) {
                    $error = $item->message;
                    $level = $itemLevel;
                }
            }
        }

        return $error;
    }

    /**
     * Return the errors from a collection.
     *
     * @param Collection $collection
     * @return array
     */
    protected static function getErrorsFromCollection(Collection $collection): array
    {
        $errors = [];
        foreach ($collection as $item) {
            if (is_array($item)) {
                $errors = array_merge($item, $errors);
            } else if ($item instanceof LogEntry) {
                $errors[] = $item->message;
            } else {
                $errors[] = $item;
            }
        }

        return $errors;
    }
}
