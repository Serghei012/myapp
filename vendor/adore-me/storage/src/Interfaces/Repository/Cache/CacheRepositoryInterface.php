<?php
namespace AdoreMe\Storage\Interfaces\Repository\Cache;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use ArrayAccess;
use Closure;
use DateTime;
use Illuminate\Contracts\Cache\Repository as IlluminateCacheRepository;

interface CacheRepositoryInterface extends IlluminateCacheRepository, ArrayAccess
{
    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function has($key, bool $prepareKey = true): bool;

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $prepareKey
     * @return mixed
     */
    public function get($key, $default = null, bool $prepareKey = true);

    /**
     * Retrieve multiple items from the cache by key.
     * Items not found in the cache will have a null value.
     *
     * @param array $keys
     * @param null $default
     * @param bool $prepareKey
     * @return array
     */
    public function many(array $keys, $default = null, bool $prepareKey = true);

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $prepareKey
     * @return mixed
     */
    public function pull($key, $default = null, bool $prepareKey = true);

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param DateTime|int $minutes
     * @param array $tags
     * @param bool $prepareKey
     */
    public function put($key, $value, $minutes, array $tags = [], bool $prepareKey = true);

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param array $values
     * @param int $minutes
     * @param array $tags
     * @param bool $prepareKeys
     */
    public function putMany(array $values, $minutes, array $tags = [], bool $prepareKeys = true);

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key
     * @param mixed $value
     * @param DateTime|int $minutes
     * @param array $tags
     * @param bool $prepareKey
     * @return bool
     */
    public function add($key, $value, $minutes, array $tags = [], bool $prepareKey = true);

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $prepareKey
     * @return bool|int
     */
    public function increment($key, $value = 1, bool $prepareKey = true);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $prepareKey
     * @return bool|int
     */
    public function decrement($key, $value = 1, bool $prepareKey = true);

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param bool $prepareKey
     */
    public function forever($key, $value, array $tags = [], bool $prepareKey = true);

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string $key
     * @param DateTime|int $minutes
     * @param Closure $callback
     * @param array $tags
     * @param bool $prepareKey
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback, array $tags = [], bool $prepareKey = true);

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string $key
     * @param Closure $callback
     * @param array $tags
     * @param bool $prepareKey
     * @return mixed
     */
    public function sear($key, Closure $callback, array $tags = [], bool $prepareKey = true);

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string $key
     * @param Closure $callback
     * @param array $tags
     * @param bool $prepareKey
     * @return mixed
     */
    public function rememberForever($key, Closure $callback, array $tags = [], bool $prepareKey = true);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function forget($key, bool $prepareKey = true);

    /**
     * Remove all items that match any of the given tags, if the store supports it.
     * Return a list of keys deleted.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $unprepare
     * @return array
     */
    public function forgetByTags(array $tags, bool $prepareTags = true, bool $unprepare = false): array;

    /**
     * Get the cache store implementation.
     *
     * @return StoreInterface
     */
    public function getStore(): StoreInterface;

    /**
     * Get the store name of implemented cache store.
     *
     * @return string
     */
    public function getStoreName(): string;
}
