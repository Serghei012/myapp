<?php
namespace specIntrusive\AdoreMe\Common\Models\Eloquent\Repository;

use AdoreMe\Common\Exceptions\ResourceConflictException;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes as Model;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentPriorityHandlerRepository;
use PhpSpec\Laravel\LaravelObjectBehavior;
use PhpSpec\Wrapper\Subject;
use stubs\AdoreMe\Common\Interfaces\FixturesInterface;
use stubs\AdoreMe\Common\Traits\PhpSpecFixturesTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;
use fixtures\AdoreMe\Common\EloquentPriorityHandlerAttributes as FixturesModel;

/** @var EloquentPriorityHandlerRepository $this */
class EloquentPriorityHandlerRepositorySpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;
    use PhpSpecFixturesTrait;

    /** @var FixturesInterface */
    protected $fixturesModel;

    /**
     * Get the fixtures model.
     *
     * @return FixturesInterface
     */
    protected function getFixturesModel(): FixturesInterface
    {
        if (is_null($this->fixturesModel)) {
            $this->fixturesModel = new FixturesModel();
        }

        return $this->fixturesModel;
    }

    function let()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->beAnInstanceOf(EloquentPriorityHandlerRepository::class);
        $this->beConstructedWith(new Model());
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_switchPriorityForOne_with_negative_priority_or_0_should_not_work()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->appUp();
        $this->fixturesUp();

        /** @var Model|Subject $model */
        $model = $this->findOneById(1);
        $model = $model->getWrappedObject();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow(ResourceConflictException::class)->duringSwitchPriority($model, -5);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow(ResourceConflictException::class)->duringSwitchPriority($model, 0);

        $this->fixturesDown();
        $this->appDown();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_switchPriorityForOne_5_to_priority_7_should_return_id_5_priority_7__id_6_priority_5__id_7_priority_6()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->appUp();
        $this->fixturesUp();

        /** @var Model|Subject $model */
        $model = $this->findOneById(5);
        $model = $model->getWrappedObject();

        /** @var Subject $result */
        $result = $this->switchPriority($model, 7);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnTheIdsWithPriorities(
            [
                6 => 5,
                7 => 6,
                5 => 7,
            ]
        );

        $this->fixturesDown();
        $this->appDown();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_switchPriorityForOne_5_to_priority_3_should_return_id_5_priority_3__id_3_priority_4__id_4_priority_5()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->appUp();
        $this->fixturesUp();

        /** @var Model|Subject $model */
        $model = $this->findOneById(5);
        $model = $model->getWrappedObject();

        /** @var Subject $result */
        $result = $this->switchPriority($model, 3);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnTheIdsWithPriorities(
            [
                5 => 3,
                3 => 4,
                4 => 5,
            ]
        );

        $this->fixturesDown();
        $this->appDown();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * ID-PRIORITY
     * Initial                   : 1-1 ; 2-2 ; 3-3 ; 4-4 ; 5-5 ; 6-6 ; 7-7 ; 8-8
     * After first switch,  5->7 : 1-1 ; 2-2 ; 3-3 ; 4-4 ; 6-5 ; 7-6 ; 5-7 ; 8-8
     * After second switch, 7->3 : 1-1 ; 2-2 ; 7-3 ; 3-4 ; 4-5 ; 6-6 ; 5-7 ; 8-8
     */
    function it_switchPriorityForOne_5_to_priority_7_and_for_id_7_to_priority_3_should_return_correct_data()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->appUp();
        $this->fixturesUp();

        /** @var Model|Subject $model */
        $model = $this->findOneById(5);
        $model = $model->getWrappedObject();

        /** @var Subject $result */
        $result = $this->switchPriority($model, 7);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnTheIdsWithPriorities(
            [
                6 => 5,
                7 => 6,
                5 => 7,
            ]
        );

        /** @var Model|Subject $model */
        $model = $this->findOneById(7);
        $model = $model->getWrappedObject();

        /** @var Subject $result */
        $result = $this->switchPriority($model, 3);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnTheIdsWithPriorities(
            [
                7 => 3,
                3 => 4,
                4 => 5,
                6 => 6,
            ]
        );

        $this->fixturesDown();
        $this->appDown();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_switchPriorityForOne_8_to_priority_100_should_do_nothing_because_is_already_the_maximum_priority()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->appUp();
        $this->fixturesUp();

        /** @var Model|Subject $model */
        $model = $this->findOneById(8);
        $model = $model->getWrappedObject();

        /** @var Subject $result */
        $result = $this->switchPriority($model, 100);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnTheIdsWithPriorities([]);

        $this->fixturesDown();
        $this->appDown();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_switchPriorityForOne_7_to_priority_100_should_change_to_9_because_is_the_next_available_priority()
    {
        /** @var EloquentPriorityHandlerRepository $this */
        $this->appUp();
        $this->fixturesUp();

        /** @var Model|Subject $model */
        $model = $this->findOneById(7);
        $model = $model->getWrappedObject();

        /** @var Subject $result */
        $result = $this->switchPriority($model, 100);
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnTheIdsWithPriorities(
            [
                7 => 9,
            ]
        );

        $this->fixturesDown();
        $this->appDown();
    }
}
