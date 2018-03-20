<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Models\Store\TwoLevelCacheStore;
use PhpSpec\Wrapper\Subject;

class TwoLevelCacheStoreSpec extends AbstractTests
{
    use TestsForStoreThatSupportKeyExpirationAndTagsTrait;
    use TestsForStoreThatSupportKeyExpirationTrait;
    use TestsForStoreThatSupportTagsTrait;

    function let()
    {
        /** @var TwoLevelCacheStore $this */
        $slowStore = StorageHelper::makeRedisStoreFromConfiguration(app(), $this->specStoreNamespace);
        $fastStore = StorageHelper::makeMemcachedStoreFromConfiguration(app(), $this->specStoreNamespace);

        $this->beConstructedWith($slowStore, $fastStore, $this->specStoreNamespace);

        $this->flushAll();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(TwoLevelCacheStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_has_active_testable_connection()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringHas('test');
    }

    /**
     * Change the namespace via reflection.
     *
     * @param string $namespace
     */
    protected function specChangeNamespaceViaReflection($namespace)
    {
        /** @var TwoLevelCacheStore $this */
        /** @var Subject $stores */
        $stores = $this->getDriver();
        $stores = $stores->getWrappedObject();

        $slowStore  = $stores[TwoLevelCacheStore::SLOW_STORE];
        $reflection = new \ReflectionClass($slowStore);
        $property   = $reflection->getProperty('prefix');
        $property->setAccessible(true);
        $property->setValue($slowStore, $namespace);

        $fastStore  = $stores[TwoLevelCacheStore::FAST_STORE];
        $reflection = new \ReflectionClass($fastStore);
        $property   = $reflection->getProperty('prefix');
        $property->setAccessible(true);
        $property->setValue($fastStore, $namespace);

        $model      = $this->getWrappedObject();
        $reflection = new \ReflectionClass($model);
        $property   = $reflection->getProperty('slowStore');
        $property->setAccessible(true);
        $property->setValue($model, $slowStore);
        $property = $reflection->getProperty('fastStore');
        $property->setAccessible(true);
        $property->setValue($model, $fastStore);
    }
}
