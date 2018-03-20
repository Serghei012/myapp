<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Models\Store\MemcachedStore;

class MemcachedStoreSpec extends AbstractTests
{
    use TestsForStoreThatDoesNotSupportTagsTrait;
    use TestsForStoreThatSupportKeyExpirationTrait;

    function let()
    {
        /** @var MemcachedStore $this */
        $memcachedStore = StorageHelper::makeMemcachedStoreFromConfiguration(app());

        $this->beConstructedWith($memcachedStore->getDriver(), $this->specStoreNamespace);

        $this->flushAll();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(MemcachedStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_has_active_testable_connection()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringHas('test');
    }
}
