<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository\Layers;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Traits\Repository\ModelCache\PriorityHandlerModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentPriorityHandlerRepository;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;

class TwoLevelCachingModelCachePriorityHandlerRepository extends EloquentPriorityHandlerRepository
    implements RepositoryInterface
{
    use PriorityHandlerModelCacheRepositoryTrait;

    /**
     * ModelCachePriorityHandlerRepository constructor.
     *
     * @param EloquentPriorityHandlerAttributes $model
     * @param TwoLevelCacheRepositoryInterface $cacheRepository
     */
    public function __construct(
        EloquentPriorityHandlerAttributes $model,
        TwoLevelCacheRepositoryInterface $cacheRepository
    ) {
        $this->initCacheRepositoryTrait($cacheRepository, 'tests');

        parent::__construct($model);
    }
}
