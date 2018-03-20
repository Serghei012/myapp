<?php
namespace AdoreMe\Common\Traits;

trait DefaultAttributesTrait
{
    /** @var array */
    protected $defaultAttributesAndValues = [];

    /**
     * Set an default value for the given key.
     * These values will not be persisted.
     * However on $model->{$key} = 'value', the 'value' will be persisted upon saving the model.
     *
     * @param string $key
     * @param $value
     */
    public function setDefaultAttribute(string $key, $value)
    {
        $this->defaultAttributesAndValues[$key] = $value;
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $value = parent::getAttribute($key);

        if (array_key_exists($key, $this->defaultAttributesAndValues) && is_null($value)) {
            return $this->defaultAttributesAndValues[$key] ?? null;
        }

        return $value;
    }

    /**
     * Return if the given key value is provided from default, or is from defaults.
     * This should be used when trying to save other models than eloquent.
     *
     * @param string $key
     * @return bool
     */
    public function isDefaultAttributeValue(string $key): bool
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if (array_key_exists($key, $this->defaultAttributesAndValues) && is_null(parent::getAttribute($key))) {
            return true;
        }

        return false;
    }
}
