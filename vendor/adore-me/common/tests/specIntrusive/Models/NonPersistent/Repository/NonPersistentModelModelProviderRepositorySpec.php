<?php
namespace specIntrusive\AdoreMe\Common\Models\NonPersistent\Repository;

use laravel\AdoreMe\Common\Models\NonPersistent\Model;
use laravel\AdoreMe\Common\Models\NonPersistent\Repository\NonPersistentModelModelProviderRepository;
use PhpSpec\Laravel\LaravelObjectBehavior;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var NonPersistentModelModelProviderRepository $this */
class NonPersistentModelModelProviderRepositorySpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;

    function let()
    {
        /** @var NonPersistentModelModelProviderRepository $this */
        $this->beAnInstanceOf(NonPersistentModelModelProviderRepository::class);
        $this->beConstructedWith(new Model());
    }
}
