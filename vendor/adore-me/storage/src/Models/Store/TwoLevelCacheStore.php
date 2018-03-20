<?php
namespace AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use AdoreMe\Storage\Interfaces\Store\TwoLevelCacheStoreInterface;

class TwoLevelCacheStore extends StoreAbstract implements TwoLevelCacheStoreInterface
{
    /** @var string */
    protected $storeName = 'TwoLevelCache';

    const SLOW_STORE = 'slow_store';
    const FAST_STORE = 'fast_store';

    /**
     * The Slow cache store.
     *
     * @var StoreInterface
     */
    protected $slowStore;

    /**
     * The Fast cache store.
     *
     * @var StoreInterface
     */
    protected $fastStore;

    /**
     * Create a new Two Way Cache Store.
     *
     * @param StoreInterface $slowStore
     * @param StoreInterface $fastStore
     * @param string $prefix
     */
    public function __construct(StoreInterface $slowStore, StoreInterface $fastStore, string $prefix)
    {
        $this->slowStore = $slowStore;
        $this->fastStore = $fastStore;
        $this->prefix    = $prefix;
    }

    /**
     * Determine if an item exists in storage.
     * The item is retrieved only from fast store.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function has(string $key, bool $prepareKey = true): bool
    {
        return $this->fastStore->has($key, $prepareKey);
    }

    /**
     * Determine if multiple items exists in storage.
     * Items not found in the storage will have false value.
     *
     * @param array $keys
     * @param bool $prepareKey
     * @return array
     */
    public function hasMany(array $keys, bool $prepareKey = true): array
    {
        return $this->fastStore->hasMany($keys, $prepareKey);
    }

    /**
     * Retrieve an item from storage, by key.
     * The item is retrieved only from fast store.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return mixed
     */
    public function get(string $key, bool $prepareKey = true)
    {
        return $this->fastStore->get($key, $prepareKey);
    }

    /**
     * Retrieve multiple items from storage, by key.
     * Items not found in the storage will not be returned.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    public function many(array $keys, bool $prepareKeys = true): array
    {
        return $this->fastStore->many($keys, $prepareKeys);
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
        if ($this->fastStore->add($key, $value, $seconds, [], $prepareKey, [])) {
            $this->slowStore->put($key, null, $seconds, $tags, $prepareKey, $tagsExpiration);

            return true;
        }

        return false;
    }

    /**
     * Store an item in storage, for a given number of minutes.
     * The item data is put in fast store, while the tags are put in slow store.
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
        $this->fastStore->put(
            $key,
            $value,
            $seconds,
            [],
            $prepareKey,
            []
        );

        $this->slowStore->put(
            $key,
            null,
            $seconds,
            $tags,
            $prepareKey,
            $tagsExpiration
        );
    }

    /**
     * Store multiple items in storage, for a given number of minutes.
     * The items data are put in fast store, while the tags are put in slow store.
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
        $this->fastStore->putMany(
            $values,
            $seconds,
            [],
            $prepareKeys,
            []
        );

        // Replace values with null, because we don't keep the content of items in slow store.
        $values = array_fill_keys(array_keys($values), null);

        $this->slowStore->putMany(
            $values,
            $seconds,
            $tags,
            $prepareKeys,
            $tagsExpiration
        );
    }

    /**
     * Increment the value of an item, from storage.
     *
     * @param string $key
     * @param int $value
     * @param bool $prepareKey
     * @return int
     */
    public function increment(string $key, int $value = 1, bool $prepareKey = true): int
    {
        return $this->fastStore->increment($key, $value, $prepareKey);
    }

    /**
     * Decrement the value of an item, from storage.
     *
     * @param string $key
     * @param int $value
     * @param bool $prepareKey
     * @return int
     */
    public function decrement(string $key, int $value = 1, bool $prepareKey = true): int
    {
        return $this->fastStore->decrement($key, $value, $prepareKey);
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
        $this->fastStore->forever(
            $key,
            $value,
            [],
            $prepareKey,
            []
        );

        $this->slowStore->forever(
            $key,
            null,
            $tags,
            $prepareKey,
            $tagsExpiration
        );
    }

    /**
     * Remove an item from storage.
     * Remove from both fast and slow stores.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function forget(string $key, bool $prepareKey = true): bool
    {
        $this->slowStore->forget($key, $prepareKey);

        return $this->fastStore->forget($key, $prepareKey);
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
        $this->fastStore->flushAll();
        $this->slowStore->flushAll();
    }

    /**
     * Remove all items from storage.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushAll()
    {
        $this->fastStore->flushAll();
        $this->slowStore->flushAll();
    }

    /**
     * Remove all items from current namespace. The prefix is the namespace.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushNamespace()
    {
        $this->fastStore->flushNamespace();
        $this->slowStore->flushNamespace();
    }

    /**
     * Get the driver(s) used.
     *
     * @return array
     */
    public function getDriver()
    {
        return [
            self::SLOW_STORE => $this->slowStore,
            self::FAST_STORE => $this->fastStore
        ];
    }

    /**
     * Clean up tags and keys that are expired or orphaned, if the store supports it.
     *
     * Do note: The garbage collector is designed to remove orphaned tags, and clean-up the global list of
     * tags and keys. It should NOT touch keys.
     *
     * Because only slow store keep the tags, garbage collector is not required for fast store.
     */
    public function collectGarbage()
    {
        $this->slowStore->collectGarbage();
    }

    /**
     * Remove all items that match any of the given tags, if the store supports it.
     * Return a list of keys deleted.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $unprepare
     * @return array
     */
    public function forgetByTags(array $tags, bool $prepareTags = true, bool $unprepare = false): array
    {
        $keys = $this->slowStore->forgetByTags($tags, $prepareTags, $unprepare);

        // Do nothing, if we didn't received any keys.
        if (empty ($keys)) {
            return [];
        }

        // Remove the namespace from keys, so we can delete them from fast store, that might have another namespace.
        $unpreparedKeys = $unprepare ? $keys : $this->slowStore->unprepareKeys($keys);

        $deletedKeys = [];
        foreach ($unpreparedKeys as $key) {
            if ($this->fastStore->forget($key, true)) {
                $deletedKeys[] = $key;
            }
        }

        if ($unprepare) {
            return $deletedKeys;
        }

        return $this->fastStore->prepareKeys($deletedKeys);
    }

    /**
     * Return a list of keys having all of the requested tags.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $removeMissingKeys If true, the resulted keys will be checked if still exists.
     * @param bool $unprepare
     * @return array
     */
    public function getKeysMatchingTags(
        array $tags,
        bool $prepareTags = true,
        bool $removeMissingKeys = false,
        bool $unprepare = false
    ): array {
        return $this->slowStore->getKeysMatchingTags($tags, $prepareTags, $removeMissingKeys, $unprepare);
    }

    /**
     * Return a list of keys having any of the requested tags.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $removeMissingKeys If true, the resulted keys will be checked if still exists.
     * @param bool $unprepare
     * @return array
     */
    public function getKeysMatchingAnyTags(
        array $tags,
        bool $prepareTags = true,
        bool $removeMissingKeys = false,
        bool $unprepare = false
    ): array {
        return $this->slowStore->getKeysMatchingAnyTags($tags, $prepareTags, $removeMissingKeys, $unprepare);
    }

    /**
     * Return a list of keys not having the requested tags.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $removeMissingKeys If true, the resulted keys will be checked if still exists.
     * @param bool $unprepare
     * @return array
     */
    public function getKeysNotMatchingAnyTags(
        array $tags,
        bool $prepareTags = true,
        bool $removeMissingKeys = false,
        bool $unprepare = false
    ): array {
        return $this->slowStore->getKeysNotMatchingAnyTags(
            $tags,
            $prepareTags,
            $removeMissingKeys,
            $unprepare
        );
    }

    /**
     * Get all tags for given key.
     *
     * @param string $key
     * @param bool $prepareKey
     * @param bool $unprepare
     * @return array
     */
    public function getTagsForKey(string $key, bool $prepareKey = true, bool $unprepare = false): array
    {
        return $this->slowStore->getTagsForKey($key, $prepareKey, $unprepare);
    }

    /**
     * Get all keys from store.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @return array
     */
    public function getAllKeys(): array
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->fastStore->getAllKeys();
    }

    /**
     * Get all tags from store.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @return array
     */
    public function getAllTags(): array
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->slowStore->getAllTags();
    }

    /**
     * Get all keys from current namespace.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @param bool $unprepare Set to true to strip prefixes.
     * @return array
     */
    public function getNamespaceKeys(bool $unprepare = false): array
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->fastStore->getNamespaceKeys($unprepare);
    }

    /**
     * Get all tags from current namespace.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @param bool $unprepare Set to true to strip prefixes.
     * @return array
     */
    public function getNamespaceTags(bool $unprepare = false): array
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->slowStore->getNamespaceTags($unprepare);
    }

    /**
     * Prepare the key name, by adding the required prefixes.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return string
     */
    public function prepareKey(string $key, bool $prepareKey = true): string
    {
        return $this->fastStore->prepareKey($key, $prepareKey);
    }

    /**
     * Unprepare the key name, by removing the required prefixes.
     *
     * @param string $key
     * @return string
     */
    public function unprepareKey(string $key): string
    {
        return $this->fastStore->unprepareKey($key);
    }

    /**
     * Prepare the keys, by adding the required prefixes.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    public function prepareKeys(array $keys, bool $prepareKeys = true): array
    {
        return $this->fastStore->prepareKeys($keys, $prepareKeys);
    }

    /**
     * Unprepare the keys, by removing the required prefixes.
     *
     * @param array $keys
     * @return array
     */
    public function unprepareKeys(array $keys): array
    {
        return $this->fastStore->prepareKeys($keys);
    }

    /**
     * Prepare the tag name, by adding the required prefixes.
     *
     * @param string $tag
     * @param bool $prepareTag
     * @return string
     */
    public function prepareTag(string $tag, bool $prepareTag = true): string
    {
        return $this->slowStore->prepareTag($tag, $prepareTag);
    }

    /**
     * Unprepare the tag name, by removing the required prefixes.
     *
     * @param string $tag
     * @return string
     */
    public function unprepareTag(string $tag): string
    {
        return $this->slowStore->unprepareTag($tag);
    }

    /**
     * Prepare the tags, by adding the required prefixes.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @return array
     */
    public function prepareTags(array $tags, bool $prepareTags = true): array
    {
        return $this->slowStore->prepareTags($tags, $prepareTags);
    }

    /**
     * Unprepare the tag, by removing the required prefixes.
     *
     * @param array $tags
     * @return array
     */
    public function unprepareTags(array $tags): array
    {
        return $this->slowStore->unprepareTags($tags);
    }
}
