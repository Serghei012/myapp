<?php
namespace spec\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\MemcachedStoreInterface;
use AdoreMe\Storage\Models\Store\MemcachedStore;
use Memcached;

class MemcachedStoreSpec extends AbstractTests
{
    use TestPrepareAndUnprepareTrait;

    /**
     * @param Memcached|\PhpSpec\Wrapper\Collaborator $memcached
     */
    function let(Memcached $memcached)
    {
        $this->beConstructedWith($memcached, $this->specPrefix);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(MemcachedStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_MemcachedStoreInterface()
    {
        $this->shouldImplement(MemcachedStoreInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getDriver_return_expected_object()
    {
        $this->getDriver()->beAnInstanceOf(Memcached::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getStoreName_returns_correct_string()
    {
        $this->getStoreName()->shouldReturn('Memcached');
    }
}
