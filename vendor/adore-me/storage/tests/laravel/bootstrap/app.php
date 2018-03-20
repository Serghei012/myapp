<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = new \stubs\AdoreMe\Common\Application\Laravel(
    realpath(__DIR__ . '/../'),
    laravel\AdoreMe\Storage\Http\Kernel::class,
    laravel\AdoreMe\Storage\Console\Kernel::class,
    laravel\AdoreMe\Storage\Exceptions\Handler::class
);

return $app;
