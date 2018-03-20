<?php
namespace AdoreMe\Factory\Exceptions;

class InvalidConfigException extends \Exception
{
    public $errors;
    public $className;
    public $config;
}
