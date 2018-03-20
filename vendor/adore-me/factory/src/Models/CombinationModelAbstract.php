<?php
namespace AdoreMe\Factory\Models;

use AdoreMe\Factory\Exceptions\InvalidConfigException;
use AdoreMe\Factory\Exceptions\UnexpectedConditionException;
use AdoreMe\Factory\Exceptions\UnrecognizableConditionTypeException;
use AdoreMe\Factory\Helpers\ConditionHelper;
use AdoreMe\Factory\Helpers\FactoryHelper;
use AdoreMe\Factory\Helpers\OperatorHelper;
use AdoreMe\Factory\Interfaces\ModelInterface;

abstract class CombinationModelAbstract extends ConditionModelAbstract
{
    /** @var string */
    protected $aggregator;

    /** @var array */
    protected $conditions;

    /** @var array */
    protected $defaultedCombinationAttributes = [];

    /** @var string */
    protected $errorMessage = 'combination conditions cannot be validated';

    protected static $configConditions = [
        ConditionHelper::TYPE     => [
            FactoryHelper::VALIDATOR_REQUIRED => true,
            FactoryHelper::VALIDATOR_TYPE     => FactoryHelper::VALIDATOR_TYPE_STRING
        ],
        ConditionHelper::OPERATOR => [
            FactoryHelper::VALIDATOR_REQUIRED => false,
            FactoryHelper::VALIDATOR_TYPE     => FactoryHelper::VALIDATOR_TYPE_STRING,
            FactoryHelper::VALIDATOR_DEFAULT  => OperatorHelper::IS,
            FactoryHelper::VALIDATOR_IN       => OperatorHelper::BOOLEAN_OPERATORS
        ],
        ConditionHelper::VALUE    => [
            FactoryHelper::VALIDATOR_REQUIRED => false,
            FactoryHelper::VALIDATOR_TYPE     => FactoryHelper::VALIDATOR_TYPE_BOOLEAN,
            FactoryHelper::VALIDATOR_DEFAULT  => true
        ]
    ];

    protected static $configCombinations = [
        ConditionHelper::AGGREGATOR => [
            FactoryHelper::VALIDATOR_REQUIRED => false,
            FactoryHelper::VALIDATOR_TYPE     => FactoryHelper::VALIDATOR_TYPE_STRING,
            FactoryHelper::VALIDATOR_DEFAULT  => OperatorHelper::AGGREGATOR_ALL,
            FactoryHelper::VALIDATOR_IN       => OperatorHelper::AGGREGATORS
        ],
        ConditionHelper::CONDITIONS => [
            FactoryHelper::VALIDATOR_REQUIRED       => true,
            FactoryHelper::VALIDATOR_TYPE           => FactoryHelper::VALIDATOR_TYPE_ARRAY,
            FactoryHelper::VALIDATOR_ARRAY_FIELD_IN => [
                FactoryHelper::VALIDATOR_ARRAY_FIELD_IN_FIELD => ConditionHelper::TYPE,
                FactoryHelper::VALIDATOR_ARRAY_FIELD_IN_IN    => []
            ]
        ]
    ];

    /**
     * Set the config.
     *
     * @param array $config
     * @throws InvalidConfigException
     */
    public function setConfig(array $config)
    {
        parent::setConfig($config);

        $this->defaultedCombinationAttributes =
            $this->validateAndSetDefaultConfig($config, static::$configCombinations);

        $this->aggregator = $config[ConditionHelper::AGGREGATOR];
        $this->conditions = $config[ConditionHelper::CONDITIONS];
    }

    /**
     * Get the config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        $config                              = parent::getConfig();
        $config[ConditionHelper::AGGREGATOR] = $this->aggregator;
        $config[ConditionHelper::CONDITIONS] = $this->conditions;

        return $config;
    }

    /**
     * Get the config without defaults. This should be used for persistence.
     *
     * @return array
     */
    public function getConfigWithoutDefaults(): array
    {
        $config = parent::getConfigWithoutDefaults();

        foreach ($this->defaultedCombinationAttributes as $attribute) {
            if (array_key_exists($attribute, $config)) {
                unset ($config[$attribute]);
            }
        }

        return $config;
    }

    /**
     * Validates that the $config has only correct data, by forcing validation
     * for all condition models that can be made using this config.
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function adminValidateAllConfigLevels(): bool
    {
        $conditionsConfig = [];

        foreach ($this->conditions as $conditionConfig) {
            try {
                $conditionModel = $this->conditionFactoryMake($conditionConfig);
                $conditionValid = $conditionModel->adminValidateAllConfigLevels();

                if ($conditionValid === false) {
                    $errors               = [$this->errorMessage . ': please contact an admin.'];
                    $exception            = new InvalidConfigException(print_r($errors, true));
                    $exception->errors    = $errors;
                    $exception->className = static::class;
                    $exception->config    = $conditionConfig;
                    throw $exception;
                }

                $conditionsConfig[] = $conditionModel->getConfig();
            } catch (UnrecognizableConditionTypeException $e) {
                throw new \Exception($this->errorMessage . ': ' . $e->getMessage());
            } catch (InvalidConfigException $e) {
                $errors = [];
                foreach ($e->errors as $error) {
                    $errors[] = $this->errorMessage . ': ' . $error;
                }

                $e->errors = $errors;
                throw $e;
            }
        }

        $this->conditions = $conditionsConfig;

        return parent::adminValidateAllConfigLevels();
    }

    /**
     * Handle the combination, and return the computed value.
     *
     * @param $model
     * @return bool
     * @throws UnexpectedConditionException
     * @throws UnrecognizableConditionTypeException
     */
    protected function executeCombinationHandle($model): bool
    {
        $value  = null;
        $result = false;

        // Write debug data.
        if ($this->isDebugMode()) {
            $microtime = $this->debugBeforeHandle();
        }

        // If there are no conditions, then here is nothing to test. Return true.
        if (empty ($this->conditions)) {
            // Write debug data.
            if ($this->isDebugMode()) {
                $this->logDebug('No conditions set for this class. Returning true.');
            }

            $result = true;
        } // Calculate the case when aggregator is ANY. This means that ANY the received calculated conditions, must be true.
        else if ($this->aggregator == OperatorHelper::AGGREGATOR_ANY) {
            list ($value, $result) = $this->handleAggregatorAny($model);
        } // Calculate the case when aggregator is ALL. This means that ALL of the received calculated conditions, must be true.
        else if ($this->aggregator == OperatorHelper::AGGREGATOR_ALL) {
            list ($value, $result) = $this->handleAggregatorAll($model);
        }

        // Write debug data.
        if ($this->isDebugMode()) {
            /** @var float $microtime */
            $this->debugAfterHandle($value, $result, $microtime);
        }

        return $result;
    }

    /**
     * Calculate the case when aggregator is ANY.
     * This means that ANY the received calculated conditions, must be true.
     *
     * @param $model
     * @return array
     * @throws UnexpectedConditionException
     * @throws UnrecognizableConditionTypeException
     */
    protected function handleAggregatorAny($model): array
    {
        $value = null;

        foreach ($this->conditions as $conditionConfig) {
            $result = $this->makeConditionModelAndReturnOperationResult($conditionConfig, $model);

            // If the combination result is true, then quit the verifications. It is all we need.
            if ($result == true) {
                return [$value, true];
            }
        }

        // If we reached this line, then the result must be false.
        return [$value, false];
    }

    /**
     * Calculate the case when aggregator is ALL.
     * This means that ALL of the received calculated conditions, must be true.
     *
     * @param $model
     * @return array
     * @throws UnexpectedConditionException
     * @throws UnrecognizableConditionTypeException
     */
    protected function handleAggregatorAll($model): array
    {
        $value = null;

        foreach ($this->conditions as $conditionConfig) {
            $result = $this->makeConditionModelAndReturnOperationResult($conditionConfig, $model);

            // If the combination result is false, then quit the verifications. Is clear that it will be false.
            if ($result == false) {
                return [$value, false];
            }
        }

        // If we reached this line, then the result must be true.
        return [$value, true];
    }

    /**
     * Make the condition using config data, and return calculated result.
     *
     * @param array $config
     * @param $model
     * @return bool
     * @throws UnexpectedConditionException
     * @throws UnrecognizableConditionTypeException
     */
    protected function makeConditionModelAndReturnOperationResult(array $config, $model): bool
    {
        // Make the condition class. We don't make the classes before,
        // because it might not be required to test the condition, if some previous
        // conditions fail.
        $condition = $this->conditionFactoryMake($config);
        $condition->inheritLogger($this);

        // Validate the created condition, otherwise throw exception.
        if (! $this->validateConditionInstance($condition)) {
            throw new UnexpectedConditionException(
                'Unexpected condition class: '
                . get_class($condition) . '. expected: '
                . $this->getExpectedConditionInstance()
            );
        }

        // Write debug data.
        if ($this->isDebugMode()) {
            $condition->debugDepth = $this->debugDepth + 1;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return OperatorHelper::compare(
            $condition->handle($model),
            $this->operator,
            $this->value
        );
    }

    /**
     * Create the condition factory model.
     *
     * @param array $config
     * @return ModelInterface|ConditionModelAbstract
     */
    abstract protected function conditionFactoryMake(array $config): ModelInterface;

    /**
     * Validate if the condition created by the factory has the correct instance.
     *
     * @param ModelInterface $condition
     * @return bool
     */
    abstract protected function validateConditionInstance(ModelInterface $condition): bool;

    /**
     * Return the expected condition instance model type.
     *
     * @return string
     */
    abstract protected function getExpectedConditionInstance(): string;

    /**
     * Write debug data.
     *
     * @param string $prependText
     * @return mixed|null
     */
    protected function debugBeforeHandle(string $prependText = '')
    {
        return parent::debugBeforeHandle($this->aggregator . $prependText . ' ');
    }

    /**
     * Write debug data.
     *
     * @return void
     */
    protected function debugConditions()
    {
        $this->logDebug(
            self::$debugSeparator . 'conditions: ' . json_encode(
                array_map(
                    function ($condition) {
                        return $condition[ConditionHelper::TYPE];
                    },
                    $this->conditions
                )
            )
        );
    }
}