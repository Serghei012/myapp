<?php
namespace AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\PhpStaticVariableStoreInterface;

class PhpStaticVariableCacheRepository extends CacheRepositoryAbstract implements PhpStaticVariableCacheRepositoryInterface
{
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new cache repository instance.
     *
     * @param PhpStaticVariableStoreInterface $store
     */
    public function __construct(PhpStaticVariableStoreInterface $store)
    {
        $this->store = $store;
    }
}
