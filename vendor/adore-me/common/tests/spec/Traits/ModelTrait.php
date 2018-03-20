<?php
namespace spec\AdoreMe\Common\Traits;

use AdoreMe\Common\Interfaces\ModelInterface;
use Illuminate\Support\Collection;
use laravel\AdoreMe\Common\Models\NonPersistent\Model;
use PhpSpec\Wrapper\Subject;

trait ModelTrait
{
    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_stub_is_initializable();

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__implements_model_interface()
    {
        $this->shouldImplement(ModelInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__isDirty_should_work_when_has_a_collection_with_multi_dimensional_arrays()
    {
        /** @var ModelInterface $this */
        $collection = Collection::make([
            'id' => 123123,
            'name' => 'name',
            'extra' => [
                'extra_1' => 'extra something',
                'name' => 'something',
                'options' => [
                    'more' => true
                ]
            ]
        ]);
        $this->setAttribute('test', $collection);

        /** @var Subject $result */
        $result = $this->isDirty();
        $result->shouldReturn(true);

        $this->syncOriginal();

        /** @var Subject $result */
        $result = $this->isDirty();
        $result->shouldReturn(false);

        $collection = Collection::make([
            'id' => 999,
            'name' => 'name',
            'extra' => [
                'extra_1' => 'extra something',
                'name' => 'something',
                'options' => [
                    'more' => true
                ]
            ],
            'something else' => 'asd'
        ]);
        $this->setAttribute('test', $collection);

        /** @var Subject $result */
        $result = $this->isDirty();
        $result->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__isDirty_should_work_when_has_a_collection_of_non_persistent_models()
    {
        /** @var ModelInterface $this */
        $model = new Model(
            [
                'id' => 123123,
                'name' => 'name',
                'extra' => [
                    'extra_1' => 'extra something',
                    'name' => 'something',
                    'options' => [
                        'more' => true
                    ]
                ]
            ]
        );
        $model2 = new Model(
            [
                'id' => 34,
                'name' => 'name',
                'extra' => [
                    'extra_1' => 'extra something',
                    'name' => 'something',
                    'options' => [
                        'more' => true
                    ]
                ]
            ]
        );
        $this->setAttribute('test', Collection::make([$model, $model2]));

        /** @var Subject $result */
        $result = $this->isDirty();
        $result->shouldReturn(true);

        $this->syncOriginal();

        /** @var Subject $result */
        $result = $this->isDirty();
        $result->shouldReturn(false);

        $this->setAttribute('test', Collection::make([$model]));

        /** @var Subject $result */
        $result = $this->isDirty();
        $result->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__isDirty_should_work_when_given_as_parameter_an_attribute_as_string()
    {
        /** @var ModelInterface $this */
        $this->setAttribute('test', 'value');

        /** @var Subject $result */
        $result = $this->isDirty('test');
        $result->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__isDirty_should_work_when_given_as_parameter_an_attribute_as_string_after_syncing_original()
    {
        /** @var ModelInterface $this */
        $this->setAttribute('test', 'value');
        $this->setAttribute('test1', 'value1');

        /** @var Subject $result */
        $result = $this->isDirty('test');
        $result->shouldReturn(true);
        /** @var Subject $result */
        $result = $this->isDirty('test1');
        $result->shouldReturn(true);

        $this->syncOriginal();
        $this->setAttribute('test', 'new value');

        /** @var Subject $result */
        $result = $this->isDirty('test');
        $result->shouldReturn(true);
        $result = $this->isDirty('test1');
        $result->shouldReturn(false);

        /** @var Subject $result */
        $result = $this->isDirty(['test', 'test1']);
        $result->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__newInstance_can_accept_array_as_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringNewInstance([]);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__getAttribute_can_accept_string_as_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringGetAttribute('test');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__getAttribute_cannot_accept_array_as_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringGetAttribute(['test']);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__getAttribute_can_accept_integer_as_parameter_and_is_casted_as_integer()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringGetAttribute(123);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__getAttribute_cannot_accept_object_as_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringGetAttribute(new class() {});
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__setAttribute_can_accept_string_as_first_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringSetAttribute('test', 'value');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__setAttribute_cannot_accept_array_as_first_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringSetAttribute([], 'value');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__setAttribute_cannot_accept_integer_as_first_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringSetAttribute([], 'value');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ModelTrait__setAttribute_cannot_accept_object_as_first_parameter()
    {
        /** @var ModelInterface $this */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringSetAttribute(new class() {}, 'value');
    }
}
