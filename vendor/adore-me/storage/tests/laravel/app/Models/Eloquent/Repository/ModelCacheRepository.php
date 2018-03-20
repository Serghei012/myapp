<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Traits\Repository\ModelCache\ModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Model;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentRepository;
use AdoreMe\Storage\Interfaces\Repository\Cache\CacheRepositoryInterface as CacheRepository;

class ModelCacheRepository extends EloquentRepository implements RepositoryInterface
{
    use ModelCacheRepositoryTrait;

    /**
     * ModelCacheRepository constructor.
     *
     * @param Model $model
     * @param CacheRepository $cacheRepository
     */
    public function __construct(Model $model, CacheRepository $cacheRepository)
    {
        $this->initCacheRepositoryTrait($cacheRepository, 'tests');

        parent::__construct($model);
    }
}
