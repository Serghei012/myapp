<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository\Layers;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Traits\Repository\ModelCache\PhpStaticVariablePriorityHandlerModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\EloquentPriorityHandlerAttributes;

class PhpStaticVariableModelCachePriorityHandlerRepository extends TwoLevelCachingModelCachePriorityHandlerRepository
    implements RepositoryInterface
{
    use PhpStaticVariablePriorityHandlerModelCacheRepositoryTrait;

    /**
     * ModelCachePriorityHandlerRepository constructor.
     *
     * @param EloquentPriorityHandlerAttributes $model
     * @param TwoLevelCacheRepositoryInterface $twoLevelCacheRepository
     * @param PhpStaticVariableCacheRepositoryInterface $phpStaticVariableRepository
     */
    public function __construct(
        EloquentPriorityHandlerAttributes $model,
        TwoLevelCacheRepositoryInterface $twoLevelCacheRepository,
        PhpStaticVariableCacheRepositoryInterface $phpStaticVariableRepository
    ) {
        $this->initCacheRepositoryTrait($phpStaticVariableRepository, 'tests');

        parent::__construct($model, $twoLevelCacheRepository);
    }
}
