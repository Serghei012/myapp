<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository\Layers;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Traits\Repository\ModelCache\PhpStaticVariableModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Model;

class PhpStaticVariableModelCacheRepository extends TwoLevelCachingModelCacheRepository implements RepositoryInterface
{
    use PhpStaticVariableModelCacheRepositoryTrait;

    /**
     * ModelCacheRepository constructor.
     *
     * @param Model $model
     * @param TwoLevelCacheRepositoryInterface $twoLevelCacheRepository
     * @param PhpStaticVariableCacheRepositoryInterface $phpStaticVariableRepository
     * @internal param CacheRepository $cacheRepository
     */
    public function __construct(
        Model $model,
        TwoLevelCacheRepositoryInterface $twoLevelCacheRepository,
        PhpStaticVariableCacheRepositoryInterface $phpStaticVariableRepository
    ) {
        $this->initCacheRepositoryTrait($phpStaticVariableRepository, 'tests');

        parent::__construct($model, $twoLevelCacheRepository);
    }
}
