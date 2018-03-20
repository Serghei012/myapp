<?php
namespace AdoreMe\Factory\Helpers;

class OperatorHelper
{
    const AGGREGATOR_ALL = 'all';
    const AGGREGATOR_ANY = 'any';

    const AGGREGATORS = [
        'all' => self::AGGREGATOR_ALL,
        'any' => self::AGGREGATOR_ANY
    ];

    const IS                    = '=';
    const IS_NOT                = '!=';
    const EQUAL_OR_GREATER_THAN = '>=';
    const EQUAL_OR_LESS_THAN    = '<=';
    const GREATER_THAN          = '>';
    const LESS_THAN             = '<';
    const IS_IN                 = 'IS_IN';
    const NOT_IN                = 'NOT_IN';

    const OPERATORS = [
        self::IS,
        self::IS_NOT,
        self::EQUAL_OR_GREATER_THAN,
        self::EQUAL_OR_LESS_THAN,
        self::GREATER_THAN,
        self::LESS_THAN,
        self::IS_IN,
        self::NOT_IN
    ];

    const BOOLEAN_OPERATORS = [
        self::IS,
        self::IS_NOT
    ];

    const NUMERIC_OPERATORS = [
        self::IS,
        self::IS_NOT,
        self::EQUAL_OR_GREATER_THAN,
        self::EQUAL_OR_LESS_THAN,
        self::GREATER_THAN,
        self::LESS_THAN
    ];

    const ARRAY_OPERATORS = [
        self::IS_IN,
        self::NOT_IN
    ];

    /**
     * Compare result with value, using operator.
     *
     * @param $value1
     * @param string|null $operator
     * @param $value2
     * @return bool
     */
    public static function compare(
        $value1,
        string $operator = null,
        $value2
    ): bool
    {
        switch ($operator) {
            case self::IS_NOT:
                return $value1 !== $value2;
                break;

            case self::EQUAL_OR_GREATER_THAN:
                return $value1 >= $value2;
                break;

            case self::EQUAL_OR_LESS_THAN:
                return $value1 <= $value2;
                break;

            case self::GREATER_THAN:
                return $value1 > $value2;
                break;

            case self::LESS_THAN:
                return $value1 <= $value2;
                break;

            case self::IS_IN:
                $resultIsArray = is_array($value1);
                $valueIsArray  = is_array($value2);

                // If value nor result are array, then compare with operator is (=).
                if (! $valueIsArray && ! $resultIsArray) {
                    return self::compare($value1, self::IS, $value2);
                }

                // If value is not array, but the result is, then search the value in result.
                if (! $valueIsArray && $resultIsArray) {
                    return in_array($value2, $value1);
                }

                // If value is array, but the result is not, then search in result in value.
                if ($valueIsArray && ! $resultIsArray) {
                    return in_array($value1, $value2);
                }

                // The last situation, both value and result are array. Use array_intersect.
                return count(array_intersect($value2, $value1));
                break;

            case self::NOT_IN:
                return ! self::compare($value1, self::IS_IN, $value2);
                break;

            default:
            case self::IS:
                return $value1 === $value2;
                break;
        }
    }
}
