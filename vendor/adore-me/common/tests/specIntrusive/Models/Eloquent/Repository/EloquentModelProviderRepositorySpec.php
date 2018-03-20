<?php
namespace specIntrusive\AdoreMe\Common\Models\Eloquent\Repository;

use laravel\AdoreMe\Common\Models\Eloquent\Model;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentModelProviderRepository;
use PhpSpec\Laravel\LaravelObjectBehavior;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var EloquentModelProviderRepository $this */
class EloquentModelProviderRepositorySpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;

    function let()
    {
        /** @var EloquentModelProviderRepository $this */
        $this->beAnInstanceOf(EloquentModelProviderRepository::class);
        $this->beConstructedWith(new Model());
    }
}
