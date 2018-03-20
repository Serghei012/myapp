<?php
namespace AdoreMe\Factory\Interfaces;

use AdoreMe\Factory\Exceptions\ClassToMakeDoesNotExistsException;

interface FactoryInterface
{
    /**
     * Make new model of the requested class, using received config.
     *
     * @param array $config
     * @throws ClassToMakeDoesNotExistsException
     */
    public static function makeOne(array $config);

    /**
     * Make a collection of new models, using received config.
     * The payment methods are ordered by priority.
     *
     * @param array $configCollection
     * @return array
     * @throws ClassToMakeDoesNotExistsException
     */
    public static function makeMany(array $configCollection): array;
}
