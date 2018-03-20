<?php
namespace AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\CacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use Closure;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Repository as IlluminateRepository;

abstract class CacheRepositoryAbstract extends IlluminateRepository implements CacheRepositoryInterface
{
    /**
     * The cache store implementation.
     *
     * @var StoreInterface
     */
    protected $store;

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new cache repository instance.
     *
     * @param StoreInterface $store
     */
    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function has($key, bool $prepareKey = true): bool
    {
        return $this->store->has($key, $prepareKey);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $prepareKey
     * @return mixed
     */
    public function get($key, $default = null, bool $prepareKey = true)
    {
        if (is_array($key)) {
            return $this->many($key, $default, $prepareKey);
        }

        $value = $this->store->get($key, $prepareKey);

        // If we could not find the cache value, we will fire the missed event and get
        // the default value for this cache value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
        if (is_null($value)) {
            $this->event(new CacheMissed($key));

            $value = value($default);
        } else {
            $this->event(new CacheHit($key, $value));
        }

        return $value;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param array $keys
     * @param null $default
     * @param bool $prepareKey
     * @return array
     */
    public function many(array $keys, $default = null, bool $prepareKey = true)
    {
        $normalizedKeys = [];

        foreach ($keys as $key => $value) {
            $normalizedKeys[] = is_string($key) ? $key : $value;
        }

        $values = $this->store->many($normalizedKeys, $prepareKey);

        foreach ($values as $key => &$value) {
            // If we could not find the cache value, we will fire the missed event and get
            // the default value for this cache value. This default could be a callback
            // so we will execute the value function which will resolve it if needed.
            if (is_null($value)) {
                $this->event(new CacheMissed($key));

                $value = isset($keys[$key]) ? value($keys[$key]) : $default;
            } else {
                // If we found a valid value we will fire the "hit" event and return the value
                // back from this function. The "hit" event gives developers an opportunity
                // to listen for every possible cache "hit" throughout this applications.
                $this->event(new CacheHit($key, $value));
            }
        }

        return $values;
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $prepareKey
     * @return mixed
     */
    public function pull($key, $default = null, bool $prepareKey = true)
    {
        $value = $this->get($key, $default, $prepareKey);

        $this->forget($key, $prepareKey);

        return $value;
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param \DateTime|int $minutes - NULL because the abstract function extended, has it set to null :/
     * @param array $tags
     * @param bool $prepareKey
     */
    public function put($key, $value, $minutes = null, array $tags = [], bool $prepareKey = true)
    {
        if (is_array($key)) {
            $this->putMany(
                $key,
                $value,
                $tags,
                $prepareKey
            );

            return;
        }

        $minutes = $this->getMinutes($minutes);

        if (! is_null($minutes)) {
            $this->store->put(
                $key,
                $value,
                $minutes * 60,
                $tags,
                $prepareKey
            );

            $this->event(new KeyWritten($key, $value, $minutes, $tags));
        }
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param array $values
     * @param int $minutes
     * @param array $tags
     * @param bool $prepareKeys
     */
    public function putMany(array $values, $minutes, array $tags = [], bool $prepareKeys = true)
    {
        $minutes = $this->getMinutes($minutes);

        // Do nothing if the minutes is null.
        if (is_null($minutes)) {
            return;
        }

        if (! is_null($minutes)) {
            $this->store->putMany(
                $values,
                $minutes * 60,
                $tags,
                $prepareKeys
            );

            foreach ($values as $key => $value) {
                $this->event(new KeyWritten($key, $value, $minutes, $tags));
            }
        }
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key
     * @param mixed $value
     * @param \DateTime|int $minutes
     * @param array $tags
     * @param bool $prepareKey
     * @return bool
     */
    public function add($key, $value, $minutes, array $tags = [], bool $prepareKey = true)
    {
        $minutes = $this->getMinutes($minutes);

        if (is_null($minutes)) {
            return false;
        }

        // If the store has an "add" method we will call the method on the store so it
        // has a chance to override this logic. Some drivers better support the way
        // this operation should work with a total "atomic" implementation of it.
        if (method_exists($this->store, 'add')) {
            return $this->store->add($key, $value, $minutes * 60, $prepareKey);
        }

        // If the value did not exist in the cache, we will put the value in the cache
        // so it exists for subsequent requests. Then, we will return true so it is
        // easy to know if the value gets added. Otherwise, we will return false.
        if (! $this->has($key, $prepareKey)) {
            $this->put($key, $value, $minutes * 60, $tags, $prepareKey);

            return true;
        }

        return false;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $prepareKey
     * @return bool|int
     */
    public function increment($key, $value = 1, bool $prepareKey = true)
    {
        return $this->store->increment($key, $value, $prepareKey);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $prepareKey
     * @return bool|int
     */
    public function decrement($key, $value = 1, bool $prepareKey = true)
    {
        return $this->store->decrement($key, $value, $prepareKey);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param bool $prepareKey
     */
    public function forever($key, $value, array $tags = [], bool $prepareKey = true)
    {
        $this->store->forever($key, $value, $tags, $prepareKey);

        $this->event(new KeyWritten($key, $value, 0, $tags));
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string $key
     * @param \DateTime|int $minutes
     * @param \Closure $callback
     * @param array $tags
     * @param bool $prepareKey
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback, array $tags = [], bool $prepareKey = true)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of minutes so it's available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $minutes * 60, $tags, $prepareKey);

        return $value;
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string $key
     * @param \Closure $callback
     * @param array $tags
     * @param bool $prepareKey
     * @return mixed
     */
    public function sear($key, Closure $callback, array $tags = [], bool $prepareKey = true)
    {
        return $this->rememberForever($key, $callback, $tags, $prepareKey);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string $key
     * @param \Closure $callback
     * @param array $tags
     * @param bool $prepareKey
     * @return mixed
     */
    public function rememberForever($key, Closure $callback, array $tags = [], bool $prepareKey = true)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of minutes so it's available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback(), $tags, $prepareKey);

        return $value;
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function forget($key, bool $prepareKey = true)
    {
        $success = $this->store->forget($key, $prepareKey);

        $this->event(new KeyForgotten($key));

        return $success;
    }

    /**
     * Remove all items that match any of the given tags.
     * Return a list of keys deleted.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $unprepare
     * @return array
     */
    public function forgetByTags(array $tags, bool $prepareTags = true, bool $unprepare = false): array
    {
        return $this->store->forgetByTags($tags, $prepareTags, $unprepare);
    }

    /**
     * Get the cache store implementation.
     *
     * @return StoreInterface
     */
    public function getStore(): StoreInterface
    {
        return $this->store;
    }

    /**
     * Get the store name of implemented cache store.
     *
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->store->getStoreName();
    }
}
