<?php
namespace AdoreMe\Logger\Http\Controllers;

use AdoreMe\Logger\Helpers\HttpHelper;

abstract class AbstractResourceController extends \AdoreMe\Common\Http\Controllers\AbstractResourceController
{
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
