<?php
namespace spec\AdoreMe\Common\Helpers;

use AdoreMe\Common\Exceptions\UnexpectedItemObjectTypeInCollectionException;
use AdoreMe\Common\Exceptions\UnexpectedObjectInstanceException;
use AdoreMe\Common\Helpers\ObjectHelper;
use Illuminate\Support\Collection;
use PhpSpec\ObjectBehavior;
use laravel\AdoreMe\Common\Models\NonPersistent\Model as ModelNonPersistentModel;
use laravel\AdoreMe\Common\Models\NonPersistent\ModelExtendingModel as ModelExtendingModelNonPersistentModel;
use laravel\AdoreMe\Common\Models\Eloquent\Model as ModelEloquent;
use laravel\AdoreMe\Common\Models\Eloquent\ModelExtendingModel as ModelExtendingModelEloquent;
use laravel\AdoreMe\Common\Models\Model as SimpleModel;
use PhpSpec\Wrapper\Subject;

/** @var ObjectHelper $this */
class ObjectHelperSpec extends ObjectBehavior
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_works_as_intended_when_value_is_already_expected_class()
    {
        /** @var ObjectHelper $this */
        $value = new ModelNonPersistentModel(['test' => 'value']);
        /** @var Subject $result */
        $result = $this->castIntoObjectClass($value, ModelNonPersistentModel::class);
        $result->shouldBe($value);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_works_as_intended_when_value_is_on_object_that_extends_expected_class()
    {
        /** @var ObjectHelper $this */
        $value = new ModelExtendingModelNonPersistentModel(['test' => 'value']);
        /** @var Subject $result */
        $result = $this->castIntoObjectClass($value, ModelNonPersistentModel::class);
        $result->shouldBe($value);

        $value = new ModelExtendingModelEloquent(['test' => 'value']);
        /** @var Subject $result */
        $result = $this->castIntoObjectClass($value, ModelEloquent::class);
        $result->shouldBe($value);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_throw_exception_when_value_is_object_other_than_class_or_child_of_class()
    {
        /** @var ObjectHelper $this */
        $value = new ModelEloquent(['test' => 'value']);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow(UnexpectedObjectInstanceException::class)->duringCastIntoObjectClass(
            $value,
            ModelNonPersistentModel::class
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_works_as_intended_when_we_want_a_model_extending_non_persistent_model()
    {
        /** @var ObjectHelper $this */
        $value = ['test' => 'value'];
        /** @var Subject $result */
        $result = $this->castIntoObjectClass($value, ModelNonPersistentModel::class);
        $result->shouldBeAnInstanceOf(ModelNonPersistentModel::class);
        $result->shouldHaveKeyWithValue('test', 'value');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_works_as_intended_when_we_want_a_model_extending_eloquent()
    {
        /** @var ObjectHelper $this */
        $value = ['test' => 'value'];
        /** @var Subject $result */
        $result = $this->castIntoObjectClass($value, ModelEloquent::class);
        $result->shouldBeAnInstanceOf(ModelEloquent::class);
        $result->shouldHaveKeyWithValue('test', 'value');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_works_as_intended_when_we_want_other_model_than_eloquent_or_non_persistent_model()
    {
        /** @var ObjectHelper $this */
        $value = ['test' => 'value'];
        /** @var Subject $result */
        $result = $this->castIntoObjectClass($value, SimpleModel::class);
        $result->shouldBeAnInstanceOf(SimpleModel::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoObjectClass_works_as_intended_when_value_is_null()
    {
        /** @var ObjectHelper $this */
        /** @var Subject $result */
        $result = $this->castIntoObjectClass(null, ModelNonPersistentModel::class);
        $result->shouldBeAnInstanceOf(ModelNonPersistentModel::class);

        /** @var Subject $result */
        $result = $this->castIntoObjectClass(null, ModelEloquent::class);
        $result->shouldBeAnInstanceOf(ModelEloquent::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollection_will_return_an_empty_collection_when_value_is_null()
    {
        /** @var ObjectHelper $this */
        /** @var Subject|Collection $result */
        $result = $this->castIntoCollection(null);
        $result->shouldBeAnInstanceOf(Collection::class);

        /** @var Subject|Collection $result */
        $result = $result->isEmpty();
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollection_will_return_the_same_collection_when_value_is_already_an_collection()
    {
        /** @var ObjectHelper $this */
        $collection = Collection::make(['test' => 'value']);
        /** @var Subject|Collection $result */
        $result = $this->castIntoCollection($collection);
        $result->shouldBeAnInstanceOf(Collection::class);
        $result->shouldBe($collection);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollection_will_return_an_collection_when_value_is_array()
    {
        /** @var ObjectHelper $this */
        $collection = ['test' => 'value'];
        /** @var Subject|Collection $result */
        $result = $this->castIntoCollection($collection);
        $result->shouldBeAnInstanceOf(Collection::class);
        $result->shouldIterateAs($collection);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollectionOf_will_return_an_empty_collection_when_value_is_null()
    {
        /** @var ObjectHelper $this */
        /** @var Subject|Collection $result */
        $result = $this->castIntoCollectionOf(null, ModelNonPersistentModel::class);
        $result->shouldBeAnInstanceOf(Collection::class);

        /** @var Subject|Collection $result */
        $result = $result->isEmpty();
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollectionOf_will_return_an_collection_with_items_having_required_model()
    {
        /** @var ObjectHelper $this */
        /** @var Subject|Collection $result */
        $result = $this->castIntoCollectionOf(
            [
                [
                    'value 1',
                    'value 2',
                ],
                [
                    'test 3' => 'value 3',
                ],
                [
                    'test 4' => [
                        'value 4',
                    ],
                ],
            ],
            ModelNonPersistentModel::class
        );
        $result->shouldBeAnInstanceOf(Collection::class);

        foreach ($result as $item) {
            $item->shouldBeAnInstanceOf(ModelNonPersistentModel::class);
        }
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollectionOf_will_return_same_collection_when_all_items_are_required_model()
    {
        /** @var ObjectHelper $this */
        $value = Collection::make(
            [
                new ModelNonPersistentModel(['test 1' => 'value 1']),
                new ModelNonPersistentModel(['test 2' => 'value 2']),
            ]
        );
        /** @var Subject $result */
        $result = $this->castIntoCollectionOf($value, ModelNonPersistentModel::class);
        $result->shouldBe($value);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_castIntoCollectionOf_will_throw_exception_when_value_is_collection_of_items_not_having_required_model()
    {
        /** @var ObjectHelper $this */
        $collection = Collection::make(
            [
                new ModelNonPersistentModel(['test 1' => 'value 1']),
                [
                    'value 1',
                    'value 2',
                ],
                [
                    'test 3' => 'value 3',
                ],
                [
                    'test 4' => [
                        'value 4',
                    ],
                ],
            ]
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow(UnexpectedItemObjectTypeInCollectionException::class)->duringcastIntoCollectionOf(
            $collection,
            ModelNonPersistentModel::class
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_throwExceptionIfNotCollectionOf_throw_exception_when_collection_does_not_have_all_items_of_expected_model(
    )
    {
        /** @var ObjectHelper $this */
        $collection = Collection::make(
            [
                new ModelNonPersistentModel(['test 1' => 'value 1']),
                [
                    'value 1',
                    'value 2',
                ],
                [
                    'test 3' => 'value 3',
                ],
                [
                    'test 4' => [
                        'value 4',
                    ],
                ],
            ]
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow(UnexpectedItemObjectTypeInCollectionException::class)
            ->duringthrowExceptionIfNotCollectionOf(
                $collection,
                ModelNonPersistentModel::class
            );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_throwExceptionIfNotCollectionOf_return_true_when_collection_have_all_items_of_expected_model()
    {
        /** @var ObjectHelper $this */
        $collection = Collection::make(
            [
                new ModelNonPersistentModel(['test 1' => 'value 1']),
                new ModelNonPersistentModel(['test 2' => 'value 2']),
            ]
        );

        /** @var Subject $result */
        $result = $this->throwExceptionIfNotCollectionOf($collection, ModelNonPersistentModel::class);
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_constructIdentifierForMethodAndArguments()
    {
        /** @var ObjectHelper $this */
        /** @var Subject $result */
        $result = $this->constructIdentifierForMethodAndArguments(
            'method_name',
            $arguments = ['test', '2', '3', 'test_4' => '4'],
            $prefix = '',
            $glue = ':',
            $queryGlue = '?',
            $argumentsGlue = '&',
            $keyValueSeparator = '='
        );

        $result->shouldBe('method_name?0=test&1=2&2=3&test_4=4');
    }
}
