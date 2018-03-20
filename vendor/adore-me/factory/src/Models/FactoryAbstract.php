<?php
namespace AdoreMe\Factory\Models;

use AdoreMe\Common\Helpers\ProviderHelper;
use AdoreMe\Factory\Exceptions\ClassToMakeDoesNotExistsException;
use AdoreMe\Factory\Interfaces\FactoryInterface;
use ReflectionClass;
use ReflectionMethod;

abstract class FactoryAbstract implements FactoryInterface
{
    /** @var ProviderHelper */
    protected static $providerHelper;

    /**
     * @return ProviderHelper
     */
    public static function getProviderHelper(): ProviderHelper
    {
        if (is_null(self::$providerHelper)) {
            self::$providerHelper = ProviderHelper::getSingletonInstance();
        }

        return self::$providerHelper;
    }

    /**
     * @param ProviderHelper $providerHelper
     */
    public static function setProviderHelper(ProviderHelper $providerHelper)
    {
        self::$providerHelper = $providerHelper;
    }

    /**
     * Create class, using reflection.
     *
     * @param string $class
     * @return object
     * @throws ClassToMakeDoesNotExistsException
     */
    protected static function makeClassUsingReflection(string $class)
    {
        // Throw exception if the class does not exists.
        if (! class_exists($class)) {
            throw new ClassToMakeDoesNotExistsException($class . ' does not exists.');
        }

        // If the constructor does not exists, then directly create the new class.
        if (method_exists($class,  '__construct') === false) {
            $classInstance = new $class;

        // Else, use reflection to instantiate required parameters from __construct class.
        } else {
            $reflectionMethod    = new ReflectionMethod($class,  '__construct');
            $constructParameters = $reflectionMethod->getParameters();

            $instanceArguments = [];
            foreach ($constructParameters as $parameter) {
                $instanceArguments[] = self::getProviderHelper()->make($parameter->getClass()->getName());
            }

            $reflectionClass = new ReflectionClass($class);
            $classInstance   = $reflectionClass->newInstanceArgs($instanceArguments);
        }

        return $classInstance;
    }

    /**
     * Make a collection of new models, using received config.
     *
     * @param array $configCollection
     * @return array
     * @throws ClassToMakeDoesNotExistsException
     */
    public static function makeMany(array $configCollection): array
    {
        $return = [];

        foreach ($configCollection as $config) {
            $return[] = static::makeOne($config);
        }

        return $return;
    }
}
