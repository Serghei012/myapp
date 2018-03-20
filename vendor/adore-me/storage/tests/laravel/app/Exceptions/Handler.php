<?php
namespace laravel\AdoreMe\Storage\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param  \Exception $e
     * @return void
     * @throws \Exception
     */
    public function report(Exception $e)
    {
    }
}
