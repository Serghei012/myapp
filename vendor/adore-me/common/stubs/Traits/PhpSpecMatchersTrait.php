<?php
namespace stubs\AdoreMe\Common\Traits;

use PhpSpec\Console\Assembler\PresenterAssembler;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Matcher\Iterate\IterablesMatcher;
use PhpSpec\ServiceContainer\IndexedServiceContainer;

trait PhpSpecMatchersTrait
{
    protected $phpSpecMatchersTraitPresenter;

    /**
     * Build, or use the existing presenter.
     *
     * @return Presenter
     */
    function getPhpSpecMatchersTraitBuildPresenter(): Presenter
    {
        if (is_null($this->phpSpecMatchersTraitPresenter)) {
            if (property_exists($this, 'presenter') && $this->presenter instanceof Presenter) {
                $this->phpSpecMatchersTraitPresenter = $this->presenter;
            } else {
                $service = new IndexedServiceContainer();
                (new PresenterAssembler())->assemble($service);
                $this->phpSpecMatchersTraitPresenter = $service->get('formatter.presenter');
            }
        }

        return $this->phpSpecMatchersTraitPresenter;
    }

    /**
     * @return array
     */
    function getMatchers()
    {
        return [
            'containArrayValues'            => function (array $subject, array $expectedArray) {
                foreach ($expectedArray as $item) {
                    if (! in_array($item, $subject)) {
                        return false;
                    }
                }

                return true;
            },
            'containArrayKeys'              => function (array $subject, array $expectedArray) {
                $keys = array_keys($subject);
                foreach ($expectedArray as $item) {
                    if (! in_array($item, $keys)) {
                        return false;
                    }
                }

                return true;
            },
            'returnTheFollowingChildrenIds' => function ($subject, array $expectedIds) {
                $ids = [];
                foreach ($subject as $item) {
                    $ids[] = $item->child_id;
                }

                $iterableModel = new IterablesMatcher($this->getPhpSpecMatchersTraitBuildPresenter());
                $iterableModel->match($ids, $expectedIds);

                return true;
            },
            'returnTheFollowingParentIds'   => function ($subject, array $expectedIds) {
                $ids = [];
                foreach ($subject as $item) {
                    $ids[] = $item->parent_id;
                }

                $iterableModel = new IterablesMatcher($this->getPhpSpecMatchersTraitBuildPresenter());
                $iterableModel->match($ids, $expectedIds);

                return true;
            },
            'returnTheFollowingModelIds'    => function ($subject, array $expectedIds) {
                $ids = [];
                foreach ($subject as $item) {
                    $ids[] = $item->id;
                }

                $iterableModel = new IterablesMatcher($this->getPhpSpecMatchersTraitBuildPresenter());
                $iterableModel->match($ids, $expectedIds);

                return true;
            },
            'returnTheIdsWithPriorities'    => function ($subject, array $expectedIdsPriorityCollection) {
                $idsPriorityCollection = [];
                foreach ($subject as $item) {
                    $idsPriorityCollection[$item->id] = $item->priority;
                }

                $iterableModel = new IterablesMatcher($this->getPhpSpecMatchersTraitBuildPresenter());
                $iterableModel->match($idsPriorityCollection, $expectedIdsPriorityCollection);

                return true;
            },
        ];
    }
}
