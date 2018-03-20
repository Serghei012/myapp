<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Models\Store\PhpStaticVariableStore;

class PhpStaticVariableStoreSpec extends AbstractTests
{
    use TestsForStoreThatHasGetAllKeysTrait;
    use TestsForStoreThatSupportNegativeCounterForIncrementTrait;
    use TestsForStoreThatSupportTagsTrait;

    function let()
    {
        /** @var PhpStaticVariableStore $this */
        $this->beConstructedWith($this->specStoreNamespace);

        $this->flushAll();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(PhpStaticVariableStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_has_active_testable_connection()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringHas('test');
    }
}
