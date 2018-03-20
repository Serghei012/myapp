<?php
namespace AdoreMe\Factory\Helpers;

class FactoryHelper
{
    // Validation fields.
    const VALIDATOR_REQUIRED           = 'required';
    const VALIDATOR_DEFAULT            = 'default';
    const VALIDATOR_TYPE               = 'type';
    const VALIDATOR_LT                 = 'lt';
    const VALIDATOR_LTE                = 'lte';
    const VALIDATOR_GT                 = 'gt';
    const VALIDATOR_GTE                = 'gte';
    const VALIDATOR_IN                 = 'in';
    const VALIDATOR_IN_KEY             = 'in_key';
    const VALIDATOR_TO_CENTS           = 'to_cents';
    const VALIDATOR_ALLOW_EMPTY        = 'allow_empty';
    const VALIDATOR_ARRAY_FIELD_IN     = 'array_field_in';
    const VALIDATOR_ARRAY_KEY_VALUE_IN = 'array_key_value_in';

    // Fields for array_field_in.
    const VALIDATOR_ARRAY_FIELD_IN_FIELD = 'field';
    const VALIDATOR_ARRAY_FIELD_IN_IN    = 'in';

    // Fields for type.
    const VALIDATOR_TYPE_NULL    = 'NULL';
    const VALIDATOR_TYPE_BOOLEAN = 'boolean';
    const VALIDATOR_TYPE_ARRAY   = 'array';
    const VALIDATOR_TYPE_STRING  = 'string';
    const VALIDATOR_TYPE_INTEGER = 'integer';
    const VALIDATOR_TYPE_FLOAT   = 'double';
}
