<?php
namespace AdoreMe\Common\Helpers;

use Closure;
use Illuminate\Support\Collection;

class ArrayHelper
{
    /**
     * Remove empty keys, or keys with null as value.
     * Example given:
     * [
     *     "discount_stamp" => [
     *         "stamp_image" => "239/stamp_1452703311.png",
     *     ],
     *     "info_box" => [
     *         "special" => null,
     *     ],
     *     "category_image" => null,
     *     "featured_on_block" => null,
     *     "featured_cta" => null,
     *     "something_else" => '',
     * ]
     * Will return:
     * [
     *      "discount_stamp" => [
     *         "stamp_image" => "239/stamp_1452703311.png",
     *      ],
     *      "something_else" => '',
     * ]
     *
     * @param $array
     * @return array
     */
    public static function filterValuesOrKeysWithNullData(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::filterValuesOrKeysWithNullData($value);
            } else if (is_null($value)) {
                unset($array[$key]);
            }

            // Remove empty keys.
            if (is_array($value) && count($array[$key]) == 0) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Generate an array with random data inside.
     *
     * @param int $rows
     * @param int $depth
     * @param int $rowsPerDepth
     * @param int $charsPerKey
     * @param int $charsPerValue
     * @param bool $randomizeKey
     * @return array
     */
    public static function generateRandomArray(
        int $rows,
        int $depth = 1,
        int $rowsPerDepth = 1,
        int $charsPerKey = 10,
        int $charsPerValue = 255,
        bool $randomizeKey = true
    ): array {
        $result = [];

        for ($row = 1; $row <= $rows; $row++) {
            if ($depth == 1) {
                $value = str_random($charsPerValue);
            } else {
                $value = self::generateRandomArray($rowsPerDepth, ($depth - 1));
            }

            if ($randomizeKey) {
                $result[str_random($charsPerKey)] = $value;
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Clone an array and its values, to a new array, to avoid passing by reference,
     * any objects from inside the array.
     *
     * @param array $array
     * @return array
     */
    public static function arrayClone(array $array): array
    {
        $result = [];
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $result[$key] = self::arrayClone($val);
            } else if (is_object($val)) {
                // Because Collection does not have a proper __clone method, the clone is just a copy
                // with references included. To bypass that, we need to arrayClone the Collection items.
                // Basically we clone the objects from collection.
                if ($val instanceof Collection) {
                    $result[$key] = collect(self::arrayClone($val->all()));
                } else {
                    $result[$key] = clone $val;
                }
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * Returns if the given array has any objects or resources inside.
     *
     * @param array $array
     * @return bool
     */
    public static function hasObjectOrResource(array $array): bool
    {
        foreach ($array as $key => $value) {
            if (
                (is_array($value) && self::hasObjectOrResource($value) === true)
                || in_array(gettype($value), ['object', 'resource'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Implode an array by key=value and separator
     *
     * @param array $array
     * @param string $implodeSeparator
     * @param string $keyValueSeparator
     * @param Closure $arrayFlattenCallback
     * @return string
     */
    public static function implodeWithKeyAndValue(
        array $array,
        string $implodeSeparator = ', ',
        string $keyValueSeparator = '=',
        Closure $arrayFlattenCallback = null
    ): string {
        if (is_null($arrayFlattenCallback)) {
            $arrayFlattenCallback = function (string $k, array $v) {
                return $k . '[]=' . implode('&' . $k . '[]=', $v);
            };
        }

        return implode(
            $implodeSeparator,
            array_map(
                function ($v, $k) use ($keyValueSeparator, $arrayFlattenCallback) {
                    if (is_array($v)) {
                        return $arrayFlattenCallback($k, $v);
                    } else {
                        return $k . $keyValueSeparator . $v;
                    }
                },
                $array,
                array_keys($array)
            )
        );
    }

    /**
     * Generate an array of attribute values, from each item of collection.
     * Do note that this function is not recursive, Will return only first level of Collection model.
     * Will not work on models that have the attribute as property. Items should be array, implement array access or
     * be instance of std class. Other kind of items will not be parsed.
     *
     * @param string $attribute
     * @param Collection $collection
     * @return array
     */
    public static function findAttributesFromCollection(string $attribute, Collection $collection): array
    {
        if ($collection->isEmpty()) {
            return [];
        }

        $return = [];
        foreach ($collection as $item) {
            if (is_array($item)) {
                if (! array_key_exists($attribute, $item)) {
                    continue;
                }

                $value = $item[$attribute];
            } else if ($item instanceof \ArrayAccess) {
                if (! $item->offsetExists($attribute)) {
                    continue;
                }

                $value = $item[$attribute];
            } else if ($item instanceof \stdClass) {
                $value = $item->{$attribute};
            } else {
                continue;
            }

            $return[] = $value;
        }

        return array_values($return);
    }
}
