<?php
namespace AdoreMe\Common\Http\Controllers;

use AdoreMe\Common\Helpers\HttpHelper;

// Laravel dependency.
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractResourceController extends BaseController
{
    /** @var ResponseFactory */
    protected $response;

    /** @var string */
    protected $remoteIp;

    /** @var string */
    protected $deviceCode;

    /** @var string */
    protected $userAgent;

    /** @var Request */
    protected $request;

    /**
     * AbstractResourceController constructor.
     *
     * @param Request $request
     * @param ResponseFactory $response
     */
    public function __construct(Request $request, ResponseFactory $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * Get the remote ip.
     *
     * @return null|string
     */
    protected function getRemoteIp()
    {
        if (is_null($this->remoteIp)) {
            $this->remoteIp = HttpHelper::getRemoteIp($this->request);
        }

        return $this->remoteIp;
    }

    /**
     * Get the device code.
     *
     * @return string|null
     */
    protected function getDeviceCode()
    {
        if (is_null($this->deviceCode)) {
            $this->deviceCode = HttpHelper::getDeviceCode($this->request);
        }

        return $this->deviceCode;
    }

    /**
     * Get the user agent.
     *
     * @return null|string
     */
    protected function getUserAgent()
    {
        if (is_null($this->userAgent)) {
            $this->userAgent = HttpHelper::getUserAgent($this->request);
        }

        return $this->userAgent;
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithNotFound($message = 'not found'): JsonResponse
    {
        return $this->respondWithJson(
            $this->handleErrorMessage($message, Response::HTTP_NOT_FOUND),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param mixed $message
     * @return JsonResponse
     */
    protected function respondWithCreated($message = 'success'): JsonResponse
    {
        if ($message instanceof Arrayable) {
            $json = $message->toArray();
        } else if (is_array($message)) {
            $json = $message;
        } else {
            $json = ['message' => $message];
        }

        return $this->respondWithJson($json, Response::HTTP_CREATED);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithInternalError($message = 'internal server error'): JsonResponse
    {
        return $this->respondWithJson(
            $this->handleErrorMessage($message, Response::HTTP_INTERNAL_SERVER_ERROR),
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function respondWithBadRequest($message = 'Bad request'): JsonResponse
    {
        return $this->respondWithJson(
            $this->handleErrorMessage($message, Response::HTTP_BAD_REQUEST),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithNoContent($message = 'no content'): JsonResponse
    {
        return $this->respondWithJson(['message' => $message], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithUnprocessableEntity($message = 'unprocessable entity'): JsonResponse
    {
        return $this->respondWithJson(
            $this->handleErrorMessage($message, Response::HTTP_UNPROCESSABLE_ENTITY),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithConflict($message = 'resource conflict'): JsonResponse
    {
        return $this->respondWithJson(
            $this->handleErrorMessage($message, Response::HTTP_CONFLICT),
            Response::HTTP_CONFLICT
        );
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    protected function respondWithJson($data = [], $status = 200, array $headers = [], $options = 0): JsonResponse
    {
        // Inject the headers from headers bag, into response. Make sure the injected headers do not already exist.
        $headers = $headers + HttpHelper::getHeaderBag()->toArray();

        return $this->response->json($data, $status, $headers, $options);
    }

    /**
     * @param $message
     * @param int $httpCode
     * @return array
     */
    protected function handleErrorMessage($message, int $httpCode): array
    {
        return HttpHelper::handleErrorMessage($message, $httpCode);
    }
}
