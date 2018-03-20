<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use PhpSpec\Wrapper\Subject;

trait TestsForStoreThatSupportNegativeCounterForIncrementTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_INCREMENT_with_1_on_a_key_added_with_put_and_value_minus_2_should_return_minus_1()
    {
        /** @var StoreInterface $this */
        $this->put('a', -2);
        /** @var Subject $increment */
        $increment = $this->increment('a', 1);
        $increment->shouldReturn(-1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_with_1_return_integer_minus_1()
    {
        /** @var StoreInterface $this */
        /** @var Subject $decrement */
        $decrement = $this->decrement('a', 1);
        $decrement->shouldReturn(-1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_existing_key_that_has_value_minus_1_with_minus_1_return_integer_minus_2()
    {
        /** @var StoreInterface $this */
        $this->decrement('a', 1);
        /** @var Subject $decrement */
        $decrement = $this->decrement('a', 1);
        $decrement->shouldReturn(-2);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_multiple_times_should_work_as_intended_to_negative()
    {
        /** @var StoreInterface $this */
        $this->decrement('a', 1);
        $this->decrement('a', 1);
        /** @var Subject $decrement */
        $decrement = $this->decrement('a', 1);
        $decrement->shouldReturn(-3);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_with_1_and_GET_should_work_and_return_integer_minus_1()
    {
        /** @var StoreInterface $this */
        $this->decrement('a', 1);
        /** @var Subject $a */
        $a = $this->get('a');
        $a->shouldReturn(-1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_with_1_on_a_key_added_with_put_and_value_minus_1_should_return_minus_2()
    {
        /** @var StoreInterface $this */
        $this->put('a', -1, 5);
        /** @var Subject $decrement */
        $decrement = $this->decrement('a', 1);
        $decrement->shouldReturn(-2);
    }
}
