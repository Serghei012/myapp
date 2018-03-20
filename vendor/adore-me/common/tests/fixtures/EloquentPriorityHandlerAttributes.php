<?php
namespace fixtures\AdoreMe\Common;

use AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface;
use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentPriorityHandlerRepository;
use stubs\AdoreMe\Common\Interfaces\FixturesInterface;
use stubs\AdoreMe\Common\Models\FixturesAbstract;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes as Model;

class EloquentPriorityHandlerAttributes extends FixturesAbstract implements FixturesInterface
{
    /** @var RepositoryInterface */
    protected $repository;

    public $items = [
        [
            Model::NAME                                  => 'name 1',
            Model::CODE                                  => 'code 1',
            Model::TITLE                                 => 'title 1',
            PriorityHandlerRepositoryInterface::PRIORITY => 1,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 2',
            Model::CODE                                  => 'code 2',
            Model::TITLE                                 => 'title 2',
            PriorityHandlerRepositoryInterface::PRIORITY => 2,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 3',
            Model::CODE                                  => 'code 3',
            Model::TITLE                                 => 'title 3',
            PriorityHandlerRepositoryInterface::PRIORITY => 3,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 4',
            Model::CODE                                  => 'code 4',
            Model::TITLE                                 => 'title 4',
            PriorityHandlerRepositoryInterface::PRIORITY => 4,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 5',
            Model::CODE                                  => 'code 5',
            Model::TITLE                                 => 'title 5',
            PriorityHandlerRepositoryInterface::PRIORITY => 5,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 6',
            Model::CODE                                  => 'code 6',
            Model::TITLE                                 => 'title 6',
            PriorityHandlerRepositoryInterface::PRIORITY => 6,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 7',
            Model::CODE                                  => 'code 7',
            Model::TITLE                                 => 'title 7',
            PriorityHandlerRepositoryInterface::PRIORITY => 7,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
        [
            Model::NAME                                  => 'name 8',
            Model::CODE                                  => 'code 8',
            Model::TITLE                                 => 'title 8',
            PriorityHandlerRepositoryInterface::PRIORITY => 8,
            PriorityHandlerRepositoryInterface::ENABLED  => true,
        ],
    ];

    /**
     * Get repository used.
     *
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        if (is_null($this->repository)) {
            $this->repository = new EloquentPriorityHandlerRepository(new Model());
        }

        return $this->repository;
    }
}
