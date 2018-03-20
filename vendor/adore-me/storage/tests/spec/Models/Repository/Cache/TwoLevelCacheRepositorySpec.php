<?php
namespace spec\AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\TwoLevelCacheStoreInterface;
use AdoreMe\Storage\Models\Repository\Cache\TwoLevelCacheRepository;

/** @var TwoLevelCacheRepository */
class TwoLevelCacheRepositorySpec extends AbstractTests
{
    /**
     * @param TwoLevelCacheStoreInterface|\PhpSpec\Wrapper\Collaborator $store
     */
    function let(TwoLevelCacheStoreInterface $store)
    {
        $this->beConstructedWith($store);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(TwoLevelCacheRepository::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_its_own_custom_named_CacheRepositoryInterface()
    {
        $this->shouldImplement(TwoLevelCacheRepositoryInterface::class);
    }
}
