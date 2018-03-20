<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestsForStoreThatSupportTagsTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_removes_orphaned_tags_when_a_key_that_has_tags_is_removed()
    {
        /** @var StoreInterface $this */
        $this->flushAll();

        $this->forever('a', 'value1', ['tag 1', 'tag 2']);
        $this->forget('a');

        /** @var Subject $result */
        $result = $this->getTagsForKey('a');
        $result->shouldIterateAs([]);

        /** @var Subject $result */
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceTags();
        $result->shouldIterateAs([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_be_allowed_to_use_same_string_as_key_and_tag()
    {
        /** @var StoreInterface $this */
        $this->forever('a', null, ['a']);
        /** @var Subject $a */
        $a = $this->get('a');
        $a->shouldReturn(null);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_MATCHING_TAGS_should_return_test_key()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysMatchingTags(['tag_1', 'tag_2'], true, true, true);
        $result->shouldIterateAs(
            [
                'test_key'
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_MATCHING_TAGS_should_return_no_keys()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysMatchingTags(['tag_1', 'non_existing_tag_1']);
        $result->shouldIterateAs([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_MATCHING_ANY_TAGS_should_return_test_key()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysMatchingAnyTags(['tag_1', 'non_existing_tag_1'], true, true, true);
        $result->shouldIterateAs(
            [
                'test_key'
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_MATCHING_ANY_TAGS_should_return_test_key_and_test_key_3()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_5'
            ]
        );

        $this->forever(
            'test_key3',
            'test_value3',
            [
                'tag_3',
                'tag_1'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysMatchingAnyTags(['tag_1', 'non_existing_tag_1', 'tag_3'], true, true, true);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayValues(
            [
                'test_key',
                'test_key3',
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_MATCHING_ANY_TAGS_should_return_no_keys()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysMatchingAnyTags(['non_existing_tag_1', 'non_existing_tag_2']);
        $result->shouldIterateAs([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_NOT_MATCHING_ANY_TAGS_should_return_keys_test_key_and_test_key2()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysNotMatchingAnyTags(
            [
                'non_existing_tag_1',
                'non_existing_tag_2'
            ],
            true,
            true,
            true
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayValues(
            [
                'test_key',
                'test_key2'
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_NOT_MATCHING_ANY_TAGS_should_return_test_key2()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysNotMatchingAnyTags(
            [
                'tag_1',
                'non_existing_tag_1'
            ],
            true,
            true,
            true
        );
        $result->shouldIterateAs(
            [
                'test_key2'
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_NOT_MATCHING_ANY_TAGS_should_return_no_keys()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysNotMatchingAnyTags(
            [
                'tag_2'
            ]
        );
        $result->shouldIterateAs([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_KEYS_NOT_MATCHING_ANY_TAGS_should_return_test_key2_another_test()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        $this->forget('test_key');

        /** @var Subject $result */
        $result = $this->getKeysNotMatchingAnyTags(
            [
                'tag_1'
            ],
            true,
            true,
            true
        );
        $result->shouldIterateAs(
            [
                'test_key2'
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_after_FORGET_key_and_FORGET_BY_TAGS_there_should_be_no_key_in_database()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key2',
            'test_value2',
            [
                'tag_2',
                'tag_3'
            ]
        );

        $this->forget('test_key');
        /** @var Subject $result */
        $result = $this->forgetByTags(['tag_3'], true, true);
        $result->shouldIterateAs(
            [
                'test_key2'
            ]
        );

        /** @var Subject $result */
        $result = $this->hasMany(['test_key', 'test_key2']);
        $result->shouldIterateAs(
            [
                'test_key'  => false,
                'test_key2' => false,
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getTagsForKey_should_return_correct_data()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        /** @var Subject $result */
        $result = $this->getTagsForKey('test_key', true, true);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayValues(
            [
                'tag_1',
                'tag_2',
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getTagsForKey_should_return_correct_data_after_overwrite()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_2'
            ]
        );

        /** @var Subject $result */
        $result = $this->getTagsForKey('test_key', true, true);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldIterateAs(
            [
                'tag_2'
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_return_that_store_has_only_two_tags_in_database_tag1_and_tag2()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        /** @var Subject|string $tag1 */
        $tag1 = $this->prepareTag('tag_1');
        $tag1 = $tag1->getWrappedObject();

        /** @var Subject|string $tag2 */
        $tag2 = $this->prepareTag('tag_2');
        $tag2 = $tag2->getWrappedObject();

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceTags();
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayValues(
            [
                $tag1,
                $tag2,
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_have_tag_2_in_store_because_key_was_overwritten_with_new_tags()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_5'
            ]
        );

        /** @var Subject|string $tag1 */
        $tag1 = $this->prepareTag('tag_1');
        $tag1 = $tag1->getWrappedObject();

        /** @var Subject|string $tag5 */
        $tag5 = $this->prepareTag('tag_5');
        $tag5 = $tag5->getWrappedObject();

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceTags();
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayValues(
            [
                $tag1,
                $tag5,
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_not_be_able_to_do_GET_on_a_key_that_is_used_as_tag()
    {
        /** @var StoreInterface $this */
        $this->forever(
            'test_key',
            'test_value',
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->get('tag_1')->shouldReturn(null);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_FORGET_BY_TAGS_should_work()
    {
        /** @var StoreInterface $this */
        $this->flushAll();

        $this->forever('key1', 'value1');
        $this->forever('key2', 'value2', ['tag1', 'tag2']);
        $this->forever('key3', 'value3', ['tag2', 'tag3']);

        /** @var Subject $result */
        $result = $this->forgetByTags(['tag2'], true, true);
        $result->shouldNotContain('key1');
        $result->shouldContain('key2');
        $result->shouldContain('key3');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_FORGET_BY_TAGS_should_work_test_2()
    {
        /** @var StoreInterface $this */
        $this->flushAll();

        $this->forever('key1', 'value1');
        $this->forever('key2', 'value2', ['tag1', 'tag2']);
        $this->forever('key3', 'value3', ['tag2', 'tag3']);

        /** @var Subject $key1 */
        $key1 = $this->prepareKey('key1');
        $key1 = $key1->getWrappedObject();

        /** @var Subject $key2 */
        $key2 = $this->prepareKey('key2');
        $key2 = $key2->getWrappedObject();

        /** @var Subject $key3 */
        $key3 = $this->prepareKey('key3');
        $key3 = $key3->getWrappedObject();

        /** @var Subject $result */
        $result = $this->forgetByTags(['tag2'], true, false);
        $result->shouldNotContain($key1);
        $result->shouldContain($key2);
        $result->shouldContain($key3);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_FORGET_BY_TAGS_should_work_test_3()
    {
        /** @var StoreInterface $this */
        $this->flushAll();

        $this->forever('key1', 'value1');
        $this->forever('key2', 'value2', ['tag1', 'tag2']);
        $this->forever('key3', 'value3', ['tag2', 'tag3']);

        /** @var Subject $tag2 */
        $tag2 = $this->prepareTag('tag2');
        $tag2 = $tag2->getWrappedObject();

        /** @var Subject $key1 */
        $key1 = $this->prepareKey('key1');
        $key1 = $key1->getWrappedObject();

        /** @var Subject $key2 */
        $key2 = $this->prepareKey('key2');
        $key2 = $key2->getWrappedObject();

        /** @var Subject $key3 */
        $key3 = $this->prepareKey('key3');
        $key3 = $key3->getWrappedObject();

        /** @var Subject $result */
        $result = $this->forgetByTags([$tag2], false, false);
        $result->shouldNotContain($key1);
        $result->shouldContain($key2);
        $result->shouldContain($key3);
    }
}
