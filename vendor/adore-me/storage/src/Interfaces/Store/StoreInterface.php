<?php
namespace AdoreMe\Storage\Interfaces\Store;

interface StoreInterface
{
    /**
     * Determine if an item exists in storage.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function has(string $key, bool $prepareKey = true): bool;

    /**
     * Determine if multiple items exists in storage.
     * Items not found in the storage will have false value.
     *
     * @param array $keys
     * @param bool $prepareKey
     * @return array
     */
    public function hasMany(array $keys, bool $prepareKey = true): array;

    /**
     * Retrieve an item from storage, by key.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return mixed
     */
    public function get(string $key, bool $prepareKey = true);

    /**
     * Retrieve multiple items from storage, by key.
     * Items not found in the storage will have null value.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    public function many(array $keys, bool $prepareKeys = true): array;

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
    ): bool;

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
    );

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
    );

    /**
     * Increment the value of an item, from storage.
     *
     * @param string $key
     * @param int $value
     * @param bool $prepareKey
     * @return int
     */
    public function increment(string $key, int $value = 1, bool $prepareKey = true): int;

    /**
     * Decrement the value of an item, from storage.
     *
     * @param string $key
     * @param int $value
     * @param bool $prepareKey
     * @return int
     */
    public function decrement(string $key, int $value = 1, bool $prepareKey = true): int;

    /**
     * Store an item in storage, without expiration.
     *
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param bool $prepareKey
     * @param array $tagsExpiration
     */
    public function forever(string $key, $value, array $tags = [], bool $prepareKey = true, array $tagsExpiration = []);

    /**
     * Remove an item from storage.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return bool
     */
    public function forget(string $key, bool $prepareKey = true): bool;

    /**
     * Remove all items from storage.
     *
     * @deprecated Use flushAll or flushNamespace instead, as per needs.
     * @since 2.0.1
     * @until 3.0.0
     * @return void
     */
    public function flush();

    /**
     * Remove all items from storage.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushAll();

    /**
     * Remove all items from current namespace. The prefix is the namespace.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushNamespace();

    /**
     * Get the storage key prefix.
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Get the driver(s) used.
     *
     * @return mixed
     */
    public function getDriver();

    /**
     * Get store name.
     *
     * @return string
     */
    public function getStoreName(): string;

    /**
     * Clean up tags and keys that are expired or orphaned, if the store supports it.
     *
     * Do note: The garbage collector is designed to remove orphaned tags, and clean-up the global list of
     * tags and keys. It should NOT touch keys.
     */
    public function collectGarbage();

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
    ): array;

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
    ): array;

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
    ): array;

    /**
     * Prepare the key name, by adding the required prefixes.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return string
     */
    public function prepareKey(string $key, bool $prepareKey = true): string;

    /**
     * Unprepare the key name, by removing the required prefixes.
     *
     * @param string $key
     * @return string
     */
    public function unprepareKey(string $key): string;

    /**
     * Prepare the keys, by adding the required prefixes.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    public function prepareKeys(array $keys, bool $prepareKeys = true): array;

    /**
     * Unprepare the keys, by removing the required prefixes.
     *
     * @param array $keys
     * @return array
     */
    public function unprepareKeys(array $keys): array;

    /**
     * Prepare the tag name, by adding the required prefixes.
     *
     * @param string $tag
     * @param bool $prepareTag
     * @return string
     */
    public function prepareTag(string $tag, bool $prepareTag = true): string;

    /**
     * Unprepare the tag name, by removing the required prefixes.
     *
     * @param string $tag
     * @return string
     */
    public function unprepareTag(string $tag): string;

    /**
     * Prepare the tags, by adding the required prefixes.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @return array
     */
    public function prepareTags(array $tags, bool $prepareTags = true): array;

    /**
     * Unprepare the tag, by removing the required prefixes.
     *
     * @param array $tags
     * @return array
     */
    public function unprepareTags(array $tags): array;

    /**
     * Get all tags for given key.
     *
     * @param string $key
     * @param bool $prepareKey
     * @param bool $unprepare
     * @return array
     */
    public function getTagsForKey(string $key, bool $prepareKey = true, bool $unprepare = false): array;

    /**
     * Get all keys from store.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @return array
     */
    public function getAllKeys(): array;

    /**
     * Get all tags from store.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @return array
     */
    public function getAllTags(): array;

    /**
     * Get all keys from current namespace.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @param bool $unprepare Set to true to strip prefixes.
     * @return array
     */
    public function getNamespaceKeys(bool $unprepare = false): array;

    /**
     * Get all tags from current namespace.
     * Do note: This should not be used in database operations, as the result might not be reliable, due to various
     * factors: non atomic operations, expired keys, tags, concurrency, etc.
     *
     * @internal
     * @param bool $unprepare Set to true to strip prefixes.
     * @return array
     */
    public function getNamespaceTags(bool $unprepare = false): array;
}
