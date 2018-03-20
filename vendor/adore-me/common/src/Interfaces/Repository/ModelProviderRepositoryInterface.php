<?php
namespace AdoreMe\Common\Interfaces\Repository;

use AdoreMe\Common\Interfaces\ModelInterface;

/**
 * @see \AdoreMe\Common\Traits\NonPersistentModel\Repository\NonPersistentModelModelProviderRepositoryTrait
 * @see \AdoreMe\Common\Traits\Eloquent\Repository\EloquentModelProviderRepositoryTrait
 * @since 2.0.0
 */
interface ModelProviderRepositoryInterface
{
    /**
     * Create a new instance of model, and returns it.
     * The model should be "dirty", as in attributes are not sync with original.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createNewModel(array $attributes = []): ModelInterface;

    /**
     * Create a new instance of model, populated with given attributes, that should emulate an model that existed before
     * The model should not be "dirty", as in attributes are in sync with original.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createOldModel(array $attributes = []): ModelInterface;
}
