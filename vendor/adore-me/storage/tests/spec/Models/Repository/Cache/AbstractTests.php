<?php
namespace spec\AdoreMe\Storage\Models\Repository\Cache;

use PhpSpec\ObjectBehavior;

abstract class AbstractTests extends ObjectBehavior
{
    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_is_initializable();

    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_implements_its_own_custom_named_CacheRepositoryInterface();
}
