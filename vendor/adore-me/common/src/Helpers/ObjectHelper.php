<?php
namespace AdoreMe\Common\Helpers;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
use AdoreMe\Common\Exceptions\UnexpectedItemObjectTypeInCollectionException;
use AdoreMe\Common\Exceptions\UnexpectedObjectInstanceException;
use AdoreMe\Common\Models\NonPersistentModel;

class ObjectHelper
{
    /**
     * Cast the given value into requested object class, if not already an instance of it.
     *
     * @param mixed $value
     * @param string $class
     * @return mixed
     * @throws UnexpectedObjectInstanceException
     */
    public static function castIntoObjectClass($value, string $class)
    {
        // Return the value, if is already the required class, or a subclass of required class.
        if (
            // Received value is an object.
            is_object($value)
            && (
                // If the received object value class matches the requested class.
                get_class($value) == $class
                // If the received object value is a subclass of requested class.
                || is_subclass_of($value, $class)
                // Reflection is slow, so we want to avoid it.
            )
        ) {
            return $value;

        // Received value is an object, but is not of the requested class.
        } else if (is_object($value)) {
            throw new UnexpectedObjectInstanceException(
                'Expected object to be instance of "' . $class . '", "' . get_class($value) . '" received.'
            );
        }

        // Transform the value into requested class.
        $reflectionClass = new \ReflectionClass($class);

        // If the class is non persistent model or extends it, we can use static old instance.
        // Reflection is used instead of "is_subclass_of" because we need to determine the object type,
        // before instantiating it.
        if (
            $class == NonPersistentModel::class
            || $reflectionClass->isSubclassOf(NonPersistentModel::class)
            || $reflectionClass->hasMethod('staticOldInstance')
        ) {
            /** @var $class NonPersistentModel */
            return $class::staticOldInstance((array) $value);

        // If the class is an instance of eloquent model.
        } else if ($reflectionClass->isSubclassOf(EloquentModel::class)) {
            /** @var $model EloquentModel */
            $model = new $class();

            return $model->newFromBuilder($value);
        }

        // Otherwise create a new class instance of the requested class, and pass it as parameter into constructor.
        return new $class($value);
    }

    /**
     * Cast the given value into Collection, if not already.
     * Reason why we don't use directly Collection::make() is that the value is checked if is Collection already,
     * and returns it, without casting.
     *
     * @param mixed $value
     * @return Collection
     */
    public static function castIntoCollection($value): Collection
    {
        // Return the value, if is already an Collection.
        if ($value instanceof Collection) {
            return $value;
        }

        // Transform into Collection
        if (is_null($value)) {
            return collect([]);
        }

        return collect($value);
    }

    /**
     * Cast the given array into Collection with items of given class.
     *
     * @param mixed $collection
     * @param string $class
     * @return Collection
     * @throws UnexpectedItemObjectTypeInCollectionException
     * @throws UnexpectedObjectInstanceException
     */
    public static function castIntoCollectionOf($collection, string $class): Collection
    {
        // Check if the value is already an Collection. If is, then check that all items from it are of requested class.
        if (is_object($collection)) {

            // We already have an collection of requested items. All good, return the collection.
            if (
                $collection instanceof Collection
                && self::throwExceptionIfNotCollectionOf($collection, $class)
            ) {
                return $collection;
            }

            // Received value is an object, but is not of the requested class.
            throw new UnexpectedObjectInstanceException(
                'Expected object to be instance of "'
                . Collection::class
                . '", "'
                . get_class($collection)
                . '" received.'
            );
        }

        // Make sure the collection is an array by force casting it into one.
        $collection = (array) $collection;

        $return = [];
        foreach ($collection as $key => $value) {
            $return[$key] = self::castIntoObjectClass($value, $class);
        }

        return collect($return);
    }

    /**
     * Return if the value is an Collection with items of given class.
     *
     * @param Collection $collection $value
     * @param string $class
     * @return bool
     * @throws UnexpectedItemObjectTypeInCollectionException
     */
    public static function throwExceptionIfNotCollectionOf(Collection $collection, string $class): bool
    {
        foreach ($collection as $item) {
            // If the received item is not an object, from start the collection has failed.
            if (! is_object($item)) {
                throw new UnexpectedItemObjectTypeInCollectionException(
                    'Expected items from collection to be instance of "'
                    . $class
                    . '", "'
                    . gettype($item)
                    . '" received.'
                );
            }

            if (
                // If the received object value class matches the requested class.
                get_class($item) == $class
                // If the received object value is a subclass of requested class.
                || is_subclass_of($item, $class)
            ) {
                continue;
            }

            throw new UnexpectedItemObjectTypeInCollectionException(
                'Expected items from collection to be instance of "'
                . $class
                . '", "'
                . get_class($item)
                . '" received.'
            );
        }

        return true;
    }

    /**
     * Create an unique identifier for given methodName and arguments.
     *
     * @param string $methodName
     * @param array $arguments
     * @param string $prefix
     * @param string $glue
     * @param string $queryGlue
     * @param string $argumentsGlue
     * @param string $keyValueSeparator
     * @return string
     */
    public static function constructIdentifierForMethodAndArguments(
        string $methodName,
        array $arguments = [],
        string $prefix = '',
        string $glue = ':',
        string $queryGlue = '?',
        string $argumentsGlue = '&',
        string $keyValueSeparator = '='
    ): string {
        $flattenArguments = ArrayHelper::implodeWithKeyAndValue(
            $arguments,
            $argumentsGlue,
            $keyValueSeparator,
            function (string $k, array $v) use ($keyValueSeparator) {
                return $k . $keyValueSeparator . '[' . implode(';', $v) . ']';
            }
        );

        return trim(
            $prefix . $glue . $methodName . $queryGlue . $flattenArguments,
            $queryGlue . $argumentsGlue . $glue
        );
    }
}
