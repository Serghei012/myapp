<?php
namespace spec\AdoreMe\Common\Helpers;

use AdoreMe\Common\Helpers\ProviderHelper;
use PhpSpec\ObjectBehavior;

/** @var ProviderHelper $this */
class ProviderHelperSpec extends ObjectBehavior
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_env_returns_correct_data()
    {
        /** @var ProviderHelper $this */
        $this::env('test', 'default')->shouldBe('default');
        $this::env('test2')->shouldBeNull();
        putenv('test3=3');
        $this::env('test3')->shouldBe('3');
    }
}
