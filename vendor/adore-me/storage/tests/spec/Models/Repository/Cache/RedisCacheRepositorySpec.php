<?php
namespace spec\AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\RedisCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\RedisStoreInterface;
use AdoreMe\Storage\Models\Repository\Cache\RedisCacheRepository;

/** @var RedisCacheRepository */
class RedisCacheRepositorySpec extends AbstractTests
{
    /**
     * @param RedisStoreInterface|\PhpSpec\Wrapper\Collaborator $store
     */
    function let(RedisStoreInterface $store)
    {
        $this->beConstructedWith($store);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(RedisCacheRepository::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_its_own_custom_named_CacheRepositoryInterface()
    {
        $this->shouldImplement(RedisCacheRepositoryInterface::class);
    }
}
