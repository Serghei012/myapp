<?php
namespace laravel\AdoreMe\Common\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface;
use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Common\Traits\Eloquent\Repository\EloquentPriorityHandlerRepositoryTrait;
use AdoreMe\Common\Traits\Eloquent\Repository\EloquentRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;

class EloquentPriorityHandlerRepository implements RepositoryInterface, PriorityHandlerRepositoryInterface
{
    use EloquentRepositoryTrait;
    use EloquentPriorityHandlerRepositoryTrait;

    /**
     * EloquentModelProviderRepository constructor.
     *
     * @param EloquentPriorityHandlerAttributes $model
     */
    public function __construct(EloquentPriorityHandlerAttributes $model)
    {
        $this->model = $model;

        $this->initEloquentPriorityHandlerRepositoryTrait(
            [
                'priority' => 'Priority "%s" already in use by id #%s.',
                'code'     => 'Code "%s" already in use by id #%s.',
            ],
            [
                'priority',
                'code',
            ]
        );
    }
}
