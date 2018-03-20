<?php
namespace specIntrusive\AdoreMe\Common\Models\Eloquent\Repository;

use Illuminate\Support\Collection;
use laravel\AdoreMe\Common\Models\Eloquent\Model;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentRepository;
use PhpSpec\Laravel\LaravelObjectBehavior;
use PhpSpec\Wrapper\Subject;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var EloquentRepository $this */
class EloquentRepositorySpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;

    function let()
    {
        /** @var EloquentRepository $this */
        $this->beAnInstanceOf(EloquentRepository::class);
        $this->beConstructedWith(new Model());
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_createModel_should_work()
    {
        /** @var EloquentRepository $this */
        $this->createModel(
            [
                'name' => 'create',
            ]
        );

        /** @var Subject|Model $result */
        $result = $this->findOneById(1);
        $result->name->shouldBe('create');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_saveModel_should_work()
    {
        /** @var EloquentRepository $this */
        $model       = $this->findOneById(1);
        $model->name = 'save';

        $this->saveModel($model);

        /** @var Subject|Model $result */
        $result = $this->findOneById(1);
        $result->name->shouldBe('save');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_updateModel_should_work()
    {
        /** @var EloquentRepository $this */
        $this->updateModel(
            $this->findOneById(1),
            [
                'name' => 'update',
            ]
        );

        /** @var Subject|Model $result */
        $result = $this->findOneById(1);
        $result->name->shouldBe('update');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_replaceModel_should_work()
    {
        /** @var EloquentRepository $this */
        $this->replaceModel(
            $this->findOneById(1),
            [
                'name' => 'replace',
            ]
        );

        /** @var Subject|Model $result */
        $result = $this->findOneById(1);
        $result->name->shouldBe('replace');
        $result->id->shouldBe(1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_deleteModel_should_work()
    {
        /** @var EloquentRepository $this */
        $this->deleteModel($this->findOneById(1));

        /** @var Subject $result */
        $result = $this->findOneById(1);
        $result->shouldBeNull();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_find_should_work()
    {
        /** @var EloquentRepository $this */
        for ($i = 1; $i <= 5; $i++) {
            $attributes = [
                'name'  => 'name_' . $i,
            ];
            $this->createModel($attributes);
        }

        /** @var Subject $result */
        $result = $this->find();
        $result->shouldHaveCount(5);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_findBy_should_work()
    {
        /** @var EloquentRepository $this */
        /** @var Subject $result */
        $result = $this->findBy('name', 'name_1');
        $result->shouldHaveCount(1);
        $result->shouldBeAnInstanceOf(Collection::class);

        $result = $this->findBy('name', ['name_1', 'name_2']);
        $result->shouldHaveCount(2);
        $result->shouldBeAnInstanceOf(Collection::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_findOneBy_should_work()
    {
        /** @var EloquentRepository $this */
        /** @var Subject $result */
        $result = $this->findOneBy('name', 'name_1');
        $result->shouldBeAnInstanceOf(Model::class);

        $result = $this->findOneBy('name', ['name_15']);
        $result->shouldBeNull();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_findByIds_should_work()
    {
        /** @var EloquentRepository $this */
        /** @var Subject $result */
        $result = $this->findByIds([2, 3, 4, 999999]);
        $result->shouldHaveCount(3);
        $result->shouldBeAnInstanceOf(Collection::class);
    }
}
