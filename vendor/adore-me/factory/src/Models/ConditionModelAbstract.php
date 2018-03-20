<?php
namespace AdoreMe\Factory\Models;

use AdoreMe\Factory\Helpers\ConditionHelper;
use AdoreMe\Factory\Helpers\FactoryHelper;
use AdoreMe\Factory\Helpers\OperatorHelper;
use AdoreMe\Factory\Interfaces\ModelInterface;

abstract class ConditionModelAbstract extends ModelAbstract implements ModelInterface
{
    /** @var string */
    protected $type;
    /** @var string */
    protected $operator = OperatorHelper::IS;
    /** @var mixed */
    protected $value = true;
    /** @var array */
    protected $defaultedAttributes = [];

    /** @var string */
    protected static $debugSeparator = '    ';

    /** @var int */
    public $debugDepth = 1;

    protected static $configConditions = [
        ConditionHelper::TYPE => [
            FactoryHelper::VALIDATOR_REQUIRED => true,
            FactoryHelper::VALIDATOR_TYPE     => FactoryHelper::VALIDATOR_TYPE_STRING
        ],
        ConditionHelper::OPERATOR => [
            FactoryHelper::VALIDATOR_REQUIRED => false,
            FactoryHelper::VALIDATOR_TYPE     => FactoryHelper::VALIDATOR_TYPE_STRING,
            FactoryHelper::VALIDATOR_DEFAULT  => OperatorHelper::IS,
            FactoryHelper::VALIDATOR_IN       => OperatorHelper::OPERATORS
        ],
        ConditionHelper::VALUE    => [
            FactoryHelper::VALIDATOR_REQUIRED => false,
            FactoryHelper::VALIDATOR_DEFAULT  => true
        ]
    ];

    /**
     * Set the config.
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->defaultedAttributes = $this->validateAndSetDefaultConfig($config, static::$configConditions);

        $this->type     = $config[ConditionHelper::TYPE];
        $this->operator = $config[ConditionHelper::OPERATOR];
        $this->value    = $config[ConditionHelper::VALUE];
    }

    /**
     * Get the config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            ConditionHelper::TYPE     => $this->type,
            ConditionHelper::OPERATOR => $this->operator,
            ConditionHelper::VALUE    => $this->value
        ];
    }

    /**
     * Get the config without defaults. This should be used for persistence.
     *
     * @return array
     */
    public function getConfigWithoutDefaults(): array
    {
        $config = $this->getConfig();

        foreach ($this->defaultedAttributes as $attribute) {
            if (array_key_exists($attribute, $config)) {
                unset ($config[$attribute]);
            }
        }

        return $config;
    }

    /**
     * Handle the condition, and return the computed value.
     * To be called by real handle, where you have put the type hinting for $model.
     *
     * @param mixed $value
     * @return bool
     */
    protected function executeConditionHandle($value): bool
    {
        // Write debug data.
        if ($this->isDebugMode()) {
            $microtime = $this->debugBeforeHandle();
        }

        $result = OperatorHelper::compare($value, $this->operator, $this->value);

        // Write debug data.
        if ($this->isDebugMode()) {
            /** @var float $microtime */
            $this->debugAfterHandle($value, $result, $microtime);
        }

        return $result;
    }

    /**
     * Write in the debug log, the given information.
     *
     * @param string $message
     */
    protected function logDebug(string $message)
    {
        $spaces = str_repeat(self::$debugSeparator, $this->debugDepth - 1);

        $this->debug($spaces . $message);
    }

    /**
     * Write debug data.
     *
     * @param string $prependText
     * @return mixed|null
     */
    protected function debugBeforeHandle(string $prependText = '')
    {
        $this->logDebug(
            $prependText . static::class . ' '
            . $this->operator . ' ' . json_encode($this->value)
        );
        $this->logDebug('{');

        return microtime(true);
    }

    /**
     * Write debug data.
     *
     * @param mixed $calculatedValue
     * @param mixed $calculatedResult
     * @param mixed $microtime
     * @return void
     */
    protected function debugAfterHandle($calculatedValue, $calculatedResult = null, $microtime = null)
    {
        if (is_null($calculatedResult)) {
            $calculatedResult = '';
        } else {
            $calculatedResult = json_encode($calculatedResult);
        }

        $this->logDebug(
            self::$debugSeparator. json_encode($calculatedValue)
            . ' ' . $this->operator . ' ' . json_encode($this->value)
        );
        $this->logDebug(
            '} = ' . $calculatedResult
            . ' ; generated in ' . number_format(microtime(true) - $microtime, 5) . 's'
        );
    }
}
