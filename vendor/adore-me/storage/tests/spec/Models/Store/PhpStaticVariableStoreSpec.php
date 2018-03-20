<?php
namespace spec\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\PhpStaticVariableStoreInterface;
use AdoreMe\Storage\Models\Store\PhpStaticVariableStore;
use PhpSpec\Wrapper\Subject;

class PhpStaticVariableStoreSpec extends AbstractTests
{
    use TestPrepareAndUnprepareTrait;

    function let()
    {
        $this->beConstructedWith($this->specPrefix);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(PhpStaticVariableStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_PhpStaticVariableStoreInterface()
    {
        $this->shouldImplement(PhpStaticVariableStoreInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getDriver_return_expected_object()
    {
        /** @var Subject $driver */
        $driver = $this->getDriver();
        $driver->shouldBeArray();
        $driver->shouldHaveKey(PhpStaticVariableStore::DATA);
        $driver->shouldHaveKey(PhpStaticVariableStore::TAGS);
        $driver->shouldHaveKey(PhpStaticVariableStore::METADATA);
        $driver[PhpStaticVariableStore::DATA]->shouldBeArray();
        $driver[PhpStaticVariableStore::TAGS]->shouldBeArray();
        $driver[PhpStaticVariableStore::METADATA]->shouldBeArray();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getStoreName_returns_correct_string()
    {
        $this->getStoreName()->shouldReturn('PhpStaticVariable');
    }
}
