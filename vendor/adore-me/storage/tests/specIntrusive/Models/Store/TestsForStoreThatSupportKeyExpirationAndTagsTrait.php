<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestsForStoreThatSupportKeyExpirationAndTagsTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_garbage_collector_should_have_deleted_all_orphaned_tags()
    {
        /** @var StoreInterface $this */
        $this->put(
            'test_key',
            'test_value',
            1,
            [
                'tag_1',
                'tag_2'
            ]
        );

        $this->put(
            'test_key2',
            'test_value2',
            1,
            [
                'tag_3',
                'tag_4'
            ]
        );

        sleep(2);

        $this->collectGarbage();

        /** @var Subject $result */
        /** @noinspection PhpInternalEntityUsedInspection */
        $result = $this->getNamespaceTags();
        $result->shouldIterateAs([]);
    }
}
