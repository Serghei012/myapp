<?php
namespace AdoreMe\Logger\Models;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Models\NonPersistentModel;
use AdoreMe\Common\Helpers\ObjectHelper;
use Illuminate\Support\Collection;

/**
 * @property Collection flash
 * @property Collection persistent
 * @property Collection temporary
 */
class Log extends NonPersistentModel implements ModelInterface
{
    const FLASH      = 'flash';
    const PERSISTENT = 'persistent';
    const TEMPORARY  = 'temporary';

    const LOG_TYPES  = [
        self::FLASH,
        self::PERSISTENT,
        self::TEMPORARY
    ];

    protected $defaultAttributesAndValues = [
        self::FLASH      => [],
        self::PERSISTENT => [],
        self::TEMPORARY  => [],
    ];

    /**
     * Log constructor.
     *
     * @param array $attributes
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (! in_array($key, self::LOG_TYPES, true)) {
                throw new \Exception(
                    'Unknown log type "' . $key . '". Accepted values: ' . json_encode(self::LOG_TYPES)
                );
            }
        }

        parent::__construct($attributes);
    }

    /**
     * Set flash attribute.
     *
     * @param $value
     * @return self
     */
    public function setFlashAttribute($value): self
    {
        $this->attributes[self::FLASH] = ObjectHelper::castIntoCollectionOf($value, Entry::class);

        return $this;
    }

    /**
     * Get flash attribute.
     *
     * @param $value
     * @return Collection
     */
    public function getFlashAttribute($value): Collection
    {
        // If the attribute is null, create an empty collection and set it on the model.
        if (is_null ($value)) {
            $value = collect([]);
            $this->attributes[self::FLASH] = $value;
        }

        return $value;
    }

    /**
     * Set persistent attribute.
     *
     * @param $value
     * @return self
     */
    public function setPersistentAttribute($value): self
    {
        $this->attributes[self::PERSISTENT] = ObjectHelper::castIntoCollectionOf($value, Entry::class);

        return $this;
    }

    /**
     * Get persistent attribute.
     *
     * @param $value
     * @return Collection
     */
    public function getPersistentAttribute($value): Collection
    {
        // If the attribute is null, create an empty collection and set it on the model.
        if (is_null ($value)) {
            $value = collect([]);
            $this->attributes[self::PERSISTENT] = $value;
        }

        return $value;
    }

    /**
     * Set temporary attribute.
     *
     * @param $value
     * @return self
     */
    public function setTemporaryAttribute($value): self
    {
        $this->attributes[self::TEMPORARY] = ObjectHelper::castIntoCollectionOf($value, Entry::class);

        return $this;
    }

    /**
     * Get temporary attribute.
     *
     * @param $value
     * @return Collection
     */
    public function getTemporaryAttribute($value): Collection
    {
        // If the attribute is null, create an empty collection and set it on the model.
        if (is_null ($value)) {
            $value = collect([]);
            $this->attributes[self::TEMPORARY] = $value;
        }

        return $value;
    }
}
