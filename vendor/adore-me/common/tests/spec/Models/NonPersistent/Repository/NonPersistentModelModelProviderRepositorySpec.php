<?php
namespace spec\AdoreMe\Common\Models\NonPersistent\Repository;

use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use laravel\AdoreMe\Common\Models\NonPersistent\Repository\NonPersistentModelModelProviderRepository;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use laravel\AdoreMe\Common\Models\NonPersistent\Model;

/** @var NonPersistentModelModelProviderRepository $this */
class NonPersistentModelModelProviderRepositorySpec extends ObjectBehavior
{
    function let()
    {
        /** @var NonPersistentModelModelProviderRepository $this */
        $this->beAnInstanceOf(NonPersistentModelModelProviderRepository::class);
        $this->beConstructedWith(new Model());
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var NonPersistentModelModelProviderRepository $this */
        $this->shouldHaveType(ModelProviderRepositoryInterface::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_createNewModel_should_work_and_return_model_dirty()
    {
        /** @var NonPersistentModelModelProviderRepository $this */
        $model = $this->createNewModel(['test' => 'value']);

        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(true);

        $model->syncOriginal();
        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_createOldModel_should_work_and_return_model_not_dirty()
    {
        /** @var NonPersistentModelModelProviderRepository $this */
        $model = $this->createOldModel(['test' => 'value']);

        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(false);

        $model->new_attribute = 'test';
        /** @var Subject $result */
        $result = $model->isDirty();
        $result->shouldBe(true);
    }
}
