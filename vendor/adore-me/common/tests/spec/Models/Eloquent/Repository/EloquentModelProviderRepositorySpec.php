<?php
namespace spec\AdoreMe\Common\Models\Eloquent\Repository;

use laravel\AdoreMe\Common\Models\Eloquent\Model;
use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentModelProviderRepository;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;

/** @var EloquentModelProviderRepository $this */
class EloquentModelProviderRepositorySpec extends ObjectBehavior
{
    function let()
    {
        /** @var EloquentModelProviderRepository $this */
        $this->beAnInstanceOf(EloquentModelProviderRepository::class);
        $this->beConstructedWith(new Model());
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var EloquentModelProviderRepository $this */
        $this->shouldImplement(ModelProviderRepositoryInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_createNewModel_should_work_and_return_model_dirty()
    {
        /** @var EloquentModelProviderRepository $this */
        $model = $this->createNewModel(['name' => 'value']);

        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(true);

        $model->syncOriginal();
        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(false);
        $model->getAttribute('name')->shouldBe('value');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_createOldModel_should_work_and_return_model_not_dirty()
    {
        /** @var EloquentModelProviderRepository $this */
        $model = $this->createOldModel(['name' => 'value']);

        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(false);

        $model->new_attribute = 'test';
        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(true);
        $model->getAttribute('name')->shouldBe('value');
    }
}
