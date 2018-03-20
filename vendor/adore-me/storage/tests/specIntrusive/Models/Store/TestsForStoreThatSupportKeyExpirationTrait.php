<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestsForStoreThatSupportKeyExpirationTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_key_expiration_should_work()
    {
        /** @var StoreInterface $this */
        $this->put('a', 'a', 1);

        sleep(2);

        /** @var Subject $result */
        $result = $this->has('a');
        $result->shouldBe(false);
    }
}
