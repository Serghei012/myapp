<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = new \stubs\AdoreMe\Common\Application\Laravel(
    realpath(__DIR__ . '/../'),
    laravel\AdoreMe\Common\Http\Kernel::class,
    laravel\AdoreMe\Common\Console\Kernel::class,
    laravel\AdoreMe\Common\Exceptions\Handler::class
);

return $app;
