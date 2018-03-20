<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestsForStoreThatSupportTemporaryTagsTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_temporary_tags_should_work()
    {
        /** @var StoreInterface $this */
        $this->put(
            'a',
            'test',
            60,
            [
                'tag_1',
                'tag_2',
                'temporary_tag'
            ],
            true,
            [
                'temporary_tag' => 1
            ]
        );

        /** @var Subject $result */
        $result = $this->getKeysMatchingTags(['temporary_tag'], true, true, true);
        $result->shouldIterateAs(
            [
                'a'
            ]
        );

        sleep(2);

        /** @var Subject $result */
        $result = $this->getKeysMatchingTags(['temporary_tag']);
        $result->shouldIterateAs([]);
    }
}
