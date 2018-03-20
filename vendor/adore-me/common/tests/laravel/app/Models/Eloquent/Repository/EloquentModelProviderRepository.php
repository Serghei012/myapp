<?php
namespace laravel\AdoreMe\Common\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use AdoreMe\Common\Traits\Eloquent\Repository\EloquentModelProviderRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Model;

class EloquentModelProviderRepository implements ModelProviderRepositoryInterface
{
    use EloquentModelProviderRepositoryTrait;

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
