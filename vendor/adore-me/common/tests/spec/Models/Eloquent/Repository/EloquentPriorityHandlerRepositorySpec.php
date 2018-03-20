<?php
namespace spec\AdoreMe\Common\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface;
use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentPriorityHandlerRepository;
use PhpSpec\ObjectBehavior;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use stubs\AdoreMe\Common\Traits\PhpSpecMockEloquentTrait;

/** @var EloquentPriorityHandlerRepository $this */
class EloquentPriorityHandlerRepositorySpec extends ObjectBehavior
{
    use PhpSpecMockEloquentTrait;

    /**
     * @param Resolver|\PhpSpec\Wrapper\Collaborator $resolver
     * @param Connection|\PhpSpec\Wrapper\Collaborator $connectionInterface
     * @param Processor|\PhpSpec\Wrapper\Collaborator $processor
     * @param EloquentPriorityHandlerAttributes|\PhpSpec\Wrapper\Collaborator $model
     */
    function let(Resolver $resolver, Connection $connectionInterface, Processor $processor, EloquentPriorityHandlerAttributes $model)
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->beAnInstanceOf(EloquentPriorityHandlerRepository::class);

        $this->mockEloquentCollaborators($resolver, $connectionInterface, $processor);
        /** @noinspection PhpUndefinedMethodInspection */
        $model->getConnectionResolver()->willReturn($resolver);

        $this->beConstructedWith($model);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->shouldImplement(RepositoryInterface::class);
        $this->shouldImplement(PriorityHandlerRepositoryInterface::class);
    }
}
