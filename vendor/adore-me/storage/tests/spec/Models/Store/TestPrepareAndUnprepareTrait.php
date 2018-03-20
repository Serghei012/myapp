<?php
namespace spec\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestPrepareAndUnprepareTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_prepareKey_return_string()
    {
        /** @var StoreInterface $this */
        /** @var Subject $result */
        $result = $this->prepareKey('test');
        $result->shouldBeString();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_preparing_and_un_preparing_a_key_will_return_the_initial_key_string()
    {
        /** @var StoreInterface $this */
        /** @var Subject $preparedKey */
        /** @var Subject $unpreparedKey */
        $key           = 'test';
        $preparedKey   = $this->prepareKey($key);
        $unpreparedKey = $this->unprepareKey($preparedKey->getWrappedObject());
        $unpreparedKey->shouldReturn($key);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_prepareKeys_return_array()
    {
        /** @var StoreInterface $this */
        /** @var Subject $result */
        $result = $this->prepareKeys(['test']);
        $result->shouldBeArray();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_preparing_and_un_preparing_keys_will_return_the_initial_keys_array()
    {
        /** @var StoreInterface $this */
        /** @var Subject $preparedKeys */
        /** @var Subject $unpreparedKeys */
        $keys           = ['test', 'test2'];
        $preparedKeys   = $this->prepareKeys($keys);
        $unpreparedKeys = $this->unprepareKeys($preparedKeys->getWrappedObject());
        $unpreparedKeys->shouldIterateAs($keys);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_prepareTag_return_string()
    {
        /** @var StoreInterface $this */
        /** @var Subject $result */
        $result = $this->prepareTag('test');
        $result->shouldBeString();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_preparing_and_un_preparing_a_tag_will_return_the_initial_tag_string()
    {
        /** @var StoreInterface $this */
        /** @var Subject $preparedTag */
        /** @var Subject $unpreparedTag */
        $tag           = 'test';
        $preparedTag   = $this->prepareTag($tag);
        $unpreparedTag = $this->unprepareTag($preparedTag->getWrappedObject());
        $unpreparedTag->shouldReturn($tag);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_method_prepareTags_return_array()
    {
        /** @var StoreInterface $this */
        /** @var Subject $result */
        $result = $this->prepareTags(['test']);
        $result->shouldBeArray();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_preparing_and_un_preparing_tags_will_return_the_initial_tags_array()
    {
        /** @var StoreInterface $this */
        /** @var Subject $preparedTags */
        /** @var Subject $unpreparedTags */
        $tags           = ['test', 'test2'];
        $preparedTags   = $this->prepareTags($tags);
        $unpreparedTags = $this->unprepareTags($preparedTags->getWrappedObject());
        $unpreparedTags->shouldIterateAs($tags);
    }
}
