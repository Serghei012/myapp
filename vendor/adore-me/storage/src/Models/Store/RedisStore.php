<?php
namespace AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Interfaces\Store\RedisStoreInterface;
use Illuminate\Redis\Connections\Connection;
use Predis\Client as PredisClient;
use Predis\Response\ServerException;
use Redis;
use RedisException;

/**
 * This redis store uses LUA scripts to do repetitive get/set operations.
 */
class RedisStore extends StoreAbstract implements RedisStoreInterface
{
    /** @var string */
    protected $storeName = 'Redis';

    const GLUE                     = ':';
    const KEYS_NAMESPACE           = 'key';
    const TAGS_NAMESPACE           = 'tag';
    const ALL_KEYS_KEY             = 'all_keys';
    const ALL_TAGS_KEY             = 'all_tags';
    const FIELD_DATA               = 'data';
    const FIELD_METADATA           = 'metadata';
    const FIELD_METADATA_TAGS      = 'tags';
    const FIELD_METADATA_DATA_TYPE = 'data_type';

    /**
     * The Redis connection that should be used.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Create a new Redis store.
     *
     * @param Connection $connection
     * @param string $prefix
     */
    public function __construct(Connection $connection, string $prefix)
    {
        $this->connection = $connection;
        $this->prefix     = $prefix;
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

        return (bool) $this->client()->exists($key);
    }

    /**
     * Determine if multiple items exists in storage.
     * Items not found in the storage will have false value.
     *
     * @param array $keys
     * @param bool $prepareKeys
     * @return array
     */
    public function hasMany(array $keys, bool $prepareKeys = true): array
    {
        $preparedKeys = $this->prepareKeys($keys, $prepareKeys);

        $lua = <<<LUA
local r = {}
for _,key in pairs(KEYS) do
    if redis.call("exists", key) == 1 then
        r[#r+1] = key
    end
end
return r
LUA;
        $existingKeys = $this->evalSha($this->scriptLoad($lua), $preparedKeys, count($preparedKeys));
        $result       = [];
        foreach ($preparedKeys as $index => $key) {
            if (in_array($key, $existingKeys)) {
                $bool = true;
            } else {
                $bool = false;
            }

            $result[$keys[$index]] = $bool;
        }

        return $result;
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
        $key      = $this->prepareKey($key, $prepareKey);
        $items    = $this->client()->hmget($key, [self::FIELD_DATA, self::FIELD_METADATA]);
        $data     = $items[self::FIELD_DATA] ?? $items[0] ?? null;
        $metadata = $items[self::FIELD_METADATA] ?? $items[1] ?? null;
        $metadata = $this->decodeMetadata($metadata);

        return $this->decode($data, $metadata);
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
        $return       = [];
        $preparedKeys = $this->prepareKeys($keys, $prepareKeys);
        $values       = $this->mhmget(
            $preparedKeys,
            [
                self::FIELD_DATA,
                self::FIELD_METADATA
            ]
        );

        foreach ($values as $index => $item) {
            $data     = $item[0] ?? null;
            $metadata = $item[1] ?? null;
            $metadata = $this->decodeMetadata($metadata);
            $data     = $this->decode($data, $metadata);

            $return[$keys[$index]] = $data;
        }

        return $return;
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
        $this->putWithTags(
            $key,
            $value,
            $seconds,
            $tags,
            $prepareKey,
            $prepareKey,
            $tagsExpiration
        );
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
        //TODO: for the future, create a multi-put-many directly via LUA
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
        //TODO: find a way to read current tags from LUA so we can fully operate redis from a single LUA script.
        $currentMetaData = $this->decodeMetadata($this->getMetadata($key, $prepareKey));
        $currentTags     = $this->getFieldMetadataTags($currentMetaData);
        $key             = $this->prepareKey($key, $prepareKey);
        $tags            = [];
        $tagsExpiration  = [];
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($data, $metadata) = $this->encode($value, $tags);

        $luaPrepend = <<<LUA
local parsedValue = 0;
local currentValue = redis.call('hget', ARGV[1], ARGV[2]);
if currentValue then
    parsedValue = tonumber(currentValue);
end

if not parsedValue then
    return redis.error_reply('Value "' .. currentValue .. '" is not an integer');
end

local currentIncrement = parsedValue + tonumber(ARGV[3]);

if not redis.call('hmset', ARGV[1], ARGV[2], currentIncrement, ARGV[4], ARGV[5]) then
    return redis.error_reply('Could not store key ' .. ARGV[1]);
end
LUA;
        $luaAppend  = <<<LUA
return currentIncrement;
LUA;

        $return = $this->putWithLua(
            $key,
            $value,
            $metadata,
            0,
            $currentTags,
            $tags,
            $tagsExpiration,
            $luaPrepend,
            $luaAppend
        );

        return (int) $return;
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
        $this->putWithTags(
            $key,
            $value,
            0,
            $tags,
            $prepareKey,
            $prepareKey,
            $tagsExpiration
        );
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
        // Get the list of old tags, so we can remove them.
        $currentMetaData = $this->decodeMetadata($this->getMetadata($key, $prepareKey));
        $currentTags     = $this->getFieldMetadataTags($currentMetaData);
        $key             = $this->prepareKey($key, $prepareKey);

        $lua = <<<LUA
redis.call('del', ARGV[1]);
redis.call('srem', ARGV[2], ARGV[1]);
for _,key in ipairs(KEYS) do
    redis.call('srem', key, ARGV[1])
    if redis.call('exists', key) == 0 then
        redis.call('srem', ARGV[3], key)
    end
end
LUA;

        $numberOfOldTags = count($currentTags);
        $args            = [];                    // Create the argument list.
        if ($numberOfOldTags > 0) {               // insert old tags in the KEYS list
            $args = $currentTags;
        }
        $args[] = $key;                           // Argv 1
        $args[] = $this->getAllKeysKey();         // Argv 2
        $args[] = $this->getAllTagsKey();         // Argv 3

        $this->evalSha($this->scriptLoad($lua), $args, $numberOfOldTags);

        return true;
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
        $this->flushDb();
        $this->flushScript();
    }

    /**
     * Remove all items from storage.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushAll()
    {
        $this->flushDb();
        $this->flushScript();
    }

    /**
     * Remove all items from current namespace. The prefix is the namespace.
     *
     * @since 2.0.1
     * @return void
     */
    public function flushNamespace()
    {
        $namespaceKeys = $this->getNamespaceKeys();
        foreach ($namespaceKeys as $key) {
            $this->forget($key, false);
        }

        $this->collectGarbage();
    }

    /**
     * Get the driver(s) used.
     *
     * @return Connection
     */
    public function getDriver()
    {
        return $this->connection;
    }

    /**
     * Clean up tags and keys that are expired or orphaned.
     *
     * Do note: The garbage collector is designed to remove orphaned tags, and clean-up the global list of
     * tags and keys. It should NOT touch keys.
     */
    public function collectGarbage()
    {
        $lua = <<<LUA
local tags = redis.call('smembers', ARGV[1])
for _,tag in ipairs(tags) do
    local tagKeys = redis.call('smembers', tag)
    for _,key in ipairs(tagKeys) do
        if redis.call('exists', key) == 0 then
            redis.call('srem', tag, key)
            redis.call('srem', ARGV[2], key)
        end
    end

    if redis.call('exists', tag) == 0 then
        redis.call('srem', ARGV[1], tag)
    end
end

if redis.call('exists', ARGV[2]) == 1 then
    local keys = redis.call('smembers', ARGV[2])
    for _,key in ipairs(keys) do
        if redis.call('exists', key) == 0 then
            redis.call('srem', ARGV[2], key)
        end
    end
end
LUA;

        $this->evalSha(
            $this->scriptLoad($lua),
            [
                $this->getAllTagsKey(), // Argv 1
                $this->getAllKeysKey(), // Argv 2
            ],
            0
        );
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
        // Do not use transaction ! Otherwise we cannot read to remove old tags.
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
        $keys = $this->client()->sinter($tags);

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
        $keys = $this->client()->sunion($tags);

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
     */
    public function getKeysNotMatchingAnyTags(
        array $tags,
        bool $prepareTags = true,
        bool $removeMissingKeys = false,
        bool $unprepare = false
    ): array {
        $tags = $this->prepareTags($tags, $prepareTags);
        $keys = $this->client()->sdiff(array_merge([$this->getAllKeysKey()], $tags));

        return $this->filterKeys($keys, $removeMissingKeys, $unprepare);
    }

    /**
     * Return redis client.
     *
     * @return PredisClient|Redis
     */
    public function client()
    {
        return $this->connection->client();
    }

    /**
     * Store an item, with real tag support, using received list type for tags.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds If is 0 or negative, it will hold forever.
     * @param array $tags
     * @param bool $prepareKey
     * @param bool $prepareTags
     * @param array $tagsExpiration
     */
    public function putWithTags(
        string $key,
        $value,
        int $seconds = 0,
        array $tags = [],
        bool $prepareKey = true,
        bool $prepareTags = true,
        array $tagsExpiration = []
    ) {
        //TODO: find a way to read current tags from LUA so we can fully operate redis from a single LUA script.
        $currentMetaData = $this->decodeMetadata($this->getMetadata($key, $prepareKey));
        $currentTags     = $this->getFieldMetadataTags($currentMetaData);
        $key             = $this->prepareKey($key, $prepareKey);
        $tags            = $this->prepareTags($tags, $prepareTags);
        $tagsExpiration  = $this->prepareTagsExpiration($tagsExpiration, $prepareTags);
        list($data, $metadata) = $this->encode($value, $tags);

        $luaPrepend = <<<LUA
if not redis.call('hmset', ARGV[1], ARGV[2], ARGV[3], ARGV[4], ARGV[5]) then
    return redis.error_reply('Could not store key ' .. ARGV[1]);
end
LUA;

        $this->putWithLua(
            $key,
            $data,
            $metadata,
            $seconds,
            $currentTags,
            $tags,
            $tagsExpiration,
            $luaPrepend
        );
    }

    /**
     * Multi hgetall
     *
     * @param array $keys
     * @return mixed
     */
    public function mhgetall(array $keys)
    {
        $lua = <<<LUA
local r = {}
for _,key in pairs(KEYS) do
    r[#r+1] = redis.call("hgetall", key)
end
return r
LUA;

        return $this->evalSha($this->scriptLoad($lua), $keys, count($keys));
    }

    /**
     * Multi hget on a single field.
     *
     * @param array $keys
     * @param string $field
     * @return mixed
     */
    public function mhget(array $keys, string $field)
    {
        $lua = <<<LUA
local r = {}
for _,key in pairs(KEYS) do
    r[#r+1] = redis.call("hget", key, ARGV[1])
end
return r
LUA;

        $args   = $keys;
        $args[] = $field; // Argv 1

        return $this->evalSha($this->scriptLoad($lua), $args, count($keys));
    }

    /**
     * Multi hmget.
     *
     * @param array $keys
     * @param array $fields
     * @return mixed
     */
    public function mhmget(array $keys, array $fields)
    {
        $lua    = <<<LUA
local r = {}
local fields = loadstring("return " .. ARGV[1])()
for _,key in pairs(KEYS) do
    local tmp = {'hmget', key, unpack(fields)}
    r[#r+1] = redis.call(unpack(tmp))
end
return r
LUA;
        $args   = $keys;
        $args[] = $this->generateLuaTable($fields); // Argv 1

        return $this->evalSha($this->scriptLoad($lua), $args, count($keys));
    }

    /**
     * Return the global key which contains all existing keys from namespace.
     *
     * @return string
     */
    public function getAllKeysKey(): string
    {
        return $this->prefix . self::GLUE . self::ALL_KEYS_KEY;
    }

    /**
     * Return the global key which contains all existing keys from namespace.
     *
     * @return string
     */
    public function getAllTagsKey(): string
    {
        return $this->prefix . self::GLUE . self::ALL_TAGS_KEY;
    }

    /**
     * Encode the given value, for storage, and generates data and metadata.
     *
     * @param $value
     * @param array $tags
     * @return array
     */
    protected function encode($value, $tags = []): array
    {
        list ($value, $dataType) = StorageHelper::encodeForStorage($value);

        return [
            $value,
            json_encode(
                [
                    self::FIELD_METADATA_TAGS      => $tags,
                    self::FIELD_METADATA_DATA_TYPE => $dataType
                ]
            )
        ];
    }

    /**
     * Decode the given data and metadata.
     *
     * @param mixed $data
     * @param array $metadata
     * @return mixed
     */
    protected function decode($data, array $metadata)
    {
        return StorageHelper::decodeFromStorage($data, $this->getFieldMetadataDataType($metadata) ?? 'NULL');
    }

    /**
     * Returns the data, from key.
     *
     * @param string $key
     * @return mixed
     */
    protected function getData(string $key)
    {
        return $this->client()->hget($key, self::FIELD_DATA);
    }

    /**
     * Return the metadata string, from key.
     *
     * @param string $key
     * @param bool $prepareKey
     * @return null|string
     */
    protected function getMetadata(string $key, bool $prepareKey = true)
    {
        $key = $this->prepareKey($key, $prepareKey);

        return $this->client()->hget($key, self::FIELD_METADATA);
    }

    /**
     * Decode the metadata.
     *
     * @param string|null $metadata
     * @return array
     */
    protected function decodeMetadata(string $metadata = null): array
    {
        if (empty($metadata)) {
            $metadata = [];
        } else {
            $metadata = json_decode($metadata, true);
            // Unable to decode metadata. Return empty metadata.
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($metadata)) {
                return [];
            }
        }

        return $metadata;
    }

    /**
     * Return the tags from metadata, from data array.
     *
     * @param array $metadata
     * @return array
     */
    protected function getFieldMetadataTags(array $metadata): array
    {
        return $metadata[self::FIELD_METADATA_TAGS] ?? [];
    }

    /**
     * Return the data type, from metadata array.
     *
     * @param array $metadata
     * @return string|null
     */
    protected function getFieldMetadataDataType(array $metadata)
    {
        return $metadata[self::FIELD_METADATA_DATA_TYPE] ?? null;
    }

    /**
     * Format the array to return an LUA valid string for table (equivalent of array from php).
     *
     * @param array $array
     * @param bool $ignoreKeys
     * @return string
     */
    protected function generateLuaTable(array $array, $ignoreKeys = true): string
    {
        if (empty ($array)) {
            return '{}';
        }

        if ($ignoreKeys) {
            $luaTable = array_map(
                function ($tag) {
                    return '"' . addslashes($tag) . '"';
                },
                $array
            );
        } else {
            $luaTable = [];
            foreach ($array as $key => $value) {
                $luaTable[] = '["' . addslashes($key) . '"] = "' . addslashes($value) . '"';
            }
        }

        return '{' . implode(', ', $luaTable) . '}';
    }

    /**
     * Flush the Lua scripts.
     */
    public function flushScript()
    {
        $this->rawCommand('SCRIPT', 'FLUSH');
    }

    /**
     * Flush the db.
     */
    public function flushDb()
    {
        $this->client()->flushdb();
    }

    /**
     * Load a script into the scripts cache, without executing it.
     *
     * @param string $script
     * @return string
     */
    public function scriptLoad(string $script)
    {
        return $this->rawCommand('SCRIPT', 'LOAD', $script);
    }

    /**
     * Returns information about the existence of the scripts in the script cache.
     *
     * @param string $scriptSha1
     * @return bool
     */
    public function scriptExists(string $scriptSha1): bool
    {
        $result = $this->rawCommand('SCRIPT', 'EXISTS', $scriptSha1);

        return (bool) reset($result);
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
            ? $this->prefix . self::GLUE . self::KEYS_NAMESPACE . self::GLUE . $key
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
        return str_replace($this->prefix . self::GLUE . self::KEYS_NAMESPACE . self::GLUE, '', $key);
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
     * Prepare the tag name, by adding the required prefixes.
     *
     * @param string $tag
     * @param bool $prepareTag
     * @return string
     */
    public function prepareTag(string $tag, bool $prepareTag = true): string
    {
        return $prepareTag
            ? $this->prefix . self::GLUE . self::TAGS_NAMESPACE . self::GLUE . $tag
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
        return str_replace($this->prefix . self::GLUE . self::TAGS_NAMESPACE . self::GLUE, '', $tag);
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
     * Prepare the array keys, by adding the required tag prefixes.
     *
     * @param array $tags
     * @param bool $prepareTags
     * @return array
     */
    protected function prepareTagsExpiration(array $tags, bool $prepareTags = true): array
    {
        if ($prepareTags == false) {
            return $tags;
        }

        $return = [];
        foreach ($tags as $key => $value) {
            $return[$this->prepareTag($key, true)] = $value;
        }

        return $return;
    }

    /**
     * Put data into Redis with LUA script.
     *
     * @param string $key
     * @param $data
     * @param string $metadata
     * @param int $seconds
     * @param array $currentTags
     * @param array $tags
     * @param array $tagsExpiration
     * @param string $luaPrepend
     * @param string $luaAppend
     * @return mixed
     */
    protected function putWithLua(
        string $key,
        $data,
        string $metadata,
        int $seconds,
        array $currentTags,
        array $tags,
        array $tagsExpiration,
        string $luaPrepend = '',
        string $luaAppend = ''
    ) {
        $lua = <<<LUA
$luaPrepend

if tonumber(ARGV[6]) > 0 then
    redis.call('expire', ARGV[1], ARGV[6])
else
    redis.call('persist', ARGV[1])
end

local newTags = loadstring("return " .. ARGV[9])()
local tagExpiration = loadstring("return " .. ARGV[10])()
for _,tag in ipairs(newTags) do
    redis.call('sadd', tag, ARGV[1])
    redis.call('sadd', ARGV[7], tag)
    if tagExpiration[tag] then
        redis.call('expire', tag, tagExpiration[tag])
    end
end
redis.call('sadd', ARGV[8], ARGV[1])

local oldTags = loadstring("return " .. ARGV[11])()
for _,tag in ipairs(oldTags) do
    redis.call('srem', tag, ARGV[1])
    if redis.call('exists', tag) == 0 then
        redis.call('srem', ARGV[7], tag)
    end
end

$luaAppend
LUA;

        $args = [
            $key,                                                     // Argv 1
            self::FIELD_DATA,                                         // Argv 2
            $data,                                                    // Argv 3
            self::FIELD_METADATA,                                     // Argv 4
            $metadata,                                                // Argv 5
            $seconds,                                                 // Argv 6
            $this->getAllTagsKey(),                                   // Argv 7
            $this->getAllKeysKey(),                                   // Argv 8
            $this->generateLuaTable(array_diff($tags, $currentTags)), // Argv 9
            $this->generateLuaTable($tagsExpiration, false),          // Argv 10
            $this->generateLuaTable(array_diff($currentTags, $tags)), // Argv 11
        ];

        return $this->evalSha($this->scriptLoad($lua), $args, 0);
    }

    /**
     * Throw exception that the client is not supported.
     *
     * @param $client
     * @throws \Exception
     */
    protected function throwUnsupportedClient($client)
    {
        throw new \Exception(
            'Unknown redis client. Supported clients: Predis & PhpRedis. "'
            . get_class($client)
            . '" received.'
        );
    }

    /**
     * Send arbitrary things to the redis server.
     *
     * @param string $command Required command to send to the server.
     * @param mixed ...$arguments Optional variable amount of arguments to send to the server.
     * @return mixed
     */
    public function rawCommand(string $command, ...$arguments)
    {
        $client = $this->client();

        if ($client instanceof PredisClient) {
            return $client->executeRaw(func_get_args());
        }

        if ($client instanceof Redis) {
            return $client->rawCommand($command, ...$arguments);
        }

        return $this->throwUnsupportedClient($client);
    }

    /**
     * Evaluate a LUA script server side, from the SHA1 hash of the script instead of the script itself.
     * In order to run this command Redis will have to have already loaded the script, either by running it or via
     * the SCRIPT LOAD command.
     *
     * @param string $scriptSha
     * @param array $args
     * @param int $numKeys
     * @return mixed
     * @throws RedisException
     */
    public function evalSha(string $scriptSha, array $args = [], int $numKeys = 0)
    {
        $client = $this->client();

        if ($client instanceof PredisClient) {
            try {
                return $client->evalsha($scriptSha, $numKeys, ...$args);
            } catch (ServerException $e) {
                throw new RedisException($e->getMessage());
            }
        }

        if ($client instanceof Redis) {
            $return    = $client->evalSha($scriptSha, $args, $numKeys);
            $lastError = $client->getLastError();
            $client->clearLastError();
            if (empty($lastError)) {
                return $return;
            }

            throw new RedisException($lastError);
        }

        return $this->throwUnsupportedClient($client);
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
        $metaData = $this->decodeMetadata($this->getMetadata($key, $prepareKey));
        $tags     = $this->getFieldMetadataTags($metaData);

        if ($unprepare) {
            return $this->unprepareTags($tags);
        }

        return $tags;
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
        $allKeys = $this->client()->smembers($this->getAllKeysKey());

        if ($unprepare) {
            return $this->unprepareKeys($allKeys);
        }

        return $allKeys;
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
        $allTags = $this->client()->smembers($this->getAllTagsKey());

        if ($unprepare) {
            return $this->unprepareTags($allTags);
        }

        return $allTags;
    }
}