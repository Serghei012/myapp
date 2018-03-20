<?php
namespace AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use BadMethodCallException;

abstract class StoreAbstract implements StoreInterface
{
    const TARGET_HAS_NO_INTEGER_VALUE = 'Target key has no valid integer value.';
    const NO_TAGGING                  = 'This store does not support tagging.';
    const NO_NAMESPACE_FLUSH          = 'This store does not support namespace flushing.';
    const NO_GET_ALL_KEYS_OR_TAGS     = 'This store does not support get all keys or tags.';
    const GLUE                        = ':';

    /**
     * Defined the store name.
     * This should be using for debugging purposes.
     * Instantiated with null, so it does crash if not overwritten.
     *
     * @var string
     */
    protected $storeName = null;

    /**
     * A string that should be prepended to keys.
     * Instantiated with null, so it does crash if not overwritten.
     *
     * @var string
     */
    protected $prefix = null;

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
    ): bool {
        if ($this->has($key, $prepareKey)) {
            return false;
        }

        $this->forever($key, $value, $tags, $prepareKey, $tagsExpiration);

        return true;
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
        $existingKeys = [];

        foreach ($keys as $key) {
            $existingKeys[$key] = $this->has($key, $prepareKey);
        }

        return $existingKeys;
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
        return $this->increment($key, -$value, $prepareKey);
    }

    /**
     * Get the storage key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
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
        return $prepareKey
            ? $this->prefix . self::GLUE . $key
            : $key;
    }

    /**
     * Unprepare the key name, by removing the required prefixes.
     *
     * @param string $key
     * @return string
     */
    public function unprepareKey(string $key): string
    {
        return str_replace($this->prefix . self::GLUE, '', $key);
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
        if ($prepareKeys == false) {
            return $keys;
        }

        $return = [];
        foreach ($keys as $key) {
            $return[] = $this->prepareKey($key, true);
        }

        return $return;
    }

    /**
     * Unprepare the keys, by removing the required prefixes.
     *
     * @param array $keys
     * @return array
     */
    public function unprepareKeys(array $keys): array
    {
        $return = [];

        foreach ($keys as $key) {
            $return[] = $this->unprepareKey($key);
        }

        return $return;
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
        return $prepareTag
            ? $this->prefix . self::GLUE . $tag
            : $tag;
    }

    /**
     * Unprepare the tag name, by removing the required prefixes.
     *
     * @param string $tag
     * @return string
     */
    public function unprepareTag(string $tag): string
    {
        return str_replace($this->prefix . self::GLUE, '', $tag);
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
        if ($prepareTags == false) {
            return $tags;
        }

        $return = [];
        foreach ($tags as $key) {
            $return[] = $this->prepareTag($key, true);
        }

        return $return;
    }

    /**
     * Unprepare the tag, by removing the required prefixes.
     *
     * @param array $tags
     * @return array
     */
    public function unprepareTags(array $tags): array
    {
        $return = [];

        foreach ($tags as $tag) {
            $return[] = $this->unprepareTag($tag);
        }

        return $return;
    }

    /**
     * Clean up tags and keys that are expired or orphaned, if the store supports it.
     * Do note: The garbage collector is designed to remove orphaned tags, and clean-up the global list of
     * tags and keys. It should NOT touch keys.
     * By default it does nothing if the store does not support tagging.
     */
    public function collectGarbage()
    {
        return;
    }

    /**
     * Get store name.
     *
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }

    /**
     * Filter keys for output.
     *
     * @param array $keys
     * @param bool $removeMissingKeys
     * @param bool $unprepare
     * @return array
     */
    protected function filterKeys(
        array $keys,
        bool $removeMissingKeys = false,
        bool $unprepare = false
    ): array {
        $keys = $removeMissingKeys
            ? $this->removeMissingKeys($keys, false)
            : $keys;

        return $unprepare
            ? $this->unprepareKeys($keys)
            : $keys;
    }

    /**
     * Remove non existing keys from the given array of keys.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    protected function removeMissingKeys(array $keys, bool $prepareKeys = true): array
    {
        $return = [];
        $result = $this->hasMany($keys, $prepareKeys);
        foreach ($result as $key => $value) {
            if ($value) {
                $return[] = $key;
            }
        }

        return $return;
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
        throw new BadMethodCallException(self::NO_TAGGING);
    }

    /**
     * Return a list of keys having all of the requested tags, if the store supports it.
     * Do note that the keys will contain the prefix used.
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
        throw new BadMethodCallException(self::NO_TAGGING);
    }

    /**
     * Return a list of keys having any of the requested tags, if the store supports it.
     * Do note that the keys will contain the prefix used.
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
        throw new BadMethodCallException(self::NO_TAGGING);
    }

    /**
     * Return a list of keys not having the requested tags, if the store supports it.
     * Do note that the keys will contain the prefix used.
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
        throw new BadMethodCallException(self::NO_TAGGING);
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
        throw new BadMethodCallException(self::NO_TAGGING);
    }

    /**
     * Remove all items from current namespace. The prefix is the namespace.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushNamespace()
    {
        throw new BadMethodCallException(self::NO_NAMESPACE_FLUSH);
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
        throw new BadMethodCallException(self::NO_GET_ALL_KEYS_OR_TAGS);
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
        throw new BadMethodCallException(self::NO_GET_ALL_KEYS_OR_TAGS);
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
        throw new BadMethodCallException(self::NO_GET_ALL_KEYS_OR_TAGS);
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
        throw new BadMethodCallException(self::NO_TAGGING);
    }
}
