<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository\Layers;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Traits\Repository\ModelCache\ModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentRepository;
use laravel\AdoreMe\Common\Models\Eloquent\Model;

class TwoLevelCachingModelCacheRepository extends EloquentRepository implements RepositoryInterface
{
    use ModelCacheRepositoryTrait;

    /**
     * ModelCacheRepository constructor.
     *
     * @param Model $model
     * @param TwoLevelCacheRepositoryInterface $cacheRepository
     */
    public function __construct(Model $model, TwoLevelCacheRepositoryInterface $cacheRepository)
    {
        $this->initCacheRepositoryTrait($cacheRepository, 'tests');

        parent::__construct($model);
    }
}
