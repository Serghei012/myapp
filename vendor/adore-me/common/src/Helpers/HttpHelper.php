<?php
namespace AdoreMe\Common\Helpers;

use AdoreMe\Common\Models\HeaderBag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

class HttpHelper
{
    const HEADER_X_FORWARDED_FOR = 'x-forwarded-for';
    const HEADER_DEVICE_CODE     = 'device-code';
    const HEADER_USER_AGENT      = 'user-agent';

    /** @var Request */
    protected static $request;

    /** @var HeaderBag */
    protected static $headerBag;

    /**
     * Get the remote ip, first from header, or else return the first item from getClientIp function from Request.
     *
     * @param Request $request
     * @return string|null
     */
    public static function getRemoteIp(Request $request = null)
    {
        if (is_null($request)) {
            $request = self::getRequest();
        }

        if (empty($remoteId = self::getXForwardedFor($request))) {
            $remoteId = $request->getClientIp();
        }

        // Get the first element from received value if is an array.
        if (is_array($remoteId)) {
            $remoteId = (string) array_first($remoteId);
        }

        return $remoteId;
    }

    /**
     * Get the x forward for value from header, if exists. Return null otherwise.
     *
     * @param Request|null $request
     * @return string|null
     */
    public static function getXForwardedFor(Request $request = null)
    {
        if (is_null($request)) {
            $request = self::getRequest();
        }

        $xForwardedFor = $request->header(self::HEADER_X_FORWARDED_FOR);
        if (empty($xForwardedFor)) {
            return null;
        }

        // Get the first element from received value if is an array.
        if (is_array($xForwardedFor)) {
            $xForwardedFor = (string) array_first($xForwardedFor);
        }

        return $xForwardedFor;
    }

    /**
     * Get the device code, from header.
     *
     * @param Request $request
     * @return null|string
     */
    public static function getDeviceCode(Request $request = null)
    {
        if (is_null($request)) {
            $request = self::getRequest();
        }

        $deviceCode = $request->header(self::HEADER_DEVICE_CODE);

        // Get the first element from received value if is an array.
        if (is_array($deviceCode)) {
            $deviceCode = (string) array_first($deviceCode);
        }

        return $deviceCode;
    }

    /**
     * Get the user agent, from $_SERVER, or from header if does not exists.
     *
     * @param Request $request
     * @return null|string
     */
    public static function getUserAgent(Request $request = null)
    {
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        if (is_null($request)) {
            $request = self::getRequest();
        }

        $userAgent = $request->header(self::HEADER_USER_AGENT);

        // Get the first element from received value if is an array.
        if (is_array($userAgent)) {
            $userAgent = (string) array_first($userAgent);
        }

        return $userAgent;
    }

    /**
     * Return raw request content.
     *
     * @param Request|null $request
     * @return string
     */
    public static function getRawRequestContent(Request $request = null): string
    {
        if (is_null($request)) {
            $request = self::getRequest();
        }

        return (string) $request->getContent();
    }

    /**
     * Return singleton instance of Request.
     *
     * @return Request
     */
    public static function getRequest(): Request
    {
        if (is_null(self::$request)) {
            self::$request = ProviderHelper::make(Request::class);
        }

        return self::$request;
    }

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
        if ($message instanceof MessageBag) {
            return self::handleMessageBagErrorMessage($message, $httpCode);
        }

        if ($message instanceof Collection) {
            return self::handleCollectionErrorMessage($message, $httpCode);
        }

        if (is_string($message)) {
            return self::handleStringErrorMessage($message, $httpCode);
        }

        if (is_array($message)) {
            return self::handleArrayErrorMessage($message, $httpCode);
        }

        return [$message];
    }

    /**
     * Construct the error output array.
     *
     * @param int $httpCode
     * @param string $message
     * @param array $errors
     * @return array
     */
    protected static function constructErrorOutputArray(int $httpCode, string $message, array $errors): array
    {
        return [
            'error' => [
                'code'    => $httpCode,
                'message' => $message,
                'errors'  => $errors,
            ],
        ];
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
                $errors = array_merge($errors, $item);
            } else {
                $errors[] = $item;
            }
        }

        return $errors;
    }

    /**
     * Handle messages that are Collection.
     *
     * @param Collection $messages
     * @param $httpCode
     * @return array
     */
    protected static function handleCollectionErrorMessage(Collection $messages, int $httpCode): array
    {
        $errors = self::getErrorsFromCollection($messages);

        return self::constructErrorOutputArray($httpCode, reset($errors), $errors);
    }

    /**
     * Handle messages that are string
     *
     * @param string $message
     * @param integer $httpCode
     * @return array
     */
    protected static function handleStringErrorMessage(string $message, int $httpCode): array
    {
        return self::constructErrorOutputArray($httpCode, $message, [$message]);
    }

    /**
     * Handle messages that are array
     *
     * @param array $messages
     * @param integer $httpCode
     * @return array
     */
    protected static function handleArrayErrorMessage(array $messages, int $httpCode): array
    {
        $errors = [];
        foreach ($messages as $errorMessages) {
            if (is_array($errorMessages)) {
                $errors = array_merge($errorMessages, $errors);
                continue;
            }
            $errors[] = $errorMessages;
        }

        return self::constructErrorOutputArray($httpCode, reset($errors), $errors);
    }

    /**
     * Handle messages that are object of type message bag.
     *
     * @param MessageBag $message
     * @param int $httpCode
     * @return array
     */
    protected static function handleMessageBagErrorMessage(MessageBag $message, int $httpCode): array
    {
        $errors = $message->all();

        return self::constructErrorOutputArray($httpCode, reset($errors), $errors);
    }

    /**
     * Set the header bag model.
     *
     * @param HeaderBag $headerBag
     */
    public static function setHeaderBag(HeaderBag $headerBag)
    {
        self::$headerBag = $headerBag;
    }

    /**
     * Get the header bag model.
     *
     * @return HeaderBag
     */
    public static function getHeaderBag(): HeaderBag
    {
        // Make the model, if not set.
        if (is_null(self::$headerBag)) {
            self::$headerBag = ProviderHelper::make(HeaderBag::class);
        }

        return self::$headerBag;
    }
}
