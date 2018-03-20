<?php
namespace AdoreMe\Common\Interfaces;

use AdoreMe\Common\Models\NonPersistentModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use ArrayAccess;
use JsonSerializable;

/**
 * @since 2.0.0
 * @see NonPersistentModel
 * @see NonPersistentModel
 */
interface ModelInterface extends ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    /**
     * Create a new model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = []);

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return self
     */
    public function fill(array $attributes);

    /**
     * Sync the original attributes with the current.
     *
     * @return self
     */
    public function syncOriginal();

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     * @return static
     */
    public function newInstance($attributes = []);

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty();

    /**
     * Determine if the model or any of given attribute(s) have been modified.
     *
     * @param array|string|null $attributes
     * @return bool
     */
    public function isDirty($attributes = null);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get the model's original attribute values.
     *
     * @return array
     */
    public function getOriginal();

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute($key);

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setAttribute($key, $value);

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key);

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value);

    /**
     * Return if the attribute exists on model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key);

    /**
     * Removes an attribute from model.
     *
     * @param string $key
     */
    public function __unset($key);

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString();
}
