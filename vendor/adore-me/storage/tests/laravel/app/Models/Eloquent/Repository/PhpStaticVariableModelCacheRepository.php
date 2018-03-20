<?php
namespace laravel\AdoreMe\Storage\Models\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use laravel\AdoreMe\Common\Models\Eloquent\Model;
use AdoreMe\Storage\Traits\Repository\ModelCache\PhpStaticVariableModelCacheRepositoryTrait;
use laravel\AdoreMe\Common\Models\Eloquent\Repository\EloquentRepository;

class PhpStaticVariableModelCacheRepository extends EloquentRepository implements RepositoryInterface
{
    use PhpStaticVariableModelCacheRepositoryTrait;

    /**
     * PhpStaticVariableModelCacheRepository constructor.
     *
     * @param Model $model
     * @param PhpStaticVariableCacheRepositoryInterface $cacheRepository
     */
    public function __construct(Model $model, PhpStaticVariableCacheRepositoryInterface $cacheRepository)
    {
        $this->initCacheRepositoryTrait($cacheRepository, 'tests');

        parent::__construct($model);
    }
}
