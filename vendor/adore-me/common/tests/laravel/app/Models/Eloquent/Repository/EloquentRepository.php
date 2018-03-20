<?php
namespace laravel\AdoreMe\Common\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Common\Traits\Eloquent\Repository\EloquentRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Model;

class EloquentRepository implements RepositoryInterface
{
    use EloquentRepositoryTrait;

    /**
     * EloquentModelProviderRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
