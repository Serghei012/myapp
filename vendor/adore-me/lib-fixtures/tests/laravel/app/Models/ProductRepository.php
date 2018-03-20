<?php
namespace laravel\AdoreMe\Library\Fixtures\Models;

use AdoreMe\Common\Traits\Eloquent\Repository\EloquentRepositoryTrait;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductInterface;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductRepositoryInterface;

/**
 * @property ProductInterface model
 */
class ProductRepository implements ProductRepositoryInterface
{
    use EloquentRepositoryTrait;

    /**
     * ProductRepository constructor.
     *
     * @param ProductInterface $model
     */
    public function __construct(ProductInterface $model)
    {
        $this->model = $model;
    }
}
