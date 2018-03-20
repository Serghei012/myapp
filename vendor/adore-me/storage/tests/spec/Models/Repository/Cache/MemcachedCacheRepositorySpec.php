<?php
namespace spec\AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\MemcachedCacheRepositoryInterface;
use AdoreMe\Storage\Models\Repository\Cache\MemcachedCacheRepository;
use AdoreMe\Storage\Models\Store\MemcachedStore;

/** @var MemcachedCacheRepository */
class MemcachedCacheRepositorySpec extends AbstractTests
{
    /**
     * @param MemcachedStore|\PhpSpec\Wrapper\Collaborator $store
     */
    function let(MemcachedStore $store)
    {
        $this->beConstructedWith($store);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(MemcachedCacheRepository::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_its_own_custom_named_CacheRepositoryInterface()
    {
        $this->shouldImplement(MemcachedCacheRepositoryInterface::class);
    }
}
