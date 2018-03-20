<?php
namespace AdoreMe\Common\Models;

use AdoreMe\Common\Exceptions\UnableToRetrieveDirtyStatusOfObjectException;
use AdoreMe\Common\Helpers\ArrayHelper;
use AdoreMe\Common\Interfaces\ModelInterface;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

abstract class NonPersistentModel implements ModelInterface
{
    /**
     * The attributes that should be cast to native types
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Holds the data that should be saved from the model.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Holds the original data from the model.
     *
     * @var array
     */
    protected $original = [];

    /**
     * Holds an list of mutator methods, so we don't need to create every time we need one.
     *
     * @var array
     */
    protected $getMutatorCache = [];

    /** @var array */
    protected $setMutatorCache = [];

    /**
     * Holds the default attributes and values for model.
     *
     * @var array
     */
    protected $defaultAttributesAndValues = [];

    /**
     * Model constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // Add the default values for the model.
        foreach ($this->defaultAttributesAndValues as $key => $value) {
            if (! array_key_exists($key, $attributes)) {
                $attributes[$key] = $value;
            }
        }

        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return self
     */
    public function fill(array $attributes): self
    {
        $this->attributes = $attributes;
        $this->castAndMutateAttributes();

        return $this;
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return self
     */
    public function syncOriginal(): self
    {
        // WARNING: Do not change this! If the $this->attributes contains an object,
        // then the object will be passed by reference to $this->original, and isDirty() will not work
        // properly, when $this->attributes['object'] is modified.
        $this->original = ArrayHelper::arrayClone($this->attributes);

        return $this;
    }

    /**
     * Create a new instance of model.
     *
     * @param array $attributes
     * @return NonPersistentModel
     * @throws \Exception
     */
    public function newInstance($attributes = []): self
    {
        $this->throwExceptionIfParameterIsNotArray($attributes);

        $model = new static((array) $attributes);

        return $model;
    }

    /**
     * Create a new instance of model.
     *
     * @param array $attributes
     * @return self
     */
    public static function staticNewInstance(array $attributes = []): self
    {
        $model = new static((array) $attributes);

        return $model;
    }

    /**
     * Create a new instance of model, with requested attributes, and run sync original.
     *
     * @param array $attributes
     * @return self
     */
    public function oldInstance(array $attributes): self
    {
        $model = $this->newInstance($attributes);
        $model->syncOriginal();

        return $model;
    }

    /**
     * Create a new instance of model, with requested attributes, and run sync original.
     *
     * @param array $attributes
     * @return self
     */
    public static function staticOldInstance(array $attributes): self
    {
        $model = new static((array) $attributes);
        $model->syncOriginal();

        return $model;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     * @throws UnableToRetrieveDirtyStatusOfObjectException
     */
    public function getDirty(): array
    {
        $dirty = [];

        // Parse each attribute of the model.
        foreach ($this->attributes as $key => $value) {
            // If the attribute does not exists in the original attributes, then is clear the attribute is dirty.
            if (! array_key_exists($key, $this->original)) {
                $dirty[$key] = $value;

                continue;
            }

            $originalKeyValue = $this->original[$key];

            // If the value if an instance of Collection, then we treat it in a special way.
            if ($value instanceof Collection) {
                if (
                    ! $originalKeyValue instanceof Collection
                    || count($originalKeyValue->flatten()->diff($value->flatten()))
                    || count($value->flatten()->diff($originalKeyValue->flatten()))
                ) {
                    $dirty[$key] = $value;
                }
                // Compare the objects that implements non persistent model, or has isDirty method.
            } else if (
                is_object($value)
                && $value instanceof NonPersistentModel
            ) {
                if ($value->isDirty()) {
                    $dirty[$key] = $value;
                    // If the model is not dirty, check flat the model into json and compare with the value from original.
                } else if (
                    is_object($originalKeyValue)
                    && $originalKeyValue instanceof NonPersistentModel
                    && $originalKeyValue->toJson() != $value->toJson()
                ) {
                    $dirty[$key] = $value;
                }
                // Compare json object.
            } else if (
                is_object($value)
                && is_object($originalKeyValue)
                && method_exists($value, 'toJson')
                && method_exists($originalKeyValue, 'toJson')
            ) {
                if ($value->toJson() != $originalKeyValue->toJson()) {
                    $dirty[$key] = $value;
                }
                // If the value is object, then we have no way to know if is dirty. Throw exception.
            } else if (is_object($value)) {
                throw new UnableToRetrieveDirtyStatusOfObjectException(
                    'Unable to retrieve dirty status. Please define "isDirty" method.'
                );
                // Compare original value with current value, as it is.
            } else if (
                $value !== $originalKeyValue && ! $this->originalIsNumericallyEquivalent($key)
            ) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Determine if the new and old values for a given key are numerically equivalent.
     *
     * @param string $key
     * @return bool
     */
    protected function originalIsNumericallyEquivalent($key): bool
    {
        $current  = $this->attributes[$key];
        $original = $this->original[$key];

        return is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) === 0;
    }

    /**
     * Determine if the model or any of given attribute(s) have been modified.
     *
     * @param array|string|null $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        $dirty = $this->getDirty();

        // If no specific attributes were provided, we will just see if the dirty array
        // already contains any attributes. If it does we will just return that this
        // count is greater than zero. Else, we need to check specific attributes.
        if (is_null($attributes)) {
            return count($dirty) > 0;
        }

        $attributes = is_array($attributes)
            ? $attributes : func_get_args();

        // Here we will spin through every attribute and see if this is in the array of
        // dirty attributes. If it is, we will return true and if we make it through
        // all of the attributes for the entire array we will return false at end.
        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the attribute has been modified.
     *
     * @param string $attribute
     * @return bool
     */
    public function attributeIsDirty(string $attribute): bool
    {
        return array_key_exists($attribute, $this->getDirty());
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the original attribute from the model.
     *
     * @param string $key
     * @return mixed|null
     */
    public function getOriginalAttribute(string $key)
    {
        if (array_key_exists($key, $this->original)) {
            return $this->original[$key];
        }

        return null;
    }

    /**
     * Get the model's original attribute values.
     *
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed|null
     * @throws \Exception
     */
    public function getAttribute($key)
    {
        $this->throwExceptionIfKeyNotString($key);

        $value = $this->attributes[$key] ?? null;;

        if ($this->hasGetMutator($key)) {
            $method = $this->createGetMutator($key);

            return $this->{$method}($value);
        }

        return $value;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        $this->throwExceptionIfKeyNotString($key);

        return $this->getAttribute($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setAttribute($key, $value): self
    {
        $this->throwExceptionIfKeyNotString($key);

        // Use mutator to set the value.
        if ($this->hasSetMutator($key)) {
            $method = $this->createSetMutator($key);

            return $this->{$method}($value);
            // If there is a cast for this key.
        } else if ($this->hasCast($key)) {
            $this->attributes[$key] = $this->castAttribute($key, $value);

            return $this;
        }

        // No mutator, nor cast found. Set the value as it is.
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->throwExceptionIfKeyNotString($key);

        $this->setAttribute($key, $value);
    }

    /**
     * Return if the attribute exists on model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key): bool
    {
        $this->throwExceptionIfKeyNotString($key);

        return isset($this->attributes[$key]);
    }

    /**
     * Removes an attribute from model.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->throwExceptionIfKeyNotString($key);

        unset($this->attributes[$key]);
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray());
    }

    /**
     * serialize() checks if your class has a function with the magic name __sleep.
     * If so, that function is executed prior to any serialization.
     * It can clean up the object and is supposed to return an array with the names of all variables of that object
     * that should be serialized. If the method does not return anything then NULL is serialized and E_NOTICE is
     * issued. The intended use of __sleep is to commit pending data or perform similar cleanup tasks. Also, the
     * function is useful if you have very large objects which do not need to be saved completely.
     *
     * @return array|NULL
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.sleep
     */
    public function __sleep()
    {
        return $this->toArray();
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Transform the model into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array      = [];
        $attributes = $this->getAttributes();
        foreach ($attributes as $key => $attribute) {
            $attribute = $this->getAttribute($key);

            // Attempt to transform the object into array.
            if (is_object($attribute)) {
                // Check if the object is instance of Arrayable or has method toArray.
                if (
                    method_exists($attribute, 'toArray')
                    || $attribute instanceof Arrayable
                ) {
                    $array[$key] = $attribute->toArray();
                    continue;
                }

                // This can throw fatal error if cannot be executed.
                $array[$key] = (array) $attribute;
                continue;
            }

            $array[$key] = $attribute;
        }

        return $array;
    }

    /**
     * Update the created_at attribute with the current timestamp.
     *
     * @return self
     */
    public function updateCreatedAt(): self
    {
        $this->setAttribute('created_at', Carbon::now()->toIso8601String());

        return $this;
    }

    /**
     * Update the updated_at attribute with the current timestamp.
     *
     * @return self
     */
    public function updateUpdatedAt(): self
    {
        $this->setAttribute('updated_at', Carbon::now()->toIso8601String());

        return $this;
    }

    /**
     * Update the received attributes.
     * It will also cast and mutate the values.
     *
     * @param array $attributes
     * @return self
     */
    public function update(array $attributes): self
    {
        $this->attributes = array_replace($this->attributes, $attributes);
        $this->castAndMutateAttributes();

        return $this;
    }

    /**
     * Determine whether an attribute should be cast to a native type.
     *
     * @param string $key
     * @return bool
     */
    protected function hasCast($key): bool
    {
        if (array_key_exists($key, $this->casts)) {
            return true;
        }

        return false;
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getCastType($key): string
    {
        return trim(strtolower($this->casts[$key]));
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    protected function castAttribute(string $key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'date':
                $value = $this->asDateTime($value);
                break;
            case 'array':
                $value = (array) $value;
                break;
            case 'int':
            case 'integer':
                $value = (int) $value;
                break;
            case 'real':
            case 'float':
            case 'double':
                $value = (float) $value;
                break;
            case 'string':
                $value = (string) $value;
                break;
            case 'bool':
            case 'boolean':
                $value = (bool) $value;
                break;
        }

        return $value;
    }

    /**
     * Return a timestamp as Carbon object.
     *
     * @param mixed $value
     * @return Carbon
     */
    protected function asDateTime($value): Carbon
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        return Carbon::parse($value);
    }

    /**
     * Cast all attributes, or mutate them, if a mutator is available.
     */
    protected function castAndMutateAttributes()
    {
        foreach ($this->attributes as $attributeKey => $attributeValue) {
            // Use mutator.
            if ($this->hasSetMutator($attributeKey)) {
                $this->setAttribute($attributeKey, $attributeValue);

                continue;
                // Use cast.
            } else if ($this->hasCast($attributeKey)) {
                $this->attributes[$attributeKey] = $this->castAttribute($attributeKey, $attributeValue);
            }
        }
    }

    /**
     * Generates the get mutator.
     *
     * @param string $key
     * @return string
     */
    protected function createGetMutator(string $key): string
    {
        if (! array_key_exists($key, $this->getMutatorCache)) {
            $this->getMutatorCache[$key] = 'get' . Str::studly($key) . 'Attribute';
        }

        return $this->getMutatorCache[$key];
    }

    /**
     * Generates the set mutator.
     *
     * @param string $key
     * @return string
     */
    protected function createSetMutator(string $key): string
    {
        if (! array_key_exists($key, $this->setMutatorCache)) {
            $this->setMutatorCache[$key] = 'set' . Str::studly($key) . 'Attribute';
        }

        return $this->setMutatorCache[$key];
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, $this->createGetMutator($key));
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasSetMutator($key): bool
    {
        return method_exists($this, $this->createSetMutator($key));
    }

    /**
     * Reset all values from model.
     * Original remains untouched.
     */
    public function resetAttributes()
    {
        $this->attributes = [];
    }

    /**
     * Throw exception if parameter is not an array.
     *
     * @param array $parameter
     * @return array
     */
    protected function throwExceptionIfParameterIsNotArray(array $parameter)
    {
        return $parameter;
    }

    /**
     * Throw exception if key is not an string.
     *
     * @param string $key
     * @return string
     */
    protected function throwExceptionIfKeyNotString(string $key)
    {
        return $key;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }
}
