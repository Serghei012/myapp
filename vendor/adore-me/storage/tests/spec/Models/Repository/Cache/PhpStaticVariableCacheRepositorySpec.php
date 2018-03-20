<?php
namespace spec\AdoreMe\Storage\Models\Repository\Cache;

use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\PhpStaticVariableStoreInterface;
use AdoreMe\Storage\Models\Repository\Cache\PhpStaticVariableCacheRepository;

/** @var PhpStaticVariableCacheRepository */
class PhpStaticVariableCacheRepositorySpec extends AbstractTests
{
    /**
     * @param PhpStaticVariableStoreInterface|\PhpSpec\Wrapper\Collaborator $store
     */
    function let(PhpStaticVariableStoreInterface $store)
    {
        $this->beConstructedWith($store);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(PhpStaticVariableCacheRepository::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_its_own_custom_named_CacheRepositoryInterface()
    {
        $this->shouldImplement(PhpStaticVariableCacheRepositoryInterface::class);
    }
}
