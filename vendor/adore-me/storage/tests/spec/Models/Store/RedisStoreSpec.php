<?php
namespace spec\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\RedisStoreInterface;
use AdoreMe\Storage\Models\Store\RedisStore;
use Illuminate\Redis\Connections\Connection;
use Predis\Client;

class RedisStoreSpec extends AbstractTests
{
    use TestPrepareAndUnprepareTrait;

    /**
     * @param Connection|\PhpSpec\Wrapper\Collaborator $connection
     * @param \PhpSpec\Wrapper\Collaborator|Client $client
     */
    function let(Connection $connection, Client $client)
    {
        $connection->client()->willReturn($client);

        $this->beConstructedWith($connection, $this->specPrefix);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(RedisStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_RedisStoreInterface()
    {
        $this->shouldImplement(RedisStoreInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getDriver_return_expected_object()
    {
        $this->getDriver()->beAnInstanceOf(Connection::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getStoreName_returns_correct_string()
    {
        $this->getStoreName()->shouldReturn('Redis');
    }
}
