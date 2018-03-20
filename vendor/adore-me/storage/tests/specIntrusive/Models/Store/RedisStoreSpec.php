<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Models\Store\RedisStore;

class RedisStoreSpec extends AbstractTests
{
    use TestsForStoreThatSupportKeyExpirationAndTagsTrait;
    use TestsForStoreThatSupportKeyExpirationTrait;
    use TestsForStoreThatSupportNegativeCounterForIncrementTrait;
    use TestsForStoreThatSupportTagsTrait;
    use TestsForStoreThatSupportTemporaryTagsTrait;

    /**
     * Construct the class.
     *
     * @var $this RedisStore
     * @param string $client
     */
    protected function beConstructedWithClient(string $client)
    {
        /** @var RedisStore $this */
        $config     = StorageHelper::getRedisConfiguration(app(), $this->specStoreNamespace);
        $config[3]  = $client;
        $redisStore = StorageHelper::makeRedisStore(...$config);

        $this->beConstructedWith(
            $redisStore->getDriver(),
            $this->specStoreNamespace
        );

        $this->flushAll();
    }

    function let()
    {
        $this->beConstructedWithClient('predis');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(RedisStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_has_active_testable_connection()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringHas('test');
    }
}
