<?php
namespace AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Interfaces\Store\PhpStaticVariableStoreInterface;
use UnexpectedValueException;

/**
 * Is important to know that this store does not support key expiration nor tag expiration.
 * No exception will be thrown, and instead these parameters will be totally ignored.
 * This is intended and preferred instead of complicating the code to throw exception.
 * If you are using this storage you clearly know that is a volatile storage, designed to be used only in a single
 * instance of php.
 *
 * @package AdoreMe\Storage\Models\Store
 */
class PhpStaticVariableStore extends StoreAbstract implements PhpStaticVariableStoreInterface
{
    const DATA               = 'data';
    const TAGS               = 'tags';
    const METADATA           = 'metadata';
    const METADATA_DATA_TYPE = 'data_type';

    /** @var string */
    protected $storeName = 'PhpStaticVariable';

    /** @var array */
    protected static $data = [];

    /** @var array */
    protected static $tags = [];

    /** @var array */
    protected static $metadata = [];

    /**
     * PhpStaticVariableStore constructor.
     *
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
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

        return array_key_exists($key, self::$data);
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
        $key = $this->prepareKey($key, $prepareKey);

        if ($this->has($key, false)) {
            return $this->decode(self::$data[$key], $key);
        }

        return null;
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
        $preparedKeys = $this->prepareKeys($keys, $prepareKeys);
        $values       = [];

        foreach ($preparedKeys as $key) {
            $values[] = $this->get($key, false);
        }

        return array_combine($keys, $values);
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
        $this->forever($key, $value, $tags, $prepareKey, $tagsExpiration);
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
        foreach ($values as $key => $value) {
            $this->put(
                $key,
                $value,
                $seconds,
                $tags,
                $prepareKeys,
                $tagsExpiration
            );
        }
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
        $key          = $this->prepareKey($key, $prepareKey);
        $currentValue = $this->get($key, false);

        if (empty ($currentValue)) {
            $currentValue = $value;
        } else if (! is_int($currentValue)) {
            throw new UnexpectedValueException(
                'Expected value to be integer, ' . gettype($currentValue) . ' received.'
            );
        } else {
            $currentValue += $value;
        }

        $this->forever($key, $currentValue, [], false);

        return $currentValue;
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
        $key  = $this->prepareKey($key, $prepareKey);
        $tags = $this->prepareTags($tags, $prepareKey);

        // Remove old tags for this key.
        if ($this->has($key, false)) {
            $this->removeTagsForKey($key, $this->getTagsForKey($key, false, false));
        }

        // Encode the value. If the value has a reference, then the reference will be "cut".
        list ($data, $dataType) = $this->encode($value);

        // Set the data.
        self::$data[$key] = $data;

        // Set the metadata.
        self::$metadata[$key] = [
            self::METADATA_DATA_TYPE => $dataType,
        ];

        // Set the tags.
        foreach ($tags as $tag) {
            // Make sure we don't have duplicate keys on the same tag.
            if (array_key_exists($tag, self::$tags) && in_array($key, self::$tags[$tag])) {
                continue;
            }

            self::$tags[$tag][] = $key;
        }
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

        if ($this->has($key, false)) {
            unset (self::$data[$key]);
            unset (self::$metadata[$key]);
            $this->removeTagsForKey($key, $this->getTagsForKey($key, false, false));

            return true;
        }

        return false;
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
        self::$data     = [];
        self::$tags     = [];
        self::$metadata = [];
    }

    /**
     * Remove all items from storage.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushAll()
    {
        self::$data     = [];
        self::$tags     = [];
        self::$metadata = [];
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
        $keys    = [];
        $allKeys = $this->getAllKeys();
        foreach ($allKeys as $key) {
            // Delete every key that starts with prefix and glue.
            if (preg_match('/^' . $this->prefix . self::GLUE . '/', $key)) {
                $keys[] = $key;
            }
        }

        if ($unprepare) {
            return $this->unprepareKeys($keys);
        }

        return $keys;
    }

    /**
     * Remove all items from current namespace. The prefix is the namespace.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushNamespace()
    {
        foreach (self::$data as $key => $value) {
            // Delete every key that starts with prefix and glue.
            if (preg_match('/^' . $this->prefix . self::GLUE . '/', $key)) {
                $this->forget($key, false);
            }
        }
    }

    /**
     * Get the driver(s) used.
     * Will return the referenced arrays.
     *
     * @return array
     */
    public function getDriver()
    {
        return [
            self::DATA     => &self::$data,
            self::TAGS     => &self::$tags,
            self::METADATA => &self::$metadata,
        ];
    }

    /**
     * Clean up tags and keys that are expired or orphaned.
     * Do note: The garbage collector is designed to remove orphaned tags, and clean-up the global list of
     * tags and keys. It should NOT touch keys.
     */
    public function collectGarbage()
    {
        // Does nothing, because there is nothing really to do, as this store cannot expire keys.
        return;
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
        $keys = $this->getKeysMatchingAnyTags($tags, $prepareTags);

        // Do nothing, if we didn't received any keys.
        if (empty ($keys)) {
            return [];
        }

        $deletedKeys = [];
        foreach ($keys as $key) {
            if ($this->forget($key, false)) {
                $deletedKeys[] = $key;
            }
        }

        if ($unprepare) {
            return $this->unprepareKeys($deletedKeys);
        }

        return $deletedKeys;
    }

    /**
     * Return a list of keys having all of the requested tags.
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
        $tags = $this->prepareTags($tags, $prepareTags);

        $keysByTags = [];
        foreach ($tags as $tag) {
            $keys = self::$tags[$tag] ?? [];

            // If no keys were found, then we have no keys matching given tags. Returning empty array.
            if (empty($keys)) {
                return [];
            }

            $keysByTags[] = $keys;
        }

        if (count($keysByTags) == 1) {
            $keys = $keysByTags;
        } else {
            $keys = array_intersect(...$keysByTags);
        }

        return $this->filterKeys($keys, $removeMissingKeys, $unprepare);
    }

    /**
     * Return a list of keys having any of the requested tags.
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
        $tags = $this->prepareTags($tags, $prepareTags);

        $keysByTags = [];
        foreach ($tags as $tag) {
            $keys = self::$tags[$tag] ?? [];

            // Do nothing if no keys were found in tags.
            if (empty($keys)) {
                continue;
            }

            $keysByTags[] = $keys;
        }

        if (empty($keysByTags)) {
            return [];
        }

        $keys = array_unique(array_merge(...$keysByTags));

        return $this->filterKeys($keys, $removeMissingKeys, $unprepare);
    }

    /**
     * Return a list of keys not having the requested tags.
     * Do note that the keys will contain the prefix used.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @param bool $removeMissingKeys If true, the resulted keys will be checked if still exists.
     * @param bool $unprepare
     * @return array
     * @throws \Exception
     */
    public function getKeysNotMatchingAnyTags(
        array $tags,
        bool $prepareTags = true,
        bool $removeMissingKeys = false,
        bool $unprepare = false
    ): array {
        $keysMatchingGivenTags = $this->getKeysMatchingAnyTags(
            $tags,
            $prepareTags,
            $removeMissingKeys,
            $unprepare
        );

        // If we received no key matching given tags, then will return all existing keys.
        $existingKeys = $this->getNamespaceKeys($unprepare);
        if (empty($keysMatchingGivenTags)) {
            $keys = $existingKeys;
        } else {
            $keys = array_diff($existingKeys, $keysMatchingGivenTags);
        }

        // Array_values to reset they array index. Might return [1] => 'test_key' instead of [0] => 'test_key';
        return $this->filterKeys(array_values($keys), false, false);
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
        $key  = $this->prepareKey($key, $prepareKey);
        $tags = [];
        foreach (self::$tags as $tag => $keys) {
            if (in_array($key, $keys)) {
                $tags[] = $tag;
            }
        }

        if ($unprepare) {
            return $this->unprepareTags($tags);
        }

        return $tags;
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
        return array_keys(self::$data);
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
        return array_keys(self::$tags);
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
        $tags = [];
        foreach (self::$tags as $tag => $value) {
            // Delete every tag that starts with prefix and glue.
            if (preg_match('/^' . $this->prefix . self::GLUE . '/', $tag)) {
                $tags[] = $tag;
            }
        }

        if ($unprepare) {
            return $this->unprepareTags($tags);
        }

        return $tags;
    }

    /**
     * Encode the given value, for storage, and generates data and metadata.
     *
     * @param mixed $data
     * @return array
     */
    protected function encode($data): array
    {
        return StorageHelper::encodeForStorage($data);
    }

    /**
     * Decode the given data.
     *
     * @param mixed $data
     * @param string $key
     * @return mixed
     */
    protected function decode($data, string $key)
    {
        return StorageHelper::decodeFromStorage($data, $this->getMetadataDataType($key));
    }

    /**
     * Get the metadata for key.
     *
     * @param string $key
     * @return array
     */
    protected function getMetadata(string $key): array
    {
        if (! array_key_exists($key, self::$metadata)) {
            return [];
        }

        return self::$metadata[$key];
    }

    /**
     * Return the data type, from metadata array.
     *
     * @param string $key
     * @return mixed
     */
    protected function getMetadataDataType(string $key)
    {
        $metadata = $this->getMetadata($key);

        return $metadata[self::METADATA_DATA_TYPE] ?? null;
    }

    /**
     * Remove tags for given key.
     *
     * @param string $key
     * @param array $tags
     */
    protected function removeTagsForKey(string $key, array $tags)
    {
        foreach ($tags as $tag) {
            if (($index = array_search($key, self::$tags[$tag])) !== false) {
                unset(self::$tags[$tag][$index]);

                // Rebuild indexes.
                if (empty (self::$tags[$tag])) {
                    unset (self::$tags[$tag]);
                } else {
                    self::$tags[$tag] = array_values(self::$tags[$tag]);
                }
            }
        }
    }
}
