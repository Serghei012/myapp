<?php
namespace AdoreMe\Common\Interfaces;

interface DefaultAttributesInterface extends ModelInterface
{
    /**
     * Set an default value for the given key.
     * These values will not be persisted.
     * However on $model->{$key} = 'value', the 'value' will be persisted upon saving the model.
     *
     * @param string $key
     * @param $value
     */
    public function setDefaultAttribute(string $key, $value);

    /**
     * Return if the given key value is provided from default, or is from defaults.
     * This should be used when trying to save other models than eloquent.
     *
     * @param string $key
     * @return bool
     */
    public function isDefaultAttributeValue(string $key): bool;
}
