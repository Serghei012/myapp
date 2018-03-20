<?php
namespace AdoreMe\Factory\Models;

use AdoreMe\Factory\Exceptions\InvalidConfigException;
use AdoreMe\Factory\Helpers\FactoryHelper;
use AdoreMe\Logger\Traits\LoggerTrait;
use AdoreMe\Factory\Interfaces\ModelInterface;

abstract class ModelAbstract implements ModelInterface
{
    use LoggerTrait;

    /** @var string */
    protected $errorMessageFormat = ':attribute :message in :class.';

    /**
     * Set the config.
     *
     * @param array $config
     */
    abstract public function setConfig(array $config);

    /**
     * Get the config.
     *
     * @return array
     */
    abstract public function getConfig(): array;

    /**
     * Get the config without defaults. This should be used for persistence.
     *
     * @return array
     */
    public function getConfigWithoutDefaults(): array
    {
        return $this->getConfig();
    }

    /**
     * Lets say we have an config with $config = ['a' => 'test', 'b' => ['config' => 'another_test']]
     * setConfig function can handle the "a" and "b" validation, but the "b" additional config might not be handled,
     * because the "b" was not yet executed, and might not even be executed due to various conditions, or is expensive,
     * and needs to be called only when is really needed.
     *
     * This function is to be used when we want to validate api call with an array of data, and validate that all
     * the config is valid, so we can save into database.
     *
     * This function is never to be called during normal execution, but only for admin purpose validation !.
     *
     * @return bool
     */
    public function adminValidateAllConfigLevels(): bool
    {
        return true;
    }

    /**
     * Set error message format.
     *
     * @param string $format
     */
    public function setErrorMessageFormat(string $format)
    {
        $this->errorMessageFormat = $format;
    }

    /**
     * Get the error message format.
     *
     * @return string
     */
    public function getErrorMessageFormat(): string
    {
        return $this->errorMessageFormat;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getConfig();
    }

    /**
     * Validate received configuration, and set defaults where is the case.
     * Return a list of attributes that were set with default attributes.
     *
     * @param array $config
     * @param array $rules
     * @return array
     * @throws InvalidConfigException
     */
    protected function validateAndSetDefaultConfig(
        array &$config,
        array $rules
    ): array {
        $defaultedAttributes = [];
        $errors              = [];

        // Iterate trough the configuration rules
        foreach ($rules as $attribute => $attributeRules) {
            // Check if item is required; If it's optional, default is defined, and no actual value is provided, use default;
            if (
                array_key_exists(FactoryHelper::VALIDATOR_REQUIRED, $attributeRules)
                && $attributeRules[FactoryHelper::VALIDATOR_REQUIRED] == true
            ) {
                if (! array_key_exists($attribute, $config)) {
                    $errors[] = $this->translateInvalidConfigError($attribute, 'is required');
                    continue;
                }
            }

            // Set the default value, if not already set.
            if (
                array_key_exists(FactoryHelper::VALIDATOR_DEFAULT, $attributeRules)
                && ! array_key_exists($attribute, $config)
            ) {
                $config[$attribute]    = $attributeRules[FactoryHelper::VALIDATOR_DEFAULT];
                $defaultedAttributes[] = $attribute;
            }

            // The item is not required, and has no default. Skipping attribute.
            if (
                array_key_exists(FactoryHelper::VALIDATOR_REQUIRED, $attributeRules)
                && $attributeRules[FactoryHelper::VALIDATOR_REQUIRED] == false
                && ! array_key_exists($attribute, $config)
            ) {
                continue;
            }

            // Check the type of attribute;
            if (array_key_exists(FactoryHelper::VALIDATOR_TYPE, $attributeRules)) {
                $attributeType = gettype($config[$attribute]);
                // By default we consider that the type is an array.
                $typeArray = $attributeRules[FactoryHelper::VALIDATOR_TYPE];

                // If the type is an string, then explode by pipe bar, to transform in array.
                if (is_string($typeArray)) {
                    $typeArray = explode('|', $attributeRules[FactoryHelper::VALIDATOR_TYPE]);
                }

                // If the type is NULL, then no further tests are required. Skip to next rule.
                if (
                    in_array(FactoryHelper::VALIDATOR_TYPE_NULL, $typeArray)
                    && $attributeType == FactoryHelper::VALIDATOR_TYPE_NULL
                ) {
                    continue;
                }

                $wrongType = true;
                foreach ($typeArray as $type) {
                    if ($attributeType != 'object') {
                        if (gettype($config[$attribute]) == $type) {
                            $wrongType = false;
                            break;
                        }
                    } else {
                        if (get_class($config[$attribute]) == $type) {
                            $wrongType = false;
                            break;
                        }
                    }
                }

                if ($wrongType) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be of type "' . json_encode($typeArray) . '"'
                        . ', received type "' . gettype($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute is less than;
            if (array_key_exists(FactoryHelper::VALIDATOR_LT, $attributeRules)) {
                if ($config[$attribute] >= $attributeRules[FactoryHelper::VALIDATOR_LT]) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be smaller than "' . $attributeRules[FactoryHelper::VALIDATOR_LT] . '"'
                        . ', received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute is less or equal to;
            if (array_key_exists(FactoryHelper::VALIDATOR_LTE, $attributeRules)) {
                if ($config[$attribute] > $attributeRules[FactoryHelper::VALIDATOR_LTE]) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be smaller or equal to "' . $attributeRules[FactoryHelper::VALIDATOR_LTE] . '"'
                        . ', received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute is greater than;
            if (array_key_exists(FactoryHelper::VALIDATOR_GT, $attributeRules)) {
                if ($config[$attribute] <= $attributeRules[FactoryHelper::VALIDATOR_GT]) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be greater than "' . $attributeRules[FactoryHelper::VALIDATOR_GT] . '"'
                        . ', received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute is greater or equal to;
            if (array_key_exists(FactoryHelper::VALIDATOR_GTE, $attributeRules)) {
                if ($config[$attribute] < $attributeRules[FactoryHelper::VALIDATOR_GTE]) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be greater or equal to "' . $attributeRules[FactoryHelper::VALIDATOR_GTE] . '"'
                        . ', received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute in array;
            if (array_key_exists(FactoryHelper::VALIDATOR_IN, $attributeRules)) {
                if (! in_array($config[$attribute], $attributeRules[FactoryHelper::VALIDATOR_IN])) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be one of "' . json_encode($attributeRules[FactoryHelper::VALIDATOR_IN]) . '"'
                        . ', received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute in array;
            if (array_key_exists(FactoryHelper::VALIDATOR_IN_KEY, $attributeRules)) {
                if (! in_array($config[$attribute], array_keys($attributeRules[FactoryHelper::VALIDATOR_IN_KEY]))) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should be one of "'
                        . json_encode(array_keys($attributeRules[FactoryHelper::VALIDATOR_IN_KEY])) . '"'
                        . ', received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute is empty;
            if (array_key_exists(FactoryHelper::VALIDATOR_ALLOW_EMPTY, $attributeRules)) {
                if ($attributeRules[FactoryHelper::VALIDATOR_ALLOW_EMPTY] == false && empty ($config[$attribute])) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'should not be empty, received "' . json_encode($config[$attribute]) . '"'
                    );
                }
            }

            // Check if attribute array_value_in array;
            if (array_key_exists(FactoryHelper::VALIDATOR_ARRAY_FIELD_IN, $attributeRules)) {
                if (! is_array($config[$attribute]) || empty ($config[$attribute])) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'must be an non-empty array, received "' . json_encode($config[$attribute]) . '"'
                    );
                } else {
                    $itemField    =
                        $attributeRules[FactoryHelper::VALIDATOR_ARRAY_FIELD_IN][FactoryHelper::VALIDATOR_ARRAY_FIELD_IN_FIELD];
                    $arrayValueIn =
                        $attributeRules[FactoryHelper::VALIDATOR_ARRAY_FIELD_IN][FactoryHelper::VALIDATOR_ARRAY_FIELD_IN_IN];
                    foreach ($config[$attribute] as $item) {
                        if (! is_array($item)) {
                            $errors[] = $this->translateInvalidConfigError(
                                $attribute,
                                'must have be an array of arrays.'
                                . ' Received "' . json_encode($config[$attribute]) . '"'
                            );

                            continue;
                        }

                        if (! array_key_exists($itemField, $item)) {
                            $errors[] = $this->translateInvalidConfigError(
                                $attribute,
                                'must have an key named "' . $itemField . '" and should be one of "' . json_encode(
                                    $arrayValueIn
                                ) . '"'
                                . ', received "' . json_encode($config[$attribute]) . '"'
                            );

                            continue;
                        }

                        if (! in_array($item[$itemField], $arrayValueIn)) {
                            $errors[] = $this->translateInvalidConfigError(
                                $attribute,
                                '"' . $itemField . '" field should be one of "' . json_encode($arrayValueIn) . '"'
                                . ', received "' . json_encode($item[$itemField]) . '"'
                            );
                        }
                    }
                }
            }

            // Check if attribute needs conversion to cents, and if so, multiply by 100;
            if (array_key_exists(FactoryHelper::VALIDATOR_TO_CENTS, $attributeRules)) {
                if (
                    $attributeRules[FactoryHelper::VALIDATOR_TO_CENTS] == true
                    && is_numeric($config[$attribute])
                    && ! is_null($config[$attribute])
                ) {
                    $config[$attribute] = $config[$attribute] * 100;
                };
            }

            // Check attribute key_value_in.
            if (array_key_exists(FactoryHelper::VALIDATOR_ARRAY_KEY_VALUE_IN, $attributeRules)) {
                if (! is_array($config[$attribute]) || empty ($config[$attribute])) {
                    $errors[] = $this->translateInvalidConfigError(
                        $attribute,
                        'must be an non-empty array, received "' . json_encode($config[$attribute]) . '"'
                    );
                } else {
                    $keyValueInRules = $attributeRules[FactoryHelper::VALIDATOR_ARRAY_KEY_VALUE_IN];

                    foreach ($keyValueInRules as $key => $values) {
                        if (! array_key_exists($key, $config[$attribute])) {
                            $errors[] = $this->translateInvalidConfigError(
                                $attribute,
                                '"' . $key . '" field does not exist, should be one of "' . json_encode($values) . '"'
                                . '"'
                            );
                        } else if (! in_array($config[$attribute][$key], $values)) {
                            $errors[] = $this->translateInvalidConfigError(
                                $attribute,
                                '"' . $key . '" field should be one of "' . json_encode($values) . '"'
                                . ', received "' . json_encode($config[$attribute][$key]) . '"'
                            );
                        }
                    }
                }
            }
        }

        // If there are any errors, throw exception;
        if (sizeof($errors)) {
            $exception            = new InvalidConfigException(print_r($errors, true));
            $exception->errors    = $errors;
            $exception->className = static::class;
            $exception->config    = $config;
            throw $exception;
        }

        return $defaultedAttributes;
    }

    /**
     * Translate the error message.
     *
     * @param string $attribute
     * @param string $message
     * @return string
     */
    protected function translateInvalidConfigError(
        string $attribute,
        string $message
    ): string
    {
        $simpleClass = explode('\\', static::class);
        $simpleClass = end($simpleClass);

        return str_replace(
            [
                ':attribute',
                ':message',
                ':class',
                ':simple_class',
            ],
            [
                $attribute,
                $message,
                static::class,
                $simpleClass,
            ],
            $this->errorMessageFormat
        );
    }
}
