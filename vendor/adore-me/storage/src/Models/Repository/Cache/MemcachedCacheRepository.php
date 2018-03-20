<?php
namespace AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\MemcachedCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\MemcachedStoreInterface;

class MemcachedCacheRepository extends CacheRepositoryAbstract implements MemcachedCacheRepositoryInterface
{
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new cache repository instance.
     *
     * @param MemcachedStoreInterface $store
     */
    public function __construct(MemcachedStoreInterface $store)
    {
        $this->store = $store;
    }
}
