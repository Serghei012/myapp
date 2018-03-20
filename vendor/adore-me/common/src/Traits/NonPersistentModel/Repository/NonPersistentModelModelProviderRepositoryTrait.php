<?php
namespace AdoreMe\Common\Traits\NonPersistentModel\Repository;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use AdoreMe\Common\Models\NonPersistentModel;

/**
 * @see ModelProviderRepositoryInterface
 * @since 2.0.0
 */
trait NonPersistentModelModelProviderRepositoryTrait
{
    /** @var ModelInterface|NonPersistentModel */
    protected $model;

    /**
     * NonPersistentModelModelProviderRepositoryTrait constructor.
     * Please overwrite and type hint appropriately the __construct function.
     *
     * @param ModelInterface|NonPersistentModel $model
     * @throws \Exception
     */
    public function __construct(NonPersistentModel $model)
    {
        $this->model = $model;

        throw new \Exception('Please overwrite and type hint appropriately the __construct function.');
    }

    /**
     * Create a new instance of model, and returns it.
     * The model should be "dirty", as in attributes are not sync with original.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createNewModel(array $attributes = []): ModelInterface
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Create a new instance of model, populated with given attributes, that should emulate an model that existed before
     * The model should not be "dirty", as in attributes are in sync with original.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createOldModel(array $attributes = []): ModelInterface
    {
        return $this->model->oldInstance($attributes);
    }
}
