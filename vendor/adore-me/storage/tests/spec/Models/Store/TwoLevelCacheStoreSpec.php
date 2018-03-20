<?php
namespace spec\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use AdoreMe\Storage\Interfaces\Store\TwoLevelCacheStoreInterface;
use AdoreMe\Storage\Models\Store\TwoLevelCacheStore;

/** @var TwoLevelCacheStore $this */
class TwoLevelCacheStoreSpec extends AbstractTests
{
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function let(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_is_initializable()
    {
        $this->shouldHaveType(TwoLevelCacheStore::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implements_TwoLevelCacheStoreInterface()
    {
        $this->shouldImplement(TwoLevelCacheStoreInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getDriver_return_expected_object()
    {
        $this->getDriver()->shouldBeArray();
        $this->getDriver()[TwoLevelCacheStore::SLOW_STORE]->shouldBeAnInstanceOf(StoreInterface::class);
        $this->getDriver()[TwoLevelCacheStore::FAST_STORE]->shouldBeAnInstanceOf(StoreInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_getStoreName_returns_correct_string()
    {
        $this->getStoreName()->shouldReturn('TwoLevelCache');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_HAS_method_uses_HAS_from_fastStore(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        /** @var TwoLevelCacheStore $this */
        $key        = 'test';
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->has($key, $prepareKey)->shouldBeCalled();
        $this->has($key, $prepareKey);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_HAS_MANY_method_uses_HAS_MANY_from_fastStore(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        /** @var TwoLevelCacheStore $this */
        $keys       = ['test1', 'test2', 'test3'];
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->hasMany($keys, $prepareKey)->shouldBeCalled();
        $this->hasMany($keys, $prepareKey);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_GET_method_uses_GET_from_fastStore(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        /** @var TwoLevelCacheStore $this */
        $key        = 'test';
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->get($key, $prepareKey)->shouldBeCalled();
        $this->get($key, $prepareKey);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_ADD_method_uses_ADD_from_fastStore_and_PUT_from_slowStore_and_return_true(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $key            = 'test';
        $value          = 'value';
        $seconds        = 10;
        $tags           = ['tag1', 'tag2'];
        $prepareKey     = true;
        $tagsExpiration = ['tag1' => 500];
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->add($key, $value, $seconds, [], $prepareKey, [])->shouldBeCalled()->willReturn(true);
        $slowStore->put($key, null, $seconds, $tags, $prepareKey, $tagsExpiration)->shouldBeCalled();
        $this->add($key, $value, $seconds, $tags, $prepareKey, $tagsExpiration)->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_ADD_method_uses_ADD_from_fastStore_and_return_false(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $key            = 'test';
        $value          = 'value';
        $seconds        = 10;
        $tags           = ['tag1', 'tag2'];
        $prepareKey     = true;
        $tagsExpiration = ['tag1' => 500];
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->add($key, $value, $seconds, [], $prepareKey, [])->shouldBeCalled()->willReturn(false);
        $this->add($key, $value, $seconds, $tags, $prepareKey, $tagsExpiration)->shouldReturn(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_PUT_method_uses_PUT_from_fastStore_and_PUT_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $key            = 'test';
        $value          = 'value';
        $seconds        = 10;
        $tags           = ['tag1', 'tag2'];
        $prepareKey     = true;
        $tagsExpiration = ['tag1' => 500];
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->put($key, $value, $seconds, [], $prepareKey, [])->shouldBeCalled();
        $slowStore->put($key, null, $seconds, $tags, $prepareKey, $tagsExpiration)->shouldBeCalled();
        $this->put($key, $value, $seconds, $tags, $prepareKey, $tagsExpiration);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_PUT_MANY_method_uses_PUT_MANY_from_fastStore_and_PUT_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $values         = ['key1' => 'value1', 'key2' => 'value2'];
        $seconds        = 10;
        $tags           = ['tag1', 'tag2'];
        $prepareKeys    = true;
        $tagsExpiration = ['tag1' => 500];
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->putMany($values, $seconds, [], $prepareKeys, [])->shouldBeCalled();
        $slowStore->putMany(['key1' => null, 'key2' => null], $seconds, $tags, $prepareKeys, $tagsExpiration)
            ->shouldBeCalled();
        $this->putMany($values, $seconds, $tags, $prepareKeys, $tagsExpiration);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_INCREMENT_method_uses_INCREMENT_from_fastStore(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        /** @var TwoLevelCacheStore $this */
        $key        = 'test';
        $value      = 5;
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->increment($key, $value, $prepareKey)->shouldBeCalled();
        $this->increment($key, $value, $prepareKey);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_DECREMENT_method_uses_DECREMENT_from_fastStore(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        /** @var TwoLevelCacheStore $this */
        $key        = 'test';
        $value      = 5;
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->decrement($key, $value, $prepareKey)->shouldBeCalled();
        $this->decrement($key, $value, $prepareKey);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_FOREVER_method_uses_FOREVER_from_fastStore_and_FOREVER_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $key            = 'test';
        $value          = 'value';
        $tags           = ['tag1', 'tag2'];
        $prepareKey     = true;
        $tagsExpiration = ['tag1' => 500];
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->forever($key, $value, [], $prepareKey, [])->shouldBeCalled();
        $slowStore->forever($key, null, $tags, $prepareKey, $tagsExpiration)->shouldBeCalled();
        $this->forever($key, $value, $tags, $prepareKey, $tagsExpiration);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_FORGET_method_uses_FORGET_from_fastStore_and_FORGET_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $key        = 'test';
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->forget($key, $prepareKey)->shouldBeCalled();
        $slowStore->forget($key, $prepareKey)->shouldBeCalled();
        $this->forget($key, $prepareKey);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_FLUSH_ALL_method_uses_FLUSH_ALL_from_fastStore_and_FORGET_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->flushAll()->shouldBeCalled();
        $slowStore->flushAll()->shouldBeCalled();
        $this->flushAll();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_FLUSH_NAMESPACE_method_uses_FLUSH_NAMESPACE_from_fastStore_and_FORGET_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->flushNamespace()->shouldBeCalled();
        $slowStore->flushNamespace()->shouldBeCalled();
        $this->flushNamespace();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_COLLECT_GARBAGE_method_uses_COLLECT_GARBAGE_from_slowStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->collectGarbage()->shouldNotBeCalled();
        $slowStore->collectGarbage()->shouldBeCalled();
        $this->collectGarbage();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_FORGET_BY_TAGS_method_uses_FORGET_BY_TAGS_from_slowStore_and_FORGET_from_fastStore(
        StoreInterface $slowStore,
        StoreInterface $fastStore
    ) {
        /** @var TwoLevelCacheStore $this */
        $tags        = ['tag1', 'tag2'];
        $prepareTags = true;
        $unprepare   = false;

        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $slowStore->forgetByTags($tags, $prepareTags, $unprepare)->shouldBeCalled()->willReturn(
            ['unprepared:key1', 'unprepared:key2',]
        );
        $slowStore->unprepareKeys(['unprepared:key1', 'unprepared:key2'])->willReturn(['key1', 'key2']);
        $fastStore->forget('key1', $prepareTags)->shouldBeCalled()->willReturn(true);
        $fastStore->forget('key2', $prepareTags)->shouldBeCalled()->willReturn(true);
        $fastStore->prepareKeys(['key1', 'key2'])->shouldBeCalled()->willReturn(
            ['unprepared:key1', 'unprepared:key2',]
        );
        $this->forgetByTags($tags, $prepareTags, $unprepare);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $slowStore
     * @param StoreInterface|\PhpSpec\Wrapper\Collaborator $fastStore
     */
    function it_method_prepareKey_uses_prepareKey_from_fastStore(StoreInterface $slowStore, StoreInterface $fastStore)
    {
        /** @var TwoLevelCacheStore $this */
        $key        = 'test';
        $prepareKey = true;
        $this->beConstructedWith($slowStore, $fastStore, $this->specPrefix);

        $fastStore->prepareKey($key, $prepareKey)->shouldBeCalled();
        $this->prepareKey($key, $prepareKey);
    }
}
