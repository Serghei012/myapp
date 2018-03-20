<?php
namespace AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\TwoLevelCacheStoreInterface;

class TwoLevelCacheRepository extends CacheRepositoryAbstract implements TwoLevelCacheRepositoryInterface
{
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new cache repository instance.
     *
     * @param TwoLevelCacheStoreInterface $store
     */
    public function __construct(TwoLevelCacheStoreInterface $store)
    {
        $this->store = $store;
    }
}
