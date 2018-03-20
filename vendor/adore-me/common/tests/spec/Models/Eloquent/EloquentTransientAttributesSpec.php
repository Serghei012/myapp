<?php
namespace spec\AdoreMe\Common\Models\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentTransientAttributes;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use spec\AdoreMe\Common\Traits\ModelTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMockEloquentTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

/** @var EloquentTransientAttributes $this */
class EloquentTransientAttributesSpec extends ObjectBehavior
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
        /** @var EloquentTransientAttributes $this */
        $this->beAnInstanceOf(EloquentTransientAttributes::class);
        $this->initLet($resolver, $connectionInterface, $processor);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var EloquentTransientAttributes $this */
        $this->shouldHaveType(EloquentModel::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_transient_attribute_should_be_assignable_go_through_mutator_and_return_correct_casted_values()
    {
        /** @var EloquentTransientAttributes $this */
        $this->{EloquentTransientAttributes::TEST_TRANSIENT}                  = 1;
        $this->{EloquentTransientAttributes::TEST_TRANSIENT_WITH_SET_MUTATOR} = 2;
        $this->{EloquentTransientAttributes::TEST_TRANSIENT_WITH_GET_MUTATOR} = 3;

        /** @var Subject $result */
        $result = $this->{EloquentTransientAttributes::TEST_TRANSIENT};
        $result->shouldBe('1');

        /** @var Subject $result */
        $result = $this->{EloquentTransientAttributes::TEST_TRANSIENT_WITH_SET_MUTATOR};
        $result->shouldBe((string) EloquentTransientAttributes::$mutatedOnSetTransientValue);

        /** @var Subject $result */
        $result = $this->{EloquentTransientAttributes::TEST_TRANSIENT_WITH_GET_MUTATOR};
        $result->shouldBe(EloquentTransientAttributes::$mutatedOnGetTransientValue);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_transient_attribute_should_be_assigned_correctly_when_using_setRawAttributes_and_not_be_casted()
    {
        /** @var EloquentTransientAttributes $this */
        $normal    = [
            'name' => 4,
        ];
        $transient = [
            EloquentTransientAttributes::TEST_TRANSIENT                  => 1,
            EloquentTransientAttributes::TEST_TRANSIENT_WITH_SET_MUTATOR => 2,
            EloquentTransientAttributes::TEST_TRANSIENT_WITH_GET_MUTATOR => 3,
        ];
        $data      = $normal + $transient;

        $this->setRawAttributes($data);

        /** @var Subject $result */
        $result = $this->{EloquentTransientAttributes::TEST_TRANSIENT};
        $result->shouldBe('1');

        /** @var Subject $result */
        $result = $this->getRawTransientAttribute(EloquentTransientAttributes::TEST_TRANSIENT);
        $result->shouldBe(1);

        /** @var Subject $result */
        $result = $this->getAttributes();
        $result->shouldIterateAs($normal);

        /** @var Subject $result */
        $result = $this->getTransientAttributes();
        $result->shouldIterateAs($transient);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_isTransientAttribute_should_work_as_expected()
    {
        /** @var EloquentTransientAttributes $this */
        $this->name                                          = 'name';
        $this->{EloquentTransientAttributes::TEST_TRANSIENT} = 'transient';

        /** @var Subject $result */
        $result = $this->isTransientAttribute(EloquentTransientAttributes::TEST_TRANSIENT);
        $result->shouldBe(true);

        /** @var Subject $result */
        $result = $this->isTransientAttribute('name');
        $result->shouldBe(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getRawTransientAttribute_should_work_as_expected()
    {
        /** @var EloquentTransientAttributes $this */
        $this->name                                          = 'name';
        $this->{EloquentTransientAttributes::TEST_TRANSIENT} = 'transient';

        /** @var Subject $result */
        $result = $this->getRawTransientAttribute('name');
        $result->shouldBeNull();

        /** @var Subject $result */
        $result = $this->getRawTransientAttribute(EloquentTransientAttributes::TEST_TRANSIENT);
        $result->shouldBe('transient');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_staticOldInstance_should_work_and_returned_model_not_dirty()
    {
        /** @var EloquentTransientAttributes $this */
        $model = $this::staticOldInstance(['test' => 'value']);

        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_toArray_should_return_all_transient_attributes_along_with_normal_ones()
    {
        /** @var EloquentTransientAttributes $this */
        $data = [
            EloquentTransientAttributes::TEST_TRANSIENT                  => 1,
            EloquentTransientAttributes::TEST_TRANSIENT_WITH_SET_MUTATOR => 2,
            EloquentTransientAttributes::TEST_TRANSIENT_WITH_GET_MUTATOR => 3,
            'name'                                                       => 4,
        ];
        $this->setRawAttributes($data);

        /** @var Subject $result */
        $result = $this->toArray();

        $result->shouldHaveKeyWithValue(EloquentTransientAttributes::TEST_TRANSIENT, '1');
        $result->shouldHaveKeyWithValue(EloquentTransientAttributes::TEST_TRANSIENT_WITH_SET_MUTATOR, '2');
        $result->shouldHaveKeyWithValue(EloquentTransientAttributes::TEST_TRANSIENT_WITH_GET_MUTATOR, 256);
        $result->shouldHaveKeyWithValue('name', '4');
        $result->shouldHaveKeyWithValue(EloquentTransientAttributes::TEST_TRANSIENT_WITH_OTHER_GET_MUTATOR, true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_resetAllAttributes_should_work()
    {
        /** @var EloquentTransientAttributes $this */
        $this->it_toArray_should_return_all_transient_attributes_along_with_normal_ones();

        $this->resetAllAttributes();

        /** @var Subject $result */
        $result = $this->toArray();
        $result->shouldHaveKeyWithValue(EloquentTransientAttributes::TEST_TRANSIENT_WITH_OTHER_GET_MUTATOR, true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_toArray_should_crash_that_getter_is_not_set_but_the_attribute_is_in_appends()
    {
        /** @var EloquentTransientAttributes $this */
        $this->setAppends([EloquentTransientAttributes::TEST_TRANSIENT_APPENDED_ATTRIBUTE]);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow(\BadMethodCallException::class)->duringToArray();
    }
}
