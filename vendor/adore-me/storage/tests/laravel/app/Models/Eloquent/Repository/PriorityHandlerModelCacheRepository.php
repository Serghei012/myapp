<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\CacheRepositoryInterface as CacheRepository;
use AdoreMe\Storage\Traits\Repository\ModelCache\PriorityHandlerModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentPriorityHandlerRepository;

class PriorityHandlerModelCacheRepository extends EloquentPriorityHandlerRepository implements RepositoryInterface
{
    use PriorityHandlerModelCacheRepositoryTrait;

    /**
     * ModelCachePriorityHandlerRepository constructor.
     *
     * @param EloquentPriorityHandlerAttributes $model
     * @param CacheRepository $cacheRepository
     */
    public function __construct(EloquentPriorityHandlerAttributes $model, CacheRepository $cacheRepository)
    {
        $this->initCacheRepositoryTrait($cacheRepository, 'tests');

        parent::__construct($model);
    }
}
