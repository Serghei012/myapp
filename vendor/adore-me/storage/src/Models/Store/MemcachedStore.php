<?php
namespace AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\MemcachedStoreInterface;
use BadMethodCallException;
use Memcached;

class MemcachedStore extends StoreAbstract implements MemcachedStoreInterface
{
    /** @var string */
    protected $storeName = 'Memcached';

    /** @var Memcached */
    protected $memcached;

    /**
     * Create a new Memcached store.
     *
     * @param Memcached $memcached
     * @param string $prefix
     */
    public function __construct(Memcached $memcached, string $prefix)
    {
        $this->prefix    = $prefix;
        $this->memcached = $memcached;
    }

    /**
     * Determine if an item exists in storage.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function has(string $key, bool $prepareKey = true): bool
    {
        $key = $this->prepareKey($key, $prepareKey);

        $this->memcached->get($key);

        return $this->memcached->getResultCode() === Memcached::RES_NOTFOUND ? false : true;
    }

    /**
     * Retrieve an item from storage, by key.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return mixed
     */
    public function get(string $key, bool $prepareKey = true)
    {
        $key   = $this->prepareKey($key, $prepareKey);
        $value = $this->memcached->get($key);

        if ($this->memcached->getResultCode() == Memcached::RES_SUCCESS) {
            return $value;
        }

        return null;
    }

    /**
     * Retrieve multiple items from storage, by key.
     * Items not found in the storage will have null value.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    public function many(array $keys, bool $prepareKeys = true): array
    {
        $preparedKeys = $this->prepareKeys($keys, $prepareKeys);

        /** @noinspection PhpPassByRefInspection */
        $values = $this->memcached->getMulti($preparedKeys, Memcached::GET_PRESERVE_ORDER);

        if ($this->memcached->getResultCode() != Memcached::RES_SUCCESS) {
            return array_fill_keys($keys, null);
        }

        return array_combine($keys, $values);
    }

    /**
     * Store an item in the storage, if not already set.
     * Returns true if key was added to store, or false if the key already exists.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds 0 => Forever
     * @param array $tags
     * @param bool $prepareKey
     * @param array $tagsExpiration
     * @return bool
     */
    public function add(
        string $key,
        $value,
        int $seconds = 0,
        array $tags = [],
        bool $prepareKey = true,
        array $tagsExpiration = []
    ): bool
    {
        if (! empty($tags)) {
            throw new BadMethodCallException(self::NO_TAGGING);
        }

        $key = $this->prepareKey($key, $prepareKey);

        return $this->memcached->add($key, $value, $seconds);
    }

    /**
     * Store an item in storage, for a given number of minutes.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds 0 => Forever
     * @param array $tags
     * @param bool $prepareKey
     * @param array $tagsExpiration
     */
    public function put(
        string $key,
        $value,
        int $seconds = 0,
        array $tags = [],
        bool $prepareKey = true,
        array $tagsExpiration = []
    ) {
        if (! empty($tags)) {
            throw new BadMethodCallException(self::NO_TAGGING);
        }

        $key = $this->prepareKey($key, $prepareKey);

        $this->memcached->set($key, $value, $seconds);
    }

    /**
     * Store multiple items in storage, for a given number of minutes.
     *
     * @param array $values
     * @param int $seconds 0 => Forever
     * @param array $tags
     * @param bool $prepareKeys
     * @param array $tagsExpiration
     */
    public function putMany(
        array $values,
        int $seconds = 0,
        array $tags = [],
        bool $prepareKeys = true,
        array $tagsExpiration = []
    ) {
        if (! empty($tags) || ! empty($tagsExpiration)) {
            throw new BadMethodCallException(self::NO_TAGGING);
        }

        $prefixedItems = [];
        foreach ($values as $key => $value) {
            $key = $this->prepareKey($key, $prepareKeys);

            $prefixedItems[$key] = $value;
        }

        $this->memcached->setMulti($prefixedItems, $seconds);
    }

    /**
     * Increment the value of an item, from storage.
     *
     * @param string $key
     * @param int $value
     * @param bool $prepareKey
     * @return int
     * @throws \Exception
     */
    public function increment(string $key, int $value = 1, bool $prepareKey = true): int
    {
        $key = $this->prepareKey($key, $prepareKey);

        // Return the value, if the result is not false.
        if (($return = $this->memcached->increment($key, $value)) !== false) {
            return $return;
        }

        if (! $this->has($key, false)) {
            // Set the default value to 0, if return is false.
            $this->memcached->set($key, 0);

            return $this->memcached->increment($key, $value);
        }

        throw new \Exception(self::TARGET_HAS_NO_INTEGER_VALUE);
    }

    /**
     * Decrement the value of an item, from storage.
     *
     * @param string $key
     * @param int $value
     * @param bool $prepareKey
     * @return int
     * @throws \Exception
     */
    public function decrement(string $key, int $value = 1, bool $prepareKey = true): int
    {
        $key = $this->prepareKey($key, $prepareKey);

        if (($return = $this->memcached->decrement($key, $value)) !== false) {
            return $return;
        }

        if (! $this->has($key, false)) {
            // Set the default value to 0, if return is false.
            $this->memcached->set($key, 0);

            return $this->memcached->decrement($key, $value);
        }

        throw new \Exception(self::TARGET_HAS_NO_INTEGER_VALUE);
    }

    /**
     * Store an item in storage, without expiration.
     *
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param bool $prepareKey
     * @param array $tagsExpiration
     */
    public function forever(string $key, $value, array $tags = [], bool $prepareKey = true, array $tagsExpiration = [])
    {
        if (! empty($tags)) {
            throw new BadMethodCallException(self::NO_TAGGING);
        }

        $this->put($key, $value, 0, [], $prepareKey);
    }

    /**
     * Remove an item from storage.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function forget(string $key, bool $prepareKey = true): bool
    {
        $key = $this->prepareKey($key, $prepareKey);

        return $this->memcached->delete($key);
    }

    /**
     * Remove all items from storage.
     *
     * @deprecated Use flushAll or flushNamespace instead, as per needs.
     * @since 2.0.1
     * @until 3.0.0
     * @return void
     */
    public function flush()
    {
        $this->memcached->flush();
    }

    /**
     * Remove all items from storage.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushAll()
    {
        $this->memcached->flush();
    }

    /**
     * Get the driver(s) used.
     *
     * @return Memcached
     */
    public function getDriver()
    {
        return $this->memcached;
    }
}
