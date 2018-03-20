<?php
namespace AdoreMe\Storage\Traits\Repository\ModelCache;

use AdoreMe\Common\Helpers\ObjectHelper;
use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Interfaces\Repository\Cache\CacheRepositoryInterface as CacheRepository;
use Illuminate\Support\Collection;
use AdoreMe\Common\Interfaces\ModelInterface;

/**
 * DO NOTE: Is intended tha all functions are private here. Is to avoid possible code breakup
 * if same methods with same name are created when inherited models.
 * Store rules:
 * - Only the model has data stored.
 * - Anything else (custom filters, collections, findBy(x, y), etc) only store the reference to the model id.
 *   The model itself will be loaded from store.
 * - Even functions like findOneByX should store the reference to model id.
 * - CRUD operations will invalidate keys having as tag the model id.
 * - Any collections and references should be invalidated programmatically by code.
 */
trait ModelCacheRepositoryTrait
{
    /** @var string */
    private static $glue = ':';

    /** @var bool */
    private $isPostProcessingDisabled = false;

    /** @var CacheRepository */
    private $cacheRepository = false; // Set to false, so it crash if boot not called.

    /** @var string */
    private $resourceIdentifier = false; // Set to false, so it crash if boot not called.

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Init the trait.
     *
     * @param CacheRepository $cacheRepository
     * @param string $resourceIdentifier
     */
    private function initCacheRepositoryTrait(CacheRepository $cacheRepository, string $resourceIdentifier)
    {
        $this->cacheRepository    = $cacheRepository;
        $this->resourceIdentifier = $resourceIdentifier;
    }

    /**
     * Create a new model in database, and returns it.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createModel(array $attributes = []): ModelInterface
    {
        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @var ModelInterface|null $model */
        $model = parent::createModel($attributes);
        $this->enablePostProcessing();

        $this->postProcessingByCollection(
            null,
            Collection::make([$model]),
            null
        );

        return $model;
    }

    /**
     * Save the model to database.
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function saveModel(ModelInterface $model): bool
    {
        $modelExists = is_null($model->getAttribute('id')) ? false : true;

        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @var ModelInterface|null $model */
        $return = parent::saveModel($model);
        $this->enablePostProcessing();

        $collection = Collection::make([$model]);
        if ($modelExists) {
            $this->postProcessingByCollection(
                $collection,
                null,
                null
            );
        } else {
            $this->postProcessingByCollection(
                null,
                $collection,
                null
            );
        }

        return $return;
    }

    /**
     * Update the model in the database.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return bool
     */
    public function updateModel(ModelInterface $model, array $attributes): bool
    {
        $modelExists = is_null($model->getAttribute('id')) ? false : true;

        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @var ModelInterface|null $model */
        $return = parent::updateModel($model, $attributes);
        $this->enablePostProcessing();

        $collection = Collection::make([$model]);
        if ($modelExists) {
            $this->postProcessingByCollection(
                $collection,
                null,
                null
            );
        } else {
            $this->postProcessingByCollection(
                null,
                $collection,
                null
            );
        }

        return $return;
    }

    /**
     * Replace the model in the database.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return bool
     */
    public function replaceModel(ModelInterface $model, array $attributes): bool
    {
        $modelExists = is_null($model->getAttribute('id')) ? false : true;

        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @var ModelInterface|null $model */
        $return = parent::replaceModel($model, $attributes);
        $this->enablePostProcessing();

        $collection = Collection::make([$model]);
        if ($modelExists) {
            $this->postProcessingByCollection(
                $collection,
                null,
                null
            );
        } else {
            $this->postProcessingByCollection(
                null,
                $collection,
                null
            );
        }

        return $return;
    }

    /**
     * Delete the model from database.
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function deleteModel(ModelInterface $model): bool
    {
        $this->disablePostProcessing();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @var ModelInterface|null $model */
        $return = parent::deleteModel($model);
        $this->enablePostProcessing();

        $this->postProcessingByCollection(
            null,
            null,
            Collection::make([$model])
        );

        return $return;
    }

    /**
     * Attempt to retrieve an model by id, from storage.
     * Not found model will be attempted to be retrieved from parent::findOneById();
     *
     * Id can be integer, alpha numeric, or anything else.
     *
     * @param mixed $id
     * @return ModelInterface|null
     */
    public function findOneById($id)
    {
        $key   = $this->constructKeyForId($id);
        $value = $this->cacheRepository->get($key);
        $model = $this->hydrateModelUsingValueFromStorage($value, $key);

        // If the model was not found in store, then load it from parent, and store it.
        if ($model === false) {
            $this->disablePostProcessing();
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedClassInspection */
            /** @var ModelInterface|null $model */
            $model = parent::findOneById($id);
            $this->enablePostProcessing();

            $this->storeModelById($id, $model, true, $key);
        }

        return $model;
    }

    /**
     * Return a collection with items having the provided ids.
     * Not found models will be retrieved from parent::findByIds();
     *
     * @param array $ids
     * @return Collection
     */
    public function findByIds(array $ids): Collection
    {
        $position   = 0;
        $missingIds = [];
        $collection = [];

        // Create the empty collection.
        foreach ($ids as $id) {
            $missingIds[$id]         = $position;
            $collection[$position++] = null;
        }

        // Fill the missing model ids, by mass getting from storage.
        if (! empty($missingIds)) {
            $missingIdKeys = [];
            foreach ($missingIds as $id => $position) {
                $missingIdKeys[$id] = $this->constructKeyForId($id);
            }

            $many = $this->cacheRepository->many(array_values($missingIdKeys));
            foreach ($many as $key => $value) {
                $model = $this->hydrateModelUsingValueFromStorage($value, $key);

                // If model is false, then the id was not found in store.
                if ($model === false) {
                    continue;
                    // Model is null. We need to get the id from array instead of model.
                } else if (is_null($model)) {
                    $id = array_search($key, $missingIdKeys);

                    // Could not find the id in model. Meaning something is broken. continue.
                    if ($id == false) {
                        // Set the status.
                        $this->setStoreLastStatus(StorageHelper::STATUS_BROKEN, [$key]);

                        continue;
                    }
                } else {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $id = $model->id;
                }

                $position = $missingIds[$id];

                // Update the collection with the loaded model from store.
                $collection[$position] = $model;
                unset($missingIds[$id]);
            }
        }

        // Fill the missing model ids, by mass getting with find by from parent.
        if (! empty($missingIds)) {
            $this->disablePostProcessing();
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedClassInspection */
            /** @var ModelInterface|null $model */
            $missingIdsCollection = parent::findByIds(array_keys($missingIds));
            $this->enablePostProcessing();

            /** @var ModelInterface $model */
            foreach ($missingIdsCollection as $model) {
                // Store the loaded models.
                $this->storeModel($model, false);

                /** @noinspection PhpUndefinedFieldInspection */
                $id       = $model->id;
                $position = $missingIds[$id];

                // Update the collection with the loaded model from store.
                $collection[$position] = $model;
                unset($missingIds[$id]);
            }
        }

        // Remove the null elements from array.
        $collection = array_filter($collection);

        // Send array values, so we don't have weird keys.
        return Collection::make(array_values($collection));
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return ModelInterface|null
     */
    //public function findOneBy(string $attribute, $value)
    //{
    //    return $this->getStoredMethodModelOrExecuteParentMethodAndStoreResult(
    //        __FUNCTION__,
    //        func_get_args()
    //    );
    //}

    /**
     * @return Collection of ModelInterface
     */
    //public function find(): Collection
    //{
    //    return $this->getStoredCollectionOrExecuteParentMethodAndStoreResult(
    //        __FUNCTION__,
    //        func_get_args()
    //    );
    //}

    /**
     * @param string $field
     * @param $value
     * @return Collection
     */
    //public function findBy(string $field, $value): Collection
    //{
    //    return $this->getStoredCollectionOrExecuteParentMethodAndStoreResult(
    //        __FUNCTION__,
    //        func_get_args()
    //    );
    //}

    /**
     * Store the model.
     *
     * @param ModelInterface $model
     * @param bool $replaceIfAlreadyStored If false, a check if is stored is made first, before trying to store model.
     * @param string $relatedKey
     * @throws \Exception
     */
    private function storeModel(ModelInterface $model, bool $replaceIfAlreadyStored = true, string $relatedKey = null)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $id = $model->id;

        // There is nothing we can do, if there is no model id.
        if (is_null($id)) {
            return;
        }

        $this->storeModelById($id, $model, $replaceIfAlreadyStored, $relatedKey);
    }

    /**
     * Store the model, by id.
     *
     * @param int $id
     * @param ModelInterface $model
     * @param bool $replaceIfAlreadyStored If true, the storage will be overwritten.
     * @param string $key
     * @throws \Exception
     */
    private function storeModelById(
        int $id,
        ModelInterface $model = null,
        bool $replaceIfAlreadyStored = true,
        string $key = null
    ) {
        $key = is_null($key) ? $this->constructKeyForId($id) : $key;

        // Do nothing if the model is stored, and replace was not requested.
        if (! $replaceIfAlreadyStored && $this->cacheRepository->has($key)) {
            // Set the status.
            $this->setStoreLastStatus(StorageHelper::STATUS_HIT, [$key]);

            return;
        }

        // Store the model.
        $this->cacheRepository->forever(
            $key,
            is_null($model) ? '' : $this->prepareModelForStorage($model),
            [
                $this->resourceIdentifier,
                $this->constructTagForId($id),
            ]
        );

        // Set the status.
        $this->setStoreLastStatus(StorageHelper::STATUS_CREATED, [$key]);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Remove the model from storage.
     *
     * @param ModelInterface $model
     */
    private function forgetModel(ModelInterface $model)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $id = $model->id;

        // There is nothing we can do, if there is no model id.
        if (is_null($id)) {
            return;
        }

        $this->forgetModelById($id);
    }

    /**
     * Remove the model from storage, by id.
     *
     * @param int|null $id
     * @param bool $replaceValueWithNull
     * @param string $key
     */
    private function forgetModelById(int $id = null, bool $replaceValueWithNull = false, string $key = null)
    {
        $key = is_null($key) ? $this->constructKeyForId($id) : $key;

        if ($replaceValueWithNull) {
            $this->storeModelById($id, null, true, $key);

            return;
        }

        // Delete from storage.
        if ($this->cacheRepository->forget($key)) {
            // Set the status.
            $this->setStoreLastStatus(StorageHelper::STATUS_DELETED, [$key]);
        }
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Remove all entries from storage with the given model tag.
     *
     * @param ModelInterface $model
     */
    private function forgetByModelTag(ModelInterface $model)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $id = $model->id;

        // There is nothing we can do, if there is no model id.
        if (is_null($id)) {
            return;
        }

        $this->forgetByModelIdTag($id);
    }

    /**
     * Remove all entries from storage with the given model id tag.
     *
     * @param int|null $id
     */
    private function forgetByModelIdTag(int $id)
    {
        $tags = [
            $this->constructTagForId($id),
        ];

        $deletedKeys = $this->cacheRepository->forgetByTags($tags);

        // Set the status.
        $this->setStoreLastStatus(StorageHelper::STATUS_DELETED, $deletedKeys, $tags);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Remove the entry from storage, for the key constructed with given method and arguments.
     *
     * @param string $methodName
     * @param array $arguments
     * @return bool
     */
    private function forgetByMethod(string $methodName, array $arguments = []): bool
    {
        return $this->cacheRepository->forget(
            $this->constructKeyForMethodAndArguments($methodName, $arguments)
        );
    }

    /**
     * Create key for given method name and arguments.
     *
     * @param string $methodName
     * @param array $arguments
     * @return string
     */
    private function constructKeyForMethodAndArguments(string $methodName, array $arguments = []): string
    {
        return ObjectHelper::constructIdentifierForMethodAndArguments(
            $methodName,
            $arguments,
            $this->resourceIdentifier
        );
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Temporary disable post processing, to avoid loops, or having the post processing being called multiple times,
     * when a function is chained to another function from same model, that also calls the post processing.
     */
    private function disablePostProcessing()
    {
        $this->isPostProcessingDisabled = true;
    }

    /**
     * Re-enable the post processing.
     */
    private function enablePostProcessing()
    {
        $this->isPostProcessingDisabled = false;
    }

    /**
     * Construct store key for id.
     *
     * @param int $id
     * @return string
     * @throws \Exception
     */
    private function constructKeyForId(
        /** @noinspection PhpUnusedParameterInspection */
        int $id
    ): string {
        return $this->resourceIdentifier . self::$glue . $id;
    }

    /**
     * Construct tag for id.
     *
     * @param int $id
     * @return string
     * @throws \Exception
     */
    private function constructTagForId(
        /** @noinspection PhpUnusedParameterInspection */
        int $id
    ): string {
        return $this->resourceIdentifier . self::$glue . $id;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Return a collection of buffered data for given method,
     * or execute the parent method, buffer the response, and return the collection.
     *
     * @param string $methodName
     * @param array $arguments
     * @param bool $bypassStore
     * @param Collection $collection
     * @param bool $storeModels
     * @return Collection
     */
    private function getStoredCollectionOrExecuteParentMethodAndStoreResult(
        string $methodName,
        array $arguments = [],
        bool $bypassStore = false,
        Collection $collection = null,
        bool $storeModels = true
    ): Collection {
        $key   = $this->constructKeyForMethodAndArguments($methodName, $arguments);
        $value = $bypassStore ? null : $this->cacheRepository->get($key);

        // Not found in store, or bypass was requested.
        if (is_null($value)) {
            // Set the store status, only if bypass was not asked.
            if (! $bypassStore) {
                $this->setStoreLastStatus(StorageHelper::STATUS_MISS, [$key]);
            }

            // Get the collection, if was not provided.
            if (is_null($collection)) {
                /** @var Collection $collection */
                $collection = call_user_func_array(
                    [
                        'parent',
                        $methodName,
                    ],
                    $arguments
                );
            }

            $ids = [];
            /** @var ModelInterface $model */
            foreach ($collection as $model) {
                /** @noinspection PhpUndefinedFieldInspection */
                $ids[] = $model->id;

                if ($storeModels) {
                    $this->storeModel($model, false);
                }
            }

            $tags = [
                $this->resourceIdentifier,
            ];
            foreach ($ids as $id) {
                $tags[] = $this->constructTagForId($id);
            }

            $this->cacheRepository->forever(
                $key,
                $ids,
                $tags
            );

            // Set the store status.
            $this->setStoreLastStatus(StorageHelper::STATUS_CREATED, [$key]);
            // Found in store. Hydrate.
        } else {
            // Set the store status.
            $this->setStoreLastStatus(StorageHelper::STATUS_HIT, [$key]);

            $collection = $this->findByIds((array) $value);
        }

        return $collection;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Return the stored model for given method,
     * or execute the parent method, store the response, and return the model.
     *
     * @param string $methodName
     * @param array $arguments
     * @param bool $bypassStore
     * @param ModelInterface $model
     * @param bool $storeModel
     * @return ModelInterface|null
     * @throws \Exception
     */
    private function getStoredMethodModelOrExecuteParentMethodAndStoreResult(
        string $methodName,
        array $arguments = [],
        bool $bypassStore = false,
        ModelInterface $model = null,
        bool $storeModel = true
    ) {
        $key   = $this->constructKeyForMethodAndArguments($methodName, $arguments);
        $value = $bypassStore ? null : $this->cacheRepository->get($key);

        // Not found in store, or bypass was requested.
        if (is_null($value)) {
            // Set the store status, only if bypass was not asked.
            if (! $bypassStore) {
                $this->setStoreLastStatus(StorageHelper::STATUS_MISS, [$key]);
            }

            // Get the model, if was not provided.
            if (is_null($model)) {
                /** @var ModelInterface $model */
                $model = call_user_func_array(
                    [
                        'parent',
                        $methodName,
                    ],
                    $arguments
                );
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $id = $model->id;

            if ($storeModel) {
                $this->storeModel($model, false);
            }

            $this->cacheRepository->forever(
                $key,
                $id,
                [
                    $this->resourceIdentifier,
                    $this->constructTagForId($id),
                ]
            );

            // Set the store status.
            $this->setStoreLastStatus(StorageHelper::STATUS_CREATED, [$key]);
            // Found in store. Hydrate.
        } else {
            // Set the store status.
            $this->setStoreLastStatus(StorageHelper::STATUS_HIT, [$key]);

            $model = $this->findOneById((int) $value);
        }

        return $model;
    }

    /**
     * Hydrate the model with value received from storage.
     *
     * @param mixed $value
     * @param string $key
     * @return bool|ModelInterface|null
     */
    private function hydrateModelUsingValueFromStorage($value, string $key)
    {
        // Not found in store. Return false.
        if (is_null($value)) {
            // Set the status.
            $this->setStoreLastStatus(StorageHelper::STATUS_MISS, [$key]);

            return false;
            // Found in store, but is empty, meaning we should return null.
        } else if ($value == '') {
            // Set the store status.
            $this->setStoreLastStatus(StorageHelper::STATUS_HIT, [$key]);

            $model = null;
            // Found in store. Hydrate.
        } else {
            // Set the store status.
            $this->setStoreLastStatus(StorageHelper::STATUS_HIT, [$key]);

            $model = $this->createModelFromStorage($value);

            // Model could not be created. Broken.
            if (is_null($model)) {
                // Set the store status.
                $this->setStoreLastStatus(StorageHelper::STATUS_BROKEN, [$key]);
            }
        }

        return $model;
    }

    /**
     * Set the store last status.
     * Also can add a keys or tags references, with the given status.
     *
     * @param string $status
     * @param array $keyReference
     * @param array $tagReference
     * @throws \Exception
     */
    private function setStoreLastStatus(
        string $status,
        array $keyReference = [],
        array $tagReference = []
    ) {
        StorageHelper::setStoreLastStatus(
            $this->cacheRepository->getStoreName(),
            $status,
            $keyReference,
            $tagReference
        );
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
        /** @noinspection PhpUnusedParameterInspection */
        Collection $modifiedModelCollection = null,
        Collection $createdModelCollection = null,
        Collection $destroyedModelCollection = null,
        bool $ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution = false
    ): array {
        // Do nothing if the post processing is disabled, or ignore was requested.
        if (! $ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution && $this->isPostProcessingDisabled) {
            return [];
        }

        // Here should be the part of code that handles the function.
        throw new \Exception(
            'Please overwrite the '
            . __FUNCTION__
            . ' function in '
            . __CLASS__
            . ' class.'
        );

        /** @noinspection PhpUnreachableStatementInspection */
        if ($ignoreIsPostProcessingDisabledFlagAndRestoreAfterExecution) {
            $this->enablePostProcessing();
        }

        return [];
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Create a model instance using the storage attributes.
     *
     * @param mixed $value
     * @return ModelInterface|null
     */
    private function createModelFromStorage($value)
    {
        /** @var RepositoryInterface $this */
        // Value is not array, so the value is broken.
        if (! is_array($value)) {
            return null;
        }

        return $this->createOldModel((array) $value);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Prepare the model for being stored.
     *
     * @param mixed $model
     * @return mixed|array
     */
    private function prepareModelForStorage(ModelInterface $model)
    {
        return $model->toArray();
    }
}
