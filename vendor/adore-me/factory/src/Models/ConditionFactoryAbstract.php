<?php
namespace AdoreMe\Factory\Models;

use AdoreMe\Factory\Exceptions\UnrecognizableConditionTypeException;
use AdoreMe\Factory\Helpers\ConditionHelper;
use AdoreMe\Factory\Interfaces\ModelInterface;
use AdoreMe\Factory\Interfaces\FactoryInterface;

abstract class ConditionFactoryAbstract extends FactoryAbstract implements FactoryInterface
{
    const TYPES = [];

    /**
     * Return new hydrated model of the requested class.
     *
     * @param array $config
     * @return ModelInterface|ConditionModelAbstract
     * @throws UnrecognizableConditionTypeException
     */
    public static function makeOne(array $config = [])
    {
        $type = $config[ConditionHelper::TYPE] ?? null;

        // Throw exception if the type is not recognized.
        if (
            is_null ($type)
            || ! array_key_exists($type, static::TYPES)
        ) {
            throw new UnrecognizableConditionTypeException(
                'Unrecognizable condition type: ' . json_encode($type)
            );
        }

        $class = static::TYPES[$type];

        /** @var ModelInterface|ConditionModelAbstract $classInstance */
        $classInstance = self::makeClassUsingReflection($class);
        $classInstance
            ->setConfig($config);

        return $classInstance;
    }
}
