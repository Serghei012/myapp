<?php
namespace AdoreMe\Common\Interfaces\Repository;

use AdoreMe\Common\Interfaces\ModelInterface;
use Illuminate\Support\Collection;

/**
 * @see \AdoreMe\Common\Traits\Eloquent\Repository\EloquentPriorityHandlerRepositoryTrait
 * @see \AdoreMe\Common\Traits\Eloquent\EloquentPriorityHandlerAttributesTrait
 */
interface PriorityHandlerRepositoryInterface
{
    const PRIORITY = 'priority';
    const ENABLED  = 'enabled';

    /**
     * Retrieve all enabled shipping methods from database.
     *
     * @return Collection
     */
    public function findByEnabledOrderedByPriority(): Collection;

    /**
     * Create a model, and calculate the priority.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createAndHandlePriority(array $attributes): ModelInterface;

    /**
     * Replace the model, and calculate the priority.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return ModelInterface|null
     */
    public function replaceAndHandlePriority(ModelInterface $model, array $attributes);

    /**
     * Update the model, and calculate the priority.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return ModelInterface|null
     */
    public function updateAndHandlePriority(ModelInterface $model, array $attributes);

    /**
     * Switch priority for given id, and return a collection of changed elements.
     *
     * @param ModelInterface $model
     * @param int $newPriority
     * @return Collection
     */
    public function switchPriority(ModelInterface $model, int $newPriority): Collection;
}
