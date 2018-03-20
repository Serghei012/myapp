<?php
namespace spec\AdoreMe\Common\Models\Eloquent;

use AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use spec\AdoreMe\Common\Traits\ModelTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMockEloquentTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

/** @var EloquentPriorityHandlerAttributes $this */
class EloquentPriorityHandlerAttributesSpec extends ObjectBehavior
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
        /** @var EloquentPriorityHandlerAttributes $this */
        $this->beAnInstanceOf(EloquentPriorityHandlerAttributes::class);
        $this->initLet($resolver, $connectionInterface, $processor);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var EloquentPriorityHandlerAttributes $this */
        $this->shouldHaveType(EloquentModel::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_have_attributes_set_in_fillable()
    {
        /** @var EloquentPriorityHandlerAttributes $this */
        /** @var Subject $result */
        $result = $this->getFillable();
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldContainArrayValues(
            [
                PriorityHandlerRepositoryInterface::PRIORITY,
                PriorityHandlerRepositoryInterface::ENABLED,
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_have_attributes_set_in_casts()
    {
        /** @var EloquentPriorityHandlerAttributes $this */
        /** @var Subject $result */
        $result = $this->getCasts();
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldContainArrayKeys(
            [
                PriorityHandlerRepositoryInterface::PRIORITY,
                PriorityHandlerRepositoryInterface::ENABLED,
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_should_allow_name_and_price_to_be_mass_assignable()
    {
        /** @var EloquentPriorityHandlerAttributes $this */
        $model = $this->newInstance(
            [
                PriorityHandlerRepositoryInterface::PRIORITY => 1,
                PriorityHandlerRepositoryInterface::ENABLED  => true,
            ]
        );

        $model->{PriorityHandlerRepositoryInterface::PRIORITY}->shouldReturn(1);
        $model->{PriorityHandlerRepositoryInterface::ENABLED}->shouldReturn(true);
    }
}
