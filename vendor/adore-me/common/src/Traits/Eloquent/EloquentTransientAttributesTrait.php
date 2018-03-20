<?php
namespace AdoreMe\Common\Traits\Eloquent;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

trait EloquentTransientAttributesTrait
{
    /**
     * Contain the values of the transient attributes.
     *
     * @var array
     */
    protected $transientAttributeValues = [];

    /** @var array */
    protected $transientAttributes = [];

    /**
     * Init the trait.
     *
     * @param array $transientAttributes
     */
    protected function initEloquentTransientAttributesTrait(array $transientAttributes)
    {
        $this->transientAttributes = $transientAttributes;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  bool $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        // Remove the transient attributes from $attributes and move to the proper container.
        foreach ($attributes as $key => $value) {
            if ($this->isTransientAttribute($key)) {
                // Remove the transient attribute from attribute list.
                unset ($attributes[$key]);

                $this->transientAttributeValues[$key] = $value;
            }
        }

        // Continue with setRawAttributes from eloquent model.
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return parent::setRawAttributes($attributes, $sync);
    }

    /**
     * Determine if the attribute is a transient attribute.
     *
     * @param string $key
     * @return bool
     */
    public function isTransientAttribute(string $key)
    {
        return in_array($key, $this->transientAttributes);
    }

    /**
     * Return the raw value of the transient attribute. without casting or mutator.
     *
     * @param string $key
     * @return mixed
     */
    public function getRawTransientAttribute(string $key)
    {
        if (
            $this->isTransientAttribute($key)
            && array_key_exists($key, $this->transientAttributeValues)
        ) {
            return $this->transientAttributeValues[$key];
        }

        return null;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // Attempt to get the transient attribute, if is set so.
        if ($this->isTransientAttribute($key)) {
            return $this->getTransientAttribute($key);
        }

        // Continue with get attribute from eloquent model if is not an transient attribute.
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return parent::getAttribute($key);
    }

    /**
     * Get the transient attribute.
     *
     * @param $key
     * @return mixed
     */
    public function getTransientAttribute($key)
    {
        $value = $this->getRawTransientAttribute($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if (in_array($key, $this->getDates())
            && ! is_null($value)
        ) {
            return $this->asDateTime($value);
        }

        return $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // Attempt to set the transient attribute, if is set so.
        if ($this->isTransientAttribute($key)) {
            if ($this->hasSetMutator($key)) {
                $method = 'set' . Str::studly($key) . 'Attribute';

                $this->{$method}($value);
            } else {
                $this->transientAttributeValues[$key] = $value;
            }

            return $this;
        }

        // Continue with set attribute from eloquent model if is not an transient attribute.
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return parent::setAttribute($key, $value);
    }

    /**
     * Set the raw value of the transient attribute. without casting or mutator.
     * No checks are performed.
     *
     * @param string $key
     * @param $value
     */
    public function setRawTransientAttribute(string $key, $value)
    {
        $this->transientAttributeValues[$key] = $value;
    }

    /**
     * Return the transient attributes.
     *
     * @return array
     */
    public function getTransientAttributes(): array
    {
        return $this->transientAttributeValues;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $transientAttributes = [];
        foreach ($this->transientAttributeValues as $key => $attribute) {
            $attribute = $this->getAttribute($key);

            // Attempt to transform the object into array.
            if (is_object($attribute)) {
                // Check if the object is instance of Arrayable or has method toArray.
                if (
                    method_exists($attribute, 'toArray')
                    || $attribute instanceof Arrayable
                ) {
                    $transientAttributes[$key] = $attribute->toArray();
                    continue;
                }

                // This can throw fatal error if cannot be executed.
                $transientAttributes[$key] = (array) $attribute;
                continue;
            }

            $transientAttributes[$key] = $attribute;
        }

        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $return = array_merge(
            parent::attributesToArray(),
            $transientAttributes
        );

        return $return;
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param array $attributes
     * @return static|EloquentTransientAttributesTrait
     */
    public static function staticOldInstance(array $attributes): self
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static([]);

        $model->exists = true;

        $model->setRawAttributes((array) $attributes, true);

        return $model;
    }

    /**
     * Reset all values from model.
     * Original remains untouched.
     */
    public function resetAllAttributes()
    {
        $this->attributes               = [];
        $this->transientAttributeValues = [];
    }

    /**
     * Prepare the transient attribute to be used for array.
     *
     * @param string $key
     * @return array
     */
    public function transientAttributePrepareToArray(string $key)
    {
        $value = $this->getTransientAttribute($key);

        if (is_object($value)) {
            // Check if the object is instance of Arrayable or has method toArray.
            if (
                method_exists($value, 'toArray')
                || $value instanceof Arrayable
            ) {
                return $value->toArray();
            }

            return (array) $value;
        }

        return $value;
    }
}
