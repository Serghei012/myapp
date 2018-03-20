<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;

trait TestsForStoreThatDoesNotSupportTagsTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_PUT_with_tags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringPut('a', null, 1, ['a']);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_allow_to_PUT_without_tags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringPut('a', null, 1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_PUT_MANY_with_tags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringPutMany(['a' => null, 'b' => 'null'], 1, ['a']);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_allow_to_PUT_MANY_without_tags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringPutMany(['a' => null, 'b' => 'null'], 1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_use_getAllTags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringGetAllTags();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_do_nothing_during_collectGarbage()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringCollectGarbage();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_use_forgetByTags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringForgetByTags([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_use_getKeysMatchingTags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringGetKeysMatchingTags([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_use_getKeysMatchingAnyTags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringGetKeysMatchingAnyTags([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_allow_to_use_getKeysNotMatchingAnyTags()
    {
        /** @var StoreInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringGetKeysNotMatchingAnyTags([]);
    }
}
