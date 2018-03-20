<?php
namespace AdoreMe\Common\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Uri;

trait HttpClientTrait
{
    /** @var Client */
    protected $httpClientTraitClient;

    /** @var string */
    protected $httpClientTraitServiceName;

    /** @var bool */
    protected $httpClientTraitCanLogError;

    /**
     * Init the trait.
     *
     * @param Client $client This http client should be already instantiated with the base url.
     * @param string $logContextIdentifier Useful for creating error log context.
     * @param bool $canLogError When true, then the LoggerTrait must be used by the class that implements this trait.
     */
    protected function initHttpClientTrait(
        Client $client,
        string $logContextIdentifier,
        bool $canLogError = true
    ) {
        $this->httpClientTraitClient      = $client;
        $this->httpClientTraitServiceName = $logContextIdentifier;
        $this->httpClientTraitCanLogError = $canLogError;
    }

    /**
     * Do a get request and retry if the request fails. The error log will be triggered on last try.
     *
     * @param int $tries
     * @param string $uri
     * @param int $timeout
     * @param int $expectedHttpCode
     * @param bool $returnResponse
     * @param bool $logError
     * @param array $additionalLogContext
     * @return array of [0]status bool, [1]response array and [2]response http_code
     */
    protected function getRequestWithRetryOnFail(
        int $tries,
        string $uri,
        int $timeout = 5,
        int $expectedHttpCode = 200,
        bool $returnResponse = true,
        bool $logError = true,
        array $additionalLogContext = []
    ): array {
        return $this->handleRequestWithRetryOnFail(
            $tries,
            'get',
            $uri,
            [],
            $timeout,
            $expectedHttpCode,
            $returnResponse,
            $logError,
            $additionalLogContext
        );
    }

    /**
     * Do a post request and retry if the request fails. The error log will be triggered on last try.
     *
     * @param int $tries
     * @param string $uri
     * @param array $jsonData
     * @param int $timeout
     * @param int $expectedHttpCode
     * @param bool $returnResponse
     * @param bool $logError
     * @param array $additionalLogContext
     * @return array of [0]status bool, [1]response array and [2]response http_code
     */
    protected function postRequestWithRetryOnFail(
        int $tries,
        string $uri,
        array $jsonData,
        int $timeout = 5,
        int $expectedHttpCode = 200,
        bool $returnResponse = true,
        bool $logError = true,
        array $additionalLogContext = []
    ): array {
        return $this->handleRequestWithRetryOnFail(
            $tries,
            'post',
            $uri,
            $jsonData,
            $timeout,
            $expectedHttpCode,
            $returnResponse,
            $logError,
            $additionalLogContext
        );
    }

    /**
     * Get request to microservice, and return response.
     *
     * @param string $uri Must be the relative path to the microservice, eg: /v1/something
     * @param int $timeout
     * @param int $expectedHttpCode
     * @param bool $returnResponse
     * @param bool $logError
     * @param array $additionalLogContext
     * @return array of [0]status bool, [1]response array and [2]response http_code
     */
    protected function getRequest(
        string $uri,
        int $timeout = 5,
        int $expectedHttpCode = 200,
        bool $returnResponse = true,
        bool $logError = true,
        array $additionalLogContext = []
    ): array {
        return $this->handleRequest(
            'get',
            $uri,
            [],
            $timeout,
            $expectedHttpCode,
            $returnResponse,
            $logError,
            $additionalLogContext
        );
    }

    /**
     * Post request to microservice, and return response.
     *
     * @param string $uri Must be the relative path to the microservice, eg: /v1/something
     * @param array $jsonData
     * @param int $timeout
     * @param int $expectedHttpCode
     * @param bool $returnResponse
     * @param bool $logError
     * @param array $additionalLogContext
     * @return array of [0]status bool, [1]response array and [2]response http_code
     */
    protected function postRequest(
        string $uri,
        array $jsonData,
        int $timeout = 5,
        int $expectedHttpCode = 200,
        bool $returnResponse = true,
        bool $logError = true,
        array $additionalLogContext = []
    ): array {
        return $this->handleRequest(
            'post',
            $uri,
            $jsonData,
            $timeout,
            $expectedHttpCode,
            $returnResponse,
            $logError,
            $additionalLogContext
        );
    }

    /**
     * Internal function to handle request with retries.
     *
     * @param int $tries
     * @param string $method
     * @param string $uri
     * @param array $jsonData
     * @param int $timeout
     * @param int $expectedHttpCode
     * @param bool $returnResponse
     * @param bool $logError
     * @param array $additionalLogContext
     * @return array of [0]status bool, [1]response array and [2]response http_code
     * @internal
     */
    protected function handleRequestWithRetryOnFail(
        int $tries,
        string $method,
        string $uri,
        array $jsonData,
        int $timeout,
        int $expectedHttpCode,
        bool $returnResponse,
        bool $logError,
        array $additionalLogContext = []
    ): array {
        $tryCount = 0;
        do {
            ++$tryCount;
            $canLogError = $logError && $tryCount == $tries ? true : false;
            list ($status, $response, $responseHttpCode) = $this->handleRequest(
                $method,
                $uri,
                $jsonData,
                $timeout,
                $expectedHttpCode,
                $returnResponse,
                $canLogError,
                $additionalLogContext
            );
        } while ($tryCount < $tries && $status == false);

        return [$status, $response, $responseHttpCode];
    }

    /**
     * Internal function to handle request, with required try catches.
     *
     * @param string $method
     * @param string $uri
     * @param array $jsonData
     * @param int $timeout
     * @param int $expectedHttpCode
     * @param bool $returnResponse
     * @param bool $logError
     * @param array $additionalLogContext
     * @return array of [0]status bool, [1]response array and [2]response http_code
     * @internal
     */
    protected function handleRequest(
        string $method,
        string $uri,
        array $jsonData,
        int $timeout,
        int $expectedHttpCode,
        bool $returnResponse,
        bool $logError,
        array $additionalLogContext = []
    ): array {
        $responseHttpCode = null;

        try {
            $response         = $this->httpClientTraitClient->request(
                $method,
                $uri,
                ['timeout' => $timeout, 'json' => $jsonData]
            );
            $content          = $response->getBody()->getContents();
            $responseHttpCode = $response->getStatusCode();

            // If the http code is the expected one, then the operation is successful.
            if ($responseHttpCode == $expectedHttpCode) {
                // The return response was not requested. Return that the operation was successful.
                if (! $returnResponse) {
                    return [true, [], $expectedHttpCode];
                }

                $content = json_decode($content, true);

                // If the response is an array, then we have what we needed. Return the result.
                if (is_array($content)) {
                    return [true, $content, $expectedHttpCode];
                } else {
                    $contextMessage = 'Invalid json response';
                }
            } else {
                $contextMessage = 'Received http status: ' . $responseHttpCode;
            }
            $contextResponse = json_encode($response->getBody()->getContents());

            // Do not log the error, if wasn't requested. Useful for retries.
            if (! $logError) {
                return $this->returnRequestFailed($responseHttpCode);
            }
        } catch (\Exception $e) {
            $isGuzzleException = $e instanceof ServerException || $e instanceof ClientException;
            $responseHttpCode  = $responseHttpCode ?? ($isGuzzleException ? $e->getResponse()->getStatusCode() : null);

            // Do not log the error, if wasn't requested. Useful for retries.
            if (! $logError) {
                return $this->returnRequestFailed($responseHttpCode);
            }

            if ($isGuzzleException) {
                $content     = $e->getResponse()->getBody()->getContents();
                $contentJson = json_decode($content, true);
                if (! empty($contentJson) && array_key_exists('message', $contentJson)) {
                    $contextResponse = $contentJson['message'];
                } else {
                    $contextResponse = $e->getResponse()->getBody()->getContents();
                }
            }

            $contextMessage = $e->getMessage();
        }

        $this->logError(
            'Failed ' . $method . ' request',
            $contextMessage  ?? '',
            $contextResponse ?? '',
            $uri,
            $additionalLogContext
        );

        // Return that the request has failed, and we have no data.
        return $this->returnRequestFailed($responseHttpCode);
    }

    /**
     * Return that the request has failed, and we have no data.
     *
     * @param int $responseHttpCode
     * @return array
     */
    protected function returnRequestFailed(int $responseHttpCode = null): array
    {
        return [false, [], $responseHttpCode];
    }

    /**
     * Create log error.
     *
     * @param string $message
     * @param string $contextMessage
     * @param string $contextResponse
     * @param string $contextUri
     * @param array $additionalContext
     */
    protected function logError(
        string $message,
        string $contextMessage,
        string $contextResponse,
        string $contextUri,
        array $additionalContext = []
    ) {
        // Do nothing if the log error was not enabled by trait.
        if (! $this->httpClientTraitCanLogError) {
            return;
        }

        /** @var Uri $baseUri */
        $baseUri = $this->httpClientTraitClient->getConfig('base_uri');

        $context = [
            'message'  => $contextMessage,
            'response' => $contextResponse,
            'uri'      => (string) $baseUri->withPath($contextUri),
        ];

        $content['message'] = $message;
        /** @noinspection PhpUndefinedMethodInspection */
        $this->error(
            $this->httpClientTraitServiceName . ' :: ' . $message,
            $context + $additionalContext
        );
    }
}
