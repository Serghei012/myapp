<?php
namespace spec\AdoreMe\Common\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentRepository;
use PhpSpec\ObjectBehavior;
use laravel\AdoreMe\Common\Models\Eloquent\Model;

/** @var EloquentRepository $this */
class EloquentRepositorySpec extends ObjectBehavior
{
    function let()
    {
        /** @var EloquentRepository $this */
        $this->beAnInstanceOf(EloquentRepository::class);
        $this->beConstructedWith(new Model());
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var EloquentRepository $this */
        $this->shouldImplement(RepositoryInterface::class);
    }
}
