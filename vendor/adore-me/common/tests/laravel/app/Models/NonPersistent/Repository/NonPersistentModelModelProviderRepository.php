<?php
namespace laravel\AdoreMe\Common\Models\NonPersistent\Repository;

use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use AdoreMe\Common\Traits\NonPersistentModel\Repository\NonPersistentModelModelProviderRepositoryTrait;
use laravel\AdoreMe\Common\Models\NonPersistent\Model;

class NonPersistentModelModelProviderRepository implements ModelProviderRepositoryInterface
{
    use NonPersistentModelModelProviderRepositoryTrait;

    /**
     * NonPersistentModelModelProviderRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
