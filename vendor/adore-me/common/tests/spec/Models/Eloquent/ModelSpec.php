<?php
namespace spec\AdoreMe\Common\Models\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use laravel\AdoreMe\Common\Models\Eloquent\Model;
use PhpSpec\ObjectBehavior;
use spec\AdoreMe\Common\Traits\ModelTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMockEloquentTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

/** @var Model $this */
class ModelSpec extends ObjectBehavior
{
    use PhpSpecMockEloquentTrait;
    use PhpSpecMatchersTrait;
    use ModelTrait;

    /**
     * @param Resolver|\PhpSpec\Wrapper\Collaborator $resolver
     * @param Connection|\PhpSpec\Wrapper\Collaborator $connectionInterface
     * @param Processor|\PhpSpec\Wrapper\Collaborator $processor
     */
    public function let(Resolver $resolver, Connection $connectionInterface, Processor $processor)
    {
        /** @var Model $this */
        $this->beAnInstanceOf(Model::class);
        $this->initLet($resolver, $connectionInterface, $processor);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var Model $this */
        $this->shouldHaveType(EloquentModel::class);
    }
}
