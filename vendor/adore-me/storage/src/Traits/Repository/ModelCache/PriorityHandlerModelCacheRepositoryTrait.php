<?php
namespace AdoreMe\Storage\Traits\Repository\ModelCache;

use Closure;
use Illuminate\Support\Collection;
use AdoreMe\Common\Interfaces\ModelInterface;

trait PriorityHandlerModelCacheRepositoryTrait
{
    use ModelCacheRepositoryTrait;

    /**
     * @return Collection of AbstractModel
     */
    public function find(): Collection
    {
        /** @var ModelCacheRepositoryTrait $this */
        return $this->getStoredCollectionOrExecuteParentMethodAndStoreResult(
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * Retrieve all enabled shipping methods from database.
     *
     * @return Collection
     */
    public function findByEnabledOrderedByPriority(): Collection
    {
        /** @var ModelCacheRepositoryTrait $this */
        return $this->getStoredCollectionOrExecuteParentMethodAndStoreResult(
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * Create a model, and calculate the priority.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createAndHandlePriority(array $attributes): ModelInterface
    {
        /** @var ModelCacheRepositoryTrait $this */
        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $model = parent::createAndHandlePriority($attributes);
        $this->enablePostProcessing();

        $this->postProcessingByCollection(null, Collection::make([$model]));

        return $model;
    }

    /**
     * Replace the model, and calculate the priority.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return ModelInterface|null
     */
    public function replaceAndHandlePriority(ModelInterface $model, array $attributes)
    {
        /** @var ModelCacheRepositoryTrait $this */
        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $model = parent::replaceAndHandlePriority($model, $attributes);
        $this->enablePostProcessing();

        // Model was not found.
        if (is_null($model)) {
            return null;
        }

        $this->postProcessingByCollection(Collection::make([$model]));

        return $model;
    }

    /**
     * Update the model, and calculate the priority.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return ModelInterface|null
     */
    public function updateAndHandlePriority(ModelInterface $model, array $attributes)
    {
        /** @var ModelCacheRepositoryTrait $this */
        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $model = parent::updateAndHandlePriority($model, $attributes);
        $this->enablePostProcessing();

        // Model was not found.
        if (is_null($model)) {
            return null;
        }

        $this->postProcessingByCollection(Collection::make([$model]));

        return $model;
    }

    /**
     * Switch priority for given id, and return a collection of changed elements.
     *
     * @param ModelInterface $model
     * @param int $newPriority
     * @return Collection
     */
    public function switchPriority(ModelInterface $model, int $newPriority): Collection
    {
        /** @var ModelCacheRepositoryTrait $this */
        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Collection $modifiedModelCollection */
        $modifiedModelCollection = parent::switchPriority($model, $newPriority);
        $this->enablePostProcessing();

        // Do nothing else if nothing was changed.
        if ($modifiedModelCollection->isEmpty()) {
            return $modifiedModelCollection;
        }

        $this->postProcessingByCollection($modifiedModelCollection);

        return $modifiedModelCollection;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Do post processing after any operation that involves a collection of models.
     *
     * @param Collection|null $modifiedModelCollection
     * @param Collection|null $createdModelCollection
     * @param Collection|null $destroyedModelCollection
     * @param bool $ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution
     * @return array
     * @throws \Exception
     */
    private function postProcessingByCollection(
        Collection $modifiedModelCollection = null,
        Collection $createdModelCollection = null,
        Collection $destroyedModelCollection = null,
        bool $ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution = false
    ): array {
        /** @var ModelCacheRepositoryTrait $this */
        // Do nothing if the post processing is disabled, or ignore was requested.
        if (! $ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution && $this->isPostProcessingDisabled) {
            return [];
        }

        // Handle modified model collection.
        if (! is_null($modifiedModelCollection) && ! $modifiedModelCollection->isEmpty()) {
            /** @var ModelInterface $model */
            foreach ($modifiedModelCollection as $model) {
                // Create the cache for the model.
                $this->storeModel($model);
            }
        }

        // Handle created model collection.
        if (! is_null($createdModelCollection) && ! $createdModelCollection->isEmpty()) {
            /** @var ModelInterface $model */
            foreach ($createdModelCollection as $model) {
                // Create the cache for the model.
                $this->storeModel($model);
            }
        }

        // Handle destroyed model collection.
        if (! is_null($destroyedModelCollection) && ! $destroyedModelCollection->isEmpty()) {
            /** @var ModelInterface $model */
            foreach ($destroyedModelCollection as $model) {
                // Create the model cache with null.
                /** @noinspection PhpUndefinedFieldInspection */
                $this->storeModelById($model->id, null);
            }
        }

        $return = [
            'find'                           => $this->postProcessingFind(
                $createdModelCollection,
                $destroyedModelCollection
            ),
            'findByEnabledOrderedByPriority' => $this->postProcessingFindByEnabledOrderedByPriority(
                $modifiedModelCollection,
                $createdModelCollection,
                $destroyedModelCollection
            ),
        ];

        if ($ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution) {
            $this->enablePostProcessing();
        }

        return $return;
    }

    /**
     * Post process to update the "find" function.
     *
     * @param Collection $createdModelCollection
     * @param Collection $destroyedModelCollection
     * @return bool
     */
    private function postProcessingFind(
        Collection $createdModelCollection = null,
        Collection $destroyedModelCollection = null
    ): bool {
        /** @var ModelCacheRepositoryTrait $this */
        // If cache does not exist, then do nothing.
        $cacheKey = $this->constructKeyForMethodAndArguments('find');
        if (! $this->cacheRepository->has($cacheKey)) {
            return false;
        }

        // Do nothing, if no model was created or destroyed.
        if ($createdModelCollection == null && $destroyedModelCollection == null) {
            return true;
        }

        $collection = $this->postProcessingCollectionFind(
            $createdModelCollection,
            $destroyedModelCollection
        );

        $this->getStoredCollectionOrExecuteParentMethodAndStoreResult(
            'find',
            [],
            true,
            $collection,
            false
        );

        return true;
    }

    /**
     * Post process the find collection, after updates.
     *
     * @param Collection|null $createdModelCollection
     * @param Collection $destroyedModelCollection
     * @return Collection
     */
    private function postProcessingCollectionFind(
        Collection $createdModelCollection = null,
        Collection $destroyedModelCollection = null
    ): Collection {
        $collection = $this->filterCollection([], $this->find());
        $collection = $this->filterCollection($collection, $createdModelCollection, null);
        $collection = $this->filterCollection($collection, $destroyedModelCollection, null, true);
        $collection = Collection::make($collection);

        // Sort the collection by priority.
        return $collection->sortBy('id');
    }

    /**
     * Post process to update the "findByEnabledOrderedByPriority" function.
     *
     * @param Collection $modifiedModelCollection
     * @param Collection $createdModelCollection
     * @param Collection $destroyedModelCollection
     * @return bool
     */
    private function postProcessingFindByEnabledOrderedByPriority(
        Collection $modifiedModelCollection = null,
        Collection $createdModelCollection = null,
        Collection $destroyedModelCollection = null
    ): bool {
        /** @var ModelCacheRepositoryTrait $this */
        // If cache does not exist, then do nothing.
        $cacheKey = $this->constructKeyForMethodAndArguments('findByEnabledOrderedByPriority');
        if (! $this->cacheRepository->has($cacheKey)) {
            return false;
        }

        // Do nothing, if no model was modified, created or destroyed.
        if ($modifiedModelCollection == null && $createdModelCollection == null && $destroyedModelCollection == null) {
            return true;
        }

        $collection = $this->postProcessingCollectionFindByEnabledOrderedByPriority(
            $modifiedModelCollection,
            $createdModelCollection,
            $destroyedModelCollection
        );

        $this->getStoredCollectionOrExecuteParentMethodAndStoreResult(
            'findByEnabledOrderedByPriority',
            [],
            true,
            $collection,
            false
        );

        return true;
    }

    /**
     * Post process the find collection, after updates.
     *
     * @param Collection $modifiedModelCollection
     * @param Collection|null $createdModelCollection
     * @param Collection $destroyedModelCollection
     * @return Collection
     */
    private function postProcessingCollectionFindByEnabledOrderedByPriority(
        Collection $modifiedModelCollection = null,
        Collection $createdModelCollection = null,
        Collection $destroyedModelCollection = null
    ): Collection {
        $closureIgnoreDisabled = function (ModelInterface $model) {
            /** @noinspection PhpUndefinedFieldInspection */
            return $model->enabled == false;
        };

        $collection = $this->filterCollection(
            [],
            $this->findByEnabledOrderedByPriority(),
            $closureIgnoreDisabled
        );
        $collection = $this->filterCollection($collection, $modifiedModelCollection, $closureIgnoreDisabled);
        $collection = $this->filterCollection($collection, $createdModelCollection, $closureIgnoreDisabled);
        $collection = $this->filterCollection($collection, $destroyedModelCollection, null, true);
        $collection = Collection::make($collection);

        // Sort the collection by priority.
        return $collection->sort(
            function (ModelInterface $a, ModelInterface $b) {
                /** @noinspection PhpUndefinedFieldInspection */
                $aPriority = $a->priority;
                /** @noinspection PhpUndefinedFieldInspection */
                $bPriority = $b->priority;

                if (is_null($aPriority)) {
                    return 1;
                } else if (is_null($bPriority)) {
                    return -1;
                }

                // If more models has same priority, the biggest id is first, unless $a = $modelId.
                if ($aPriority == $bPriority) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    return $a->id < $b->id ? -1 : 1;
                }

                return $aPriority < $bPriority ? -1 : 1;
            }
        );
    }

    /**
     * Insert the missing models from collection to array, if not already exists, and is not disabled.
     *
     * @param array $array
     * @param Collection $collection
     * @param Closure $additionalValidation
     * @param bool $isToBeRemoved
     * @return array
     */
    private function filterCollection(
        array $array,
        Collection $collection = null,
        Closure $additionalValidation = null,
        bool $isToBeRemoved = false
    ): array {
        // Do nothing if we didn't received any collections.
        if (is_null($collection)) {
            return $array;
        }

        /** @var ModelInterface $model */
        foreach ($collection as $model) {
            /** @noinspection PhpUndefinedFieldInspection */
            $modelId = $model->id;

            // Remove the model, by id, from array, if was requested.
            if ($isToBeRemoved && array_key_exists($modelId, $array)) {
                unset ($array[$modelId]);
                continue;
            }

            if (
                array_key_exists($modelId, $array)
                || (
                    ! is_null($additionalValidation)
                    && $additionalValidation($model)
                )
            ) {
                continue;
            }

            $array[$modelId] = $model;
        }

        return $array;
    }
}
