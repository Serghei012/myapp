<?php
namespace AdoreMe\Storage\Helpers;

use AdoreMe\Common\Helpers\ArrayHelper;
use AdoreMe\Common\Helpers\HttpHelper;
use AdoreMe\Common\Helpers\ProviderHelper;
use AdoreMe\Common\Models\HeaderBag;
use AdoreMe\Storage\Models\Store\MemcachedStore;
use AdoreMe\Storage\Models\Store\RedisStore;
use Illuminate\Container\Container;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class StorageHelper
{
    const ENV_IDENTIFIER = 'ENABLE_DEBUG_ADOREME_CACHE_STATUS';

    const STATUS_HIT     = 'HIT';     // Resource was found in cache.
    const STATUS_MISS    = 'MISS';    // Resource was not found in cache.
    const STATUS_PARTIAL = 'PARTIAL'; // Some resources were found in cache, while others were not, or were modified.
    const STATUS_BROKEN  = 'BROKEN';  // Resource found in cache, but cannot be used. Returned null.
    const STATUS_CREATED = 'CREATED'; // Resource was created in cache.
    const STATUS_DELETED = 'DELETED'; // Resource was deleted from cache.
    const STATUS_UNKNOWN = 'UNKNOWN'; // Unable to determine the cache status.

    /** @var array */
    protected static $storeStatus = [];
    /** @var array */
    protected static $storeKeyReference = [];
    /** @var array */
    protected static $storeTagReference = [];
    /** @var array */
    protected static $phpVariableCache = [];

    /**
     * Set the store last status.
     * Also adds a key status for given key, if requested.
     *
     * @param string $storeName
     * @param string $status
     * @param array $keyReference
     * @param array $tagReference
     */
    public static function setStoreLastStatus(
        //TODO: update header bag model to handle this logic, so is not calculated over and over again, but only when is needed.
        string $storeName,
        string $status,
        array $keyReference = [],
        array $tagReference = []
    ) {
        // Do nothing if debug mode is not enabled.
        if (! ProviderHelper::env('APP_DEBUG') && ! ProviderHelper::env(self::ENV_IDENTIFIER)) {
            return;
        }

        // Avoid parsing the store name over and over again, if is used multiple times.
        if (! array_key_exists($storeName, self::$phpVariableCache)) {
            $storeName                          = ucwords(Str::slug(Str::snake($storeName)), ' -');
            self::$phpVariableCache[$storeName] = $storeName;
        } else {
            $storeName = self::$phpVariableCache[$storeName];
        }

        self::$storeStatus[$storeName][$status] = $status;

        // Insert the key reference.
        if (! empty($keyReference)) {
            foreach ($keyReference as $item) {
                self::$storeKeyReference[$storeName][$item][] = $status;
            }
        } else {
            self::$storeKeyReference[$storeName] = [];
        }

        // Insert the tag reference.
        if (! empty($tagReference)) {
            foreach ($tagReference as $item) {
                self::$storeTagReference[$storeName][$item][] = $status;
            }
        } else {
            self::$storeTagReference[$storeName] = [];
        }

        $implodeCallback = function (string $k, array $v) {
            return $k . '=[' . implode(';', $v) . ']';
        };

        $headerBag = self::getHeaderBag();

        $headerBag->setAttribute($storeName . '-Status', self::calculateStoreStatus($storeName));
        $headerBag->setAttribute(
            $storeName . '-Key-Reference',
            ArrayHelper::implodeWithKeyAndValue(self::$storeKeyReference[$storeName], ' ; ', '=', $implodeCallback)
        );
        $headerBag->setAttribute(
            $storeName . '-Tag-Reference',
            ArrayHelper::implodeWithKeyAndValue(self::$storeTagReference[$storeName], ' ; ', '=', $implodeCallback)
        );
    }

    /**
     * Calculate the cache status.
     *
     * @param string $storeName
     * @return string
     */
    protected static function calculateStoreStatus(string $storeName): string
    {
        // Return the unique status.
        $arrayUnique = array_unique(self::$storeStatus[$storeName]);
        if (count($arrayUnique) == 1) {
            return reset($arrayUnique);
        }

        // If there is a status partial, return partial.
        if (in_array(self::STATUS_PARTIAL, $arrayUnique)) {
            return self::STATUS_PARTIAL;
        }

        // If there is a status broken, return partial.
        if (in_array(self::STATUS_BROKEN, $arrayUnique)) {
            return self::STATUS_BROKEN;
        }

        // If there is at least one status with hit and at least one with miss, then return partial.
        if (
            in_array(self::STATUS_HIT, $arrayUnique)
            && in_array(self::STATUS_MISS, $arrayUnique)
        ) {
            return self::STATUS_PARTIAL;
        }

        // Remove the created and deleted info, and if there is only one status remaining, return it.
        $arrayUnique = array_filter($arrayUnique, function($v) {
            return ! in_array($v, [self::STATUS_DELETED, self::STATUS_CREATED]);
        }, ARRAY_FILTER_USE_BOTH);
        if (count($arrayUnique) == 1) {
            return reset($arrayUnique);
        }

        return self::STATUS_UNKNOWN;
    }

    /**
     * Get the header bag from http helper.
     *
     * @return HeaderBag
     */
    protected static function getHeaderBag(): HeaderBag
    {
        return HttpHelper::getHeaderBag();
    }

    /**
     * Create memcached store based on given config.
     *
     * @param string $prefix
     * @param array $servers
     * @param string|null $persistentId
     * @param array $options
     * @param array $credentials
     * @return MemcachedStore
     */
    public static function makeMemcachedStore(
        string $prefix,
        array $servers,
        string $persistentId = null,
        array $options = [],
        array $credentials = []
    ): MemcachedStore
    {
        $connector = new MemcachedConnector();

        return new MemcachedStore(
            $connector->connect($servers, $persistentId, $options, $credentials),
            $prefix
        );
    }

    /**
     * Get memcached configuration.
     *
     * @param Container $app
     * @param string|null $forcePrefix
     * @return array
     */
    public static function getMemcachedConfiguration(Container $app, string $forcePrefix = null): array
    {
        $config       = $app['config']['cache.stores.memcached'];
        $servers      = $config['servers'];
        $persistentId = $config['persistent_id'] ?? null;
        $options      = $config['options'] ?? [];
        $credentials  = array_filter($config['sasl'] ?? []);
        $prefix       = $forcePrefix ?? $config['prefix'] ?? $app['config']['cache.prefix'];

        return [$prefix, $servers, $persistentId, $options, $credentials];
    }

    /**
     * Make memcached store using the configuration.
     *
     * @param Container $app
     * @param string $forcePrefix
     * @return MemcachedStore
     */
    public static function makeMemcachedStoreFromConfiguration(
        Container $app,
        string $forcePrefix = null
    ): MemcachedStore
    {
        return StorageHelper::makeMemcachedStore(
            ...self::getMemcachedConfiguration($app, $forcePrefix)
        );
    }

    /**
     * Create redis store based on given config.
     *
     * @param string $prefix
     * @param array $config
     * @param string $connection
     * @param string $client Supported for now Predis and PhpRedis
     * @return RedisStore
     */
    public static function makeRedisStore(
        string $prefix,
        array $config,
        string $connection = 'default',
        string $client = 'predis'
    ): RedisStore
    {
        $connector = new RedisManager($client, $config);

        return new RedisStore($connector->connection($connection), $prefix);
    }

    /**
     * Get redis configuration.
     *
     * @param Container $app
     * @param string|null $forcePrefix
     * @return array
     */
    public static function getRedisConfiguration(Container $app, string $forcePrefix = null): array
    {
        $cacheRedisConfig = $app['config']['cache.stores.redis'];
        $driver           = $cacheRedisConfig['driver'] ?? 'redis';
        $connection       = $cacheRedisConfig['connection'] ?? 'default';
        $prefix           = $forcePrefix ?? $cacheRedisConfig['prefix'] ?? $app['config']['cache.prefix'];
        $config           = $app['config']['database.' . $driver];
        $client           = Arr::pull($config, 'client', 'predis');

        return [$prefix, $config, $connection, $client];
    }

    /**
     * Make redis using the configuration.
     *
     * @param Container $app
     * @param string $forcePrefix
     * @return RedisStore
     */
    public static function makeRedisStoreFromConfiguration(Container $app, string $forcePrefix = null): RedisStore
    {
        return StorageHelper::makeRedisStore(
            ...self::getRedisConfiguration($app, $forcePrefix)
        );
    }

    /**
     * Encode the value for storage.
     * This function also makes sure that the reference is removed from given value,
     * as we don't want to have surprises that we modify the model after that put in cache, and we, unknowingly
     * modify the model from cache by reference.
     *
     * @param mixed $value
     * @return array of encoded value, and encode used by value.
     */
    public static function encodeForStorage($value): array
    {
        $dataType = gettype($value);
        switch ($dataType) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case 'NULL':
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'array':
                if (! ArrayHelper::hasObjectOrResource($value)) {
                    $value = json_encode($value);
                    break;
                }

                $dataType = 'serialized_array';
            // Break statement intentionally omitted.
            case 'object':
            case 'resource':
            default:
                $value = base64_encode(serialize($value));
        }

        return [$value, $dataType];
    }

    /**
     * Decode the value from storage.
     *
     * @param mixed $value
     * @param string $dataType
     * @return mixed
     */
    public static function decodeFromStorage($value, string $dataType)
    {
        switch ($dataType) {
            case 'boolean':
                return (bool) $value;
                break;
            case 'integer':
                return (int) $value;
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return json_decode($value, true);
            case 'serialized_array':
            case 'object':
            case 'resource':
                return unserialize(base64_decode($value));
                break;
            case 'NULL':
            default:
                return null;
                break;
        }
    }
}
