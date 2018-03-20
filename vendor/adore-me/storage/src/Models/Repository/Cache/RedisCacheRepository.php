<?php
namespace AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\RedisCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\RedisStoreInterface;

class RedisCacheRepository extends CacheRepositoryAbstract implements RedisCacheRepositoryInterface
{
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new cache repository instance.
     *
     * @param RedisStoreInterface $store
     */
    public function __construct(RedisStoreInterface $store)
    {
        $this->store = $store;
    }
}
