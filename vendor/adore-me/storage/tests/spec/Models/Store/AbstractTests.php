<?php
namespace spec\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\ObjectBehavior;

abstract class AbstractTests extends ObjectBehavior
{
    protected $specPrefix = 'tests';

    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_is_initializable();

    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_method_getDriver_return_expected_object();

    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_method_getStoreName_returns_correct_string();

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_StoreInterface()
    {
        $this->shouldImplement(StoreInterface::class);
    }
}
