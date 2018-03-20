<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use AdoreMe\Storage\Traits\Repository\ModelCache\PhpStaticVariablePriorityHandlerModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentPriorityHandlerRepository;

class PhpStaticVariablePriorityHandlerModelCacheRepository extends EloquentPriorityHandlerRepository implements RepositoryInterface
{
    use PhpStaticVariablePriorityHandlerModelCacheRepositoryTrait;

    /**
     * ModelCachePriorityHandlerRepository constructor.
     *
     * @param EloquentPriorityHandlerAttributes $model
     * @param PhpStaticVariableCacheRepositoryInterface $cacheRepository
     */
    public function __construct(EloquentPriorityHandlerAttributes $model, PhpStaticVariableCacheRepositoryInterface $cacheRepository)
    {
        $this->initCacheRepositoryTrait($cacheRepository, 'tests');

        parent::__construct($model);
    }
}
