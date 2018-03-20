<?php
namespace AdoreMe\Common\Traits\Eloquent\Repository;

use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use AdoreMe\Common\Interfaces\ModelInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @see ModelProviderRepositoryInterface
 * @since 2.0.0
 */
trait EloquentModelProviderRepositoryTrait
{
    /** @var ModelInterface|EloquentModel */
    protected $model;

    /**
     * EloquentModelProviderRepositoryTrait constructor.
     * Please overwrite and type hint appropriately the __construct function.
     *
     * @param ModelInterface|EloquentModel $model
     * @throws \Exception
     */
    public function __construct(EloquentModel $model)
    {
        $this->model = $model;

        throw new \Exception('Please overwrite and type hint appropriately the __construct function.');
    }

    /**
     * Create a new instance of model, and returns it.
     * The model should be "dirty", as in attributes are not sync with original.
     *
     * @param array $attributes
     * @return ModelInterface|EloquentModel
     */
    public function createNewModel(array $attributes = []): ModelInterface
    {
        $model = $this->model->newInstance([], false);
        $model->fill($attributes);

        return $model;
    }

    /**
     * Create a new instance of model, populated with given attributes, that should emulate an model that existed before
     * The model should not be "dirty", as in attributes are in sync with original.
     *
     * @param array $attributes
     * @return ModelInterface|EloquentModel
     */
    public function createOldModel(array $attributes = []): ModelInterface
    {
        $model = $this->model->newInstance([], true);
        $model->forceFill($attributes);
        $model->syncOriginal();

        return $model;
    }
}
