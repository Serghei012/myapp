<?php
namespace AdoreMe\Common\Traits\Eloquent\Repository;

use AdoreMe\Common\Exceptions\ResourceConflictException;
use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface;
use AdoreMe\Common\Traits\Eloquent\EloquentPriorityHandlerAttributesTrait;
use AdoreMe\Common\Traits\SwitchValueTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @see PriorityHandlerRepositoryInterface
 * @see \AdoreMe\Common\Traits\Eloquent\EloquentPriorityHandlerAttributesTrait
 */
trait EloquentPriorityHandlerRepositoryTrait
{
    use EloquentSwitchValueRepositoryTrait;
    use SwitchValueTrait;

    /** @var ModelInterface|EloquentModel */
    protected $model;

    /** @var string */
    protected $errorMessageAttributeNotUnique = [
        PriorityHandlerRepositoryInterface::PRIORITY => 'Priority "%s" already in use by id #%s.',
    ];

    /** @var string */
    protected $errorMessageAttributePriorityCannotBe0OrNegative = 'Priority cannot be 0 or negative.';

    /** @var array */
    protected $uniqueAttributes = [
        PriorityHandlerRepositoryInterface::PRIORITY,
    ];

    /** @var array */
    protected $maximumCharsPerUniqueAttribute = [];

    /**
     * Work around to use SwitchValueTrait.
     */
    protected $repository;

    /**
     * Add ability to overwrite attributes.
     *
     * @param array $errorMessageAttributeNotUnique
     * @param array $uniqueAttributes
     * @param string $errorMessageAttributePriorityCannotBe0OrNegative
     * @param array $maximumCharsPerUniqueAttribute
     * @param bool $sqlCheckConstrainsAtEndOfStatementOrTransaction
     */
    protected function initEloquentPriorityHandlerRepositoryTrait(
        array $errorMessageAttributeNotUnique = null,
        array $uniqueAttributes = null,
        string $errorMessageAttributePriorityCannotBe0OrNegative = null,
        array $maximumCharsPerUniqueAttribute = null,
        bool $sqlCheckConstrainsAtEndOfStatementOrTransaction = null
    ) {
        if (! is_null($errorMessageAttributeNotUnique)) {
            $this->errorMessageAttributeNotUnique = $errorMessageAttributeNotUnique;
        }

        if (! is_null($uniqueAttributes)) {
            $this->uniqueAttributes = $uniqueAttributes;
        }

        if (! is_null($errorMessageAttributePriorityCannotBe0OrNegative)) {
            $this->errorMessageAttributePriorityCannotBe0OrNegative = $errorMessageAttributePriorityCannotBe0OrNegative;
        }

        if (! is_null($maximumCharsPerUniqueAttribute)) {
            $this->maximumCharsPerUniqueAttribute = $maximumCharsPerUniqueAttribute;
        }

        $this->initEloquentSwitchValueRepositoryTrait($sqlCheckConstrainsAtEndOfStatementOrTransaction);

        // Work around to use SwitchValueTrait.
        $this->repository = $this;
    }

    /**
     * Retrieve all enabled shipping promotions from database.
     *
     * @return Collection
     */
    public function findByEnabledOrderedByPriority(): Collection
    {
        $eloquentBuilder = $this->model->newQuery();

        $eloquentBuilder->where('enabled', '=', 1);
        $eloquentBuilder->getQuery()->orderBy('priority');

        return $eloquentBuilder->get()->keyBy('id')->values();
    }

    /**
     * Create a model, and calculate the priority.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createAndHandlePriority(array $attributes): ModelInterface
    {
        $attributes = $this->throwConflictExceptionIfAttributesNotUniqueAndReturnFixedAttributes($attributes);
        $attributes = $this->calculateAndUpdatePriorityAttribute($attributes);

        return $this->createModel($attributes);
    }

    /**
     * Replace the model, and calculate the priority.
     *
     * @param ModelInterface|EloquentModel $model
     * @param array $attributes
     * @return ModelInterface|null
     */
    public function replaceAndHandlePriority(ModelInterface $model, array $attributes)
    {
        return $this->updateAndHandlePriority($model, $attributes, true);
    }

    /**
     * Update the model, and calculate the priority.
     *
     * @param ModelInterface|EloquentModel|EloquentPriorityHandlerAttributesTrait $model
     * @param array $attributes
     * @param bool $replace
     * @return ModelInterface|EloquentModel|null
     */
    public function updateAndHandlePriority(ModelInterface $model, array $attributes, bool $replace = false)
    {
        $modelId = $model->id;

        $attributes = $this->throwConflictExceptionIfAttributesNotUniqueAndReturnFixedAttributes($attributes, $modelId);
        $attributes = $this->calculateAndUpdatePriorityAttribute($attributes, $model);

        if ($replace) {
            // Replace all attributes from model.
            $model->setRawAttributes([]);
        }

        $model->fill($attributes);
        $model->id = $modelId;

        // Save the model.
        $this->saveModel($model);

        return $model;
    }

    /**
     * Update the priority based on attributes.
     *
     * @param array $attributes
     * @param ModelInterface|EloquentModel|EloquentPriorityHandlerAttributesTrait $model
     * @return array
     * @throws ResourceConflictException
     */
    protected function calculateAndUpdatePriorityAttribute(array $attributes, ModelInterface $model = null): array
    {
        $defaultPriority = is_null($model) ? null : $model->priority;
        $defaultEnabled  = is_null($model) ? false : ($model->enabled ?? false);

        $priority = $attributes['priority'] ?? null;
        $enabled  = $attributes['enabled']  ?? null;

        // Make sure the priority and/or enabled are set in attributes, if were not provided.
        if (is_null($priority)) {
            $priority               = $defaultPriority;
            $attributes['priority'] = $priority;
        }
        if (is_null($enabled)) {
            $enabled               = $defaultEnabled;
            $attributes['enabled'] = $enabled;
        }

        if ($enabled) {
            $priority = is_null($priority) ? $this->getNextAvailablePriority() : $priority;
        } else {
            $priority = null;
        }

        // Do nothing if the priority was not changed.
        if (! is_null($model) && $model->priority == $priority) {
            return $attributes;
        }

        // Throw exception if priority becomes 0 or negative.
        if (! is_null($priority) && $priority <= 0) {
            $exception = new ResourceConflictException($this->errorMessageAttributePriorityCannotBe0OrNegative);

            throw $exception;
        }

        // Do not keep incrementing the priority, if is already the highest.
        if (
            ! is_null($model)
            && ! is_null($model->priority)
            && $model->priority + 1 == $priority
        ) {
            $priority = $model->priority;
        }

        $attributes[PriorityHandlerRepositoryInterface::PRIORITY] = $priority;

        return $attributes;
    }

    /**
     * Return the next available priority.
     *
     * @return int
     */
    protected function getNextAvailablePriority(): int
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $maxPriority = DB::selectOne('SELECT MAX(priority) AS max_priority FROM ' . $this->model->getTable());

        return (int) $maxPriority->max_priority + 1;
    }

    /**
     * Check if the unique attributes are unique, and is not the given id.
     * Also fixes attributes, when an unique element has a maximum number of characters allowed.
     *
     * @param array $attributes
     * @param int|null $id
     * @return array
     * @throws ResourceConflictException
     */
    protected function throwConflictExceptionIfAttributesNotUniqueAndReturnFixedAttributes(
        array $attributes,
        int $id = null
    ): array {
        $attributeValues = [];

        $eloquentBuilder = $this->model->newQuery();
        foreach ($this->uniqueAttributes as $attributeName) {
            $attributeValues[$attributeName] = $attributes[$attributeName] ?? '';
            $attributeMaximumChars           = $this->maximumCharsPerUniqueAttribute[$attributeName] ?? null;

            if (! is_null($attributeMaximumChars)) {
                $attributeValues[$attributeName] = substr($attributeValues[$attributeName], 0, $attributeMaximumChars);
                $attributes[$attributeName]      = $attributeValues[$attributeName];
            }

            $eloquentBuilder->where(
                $attributeName,
                '=',
                $attributeValues[$attributeName],
                'or'
            );
        }

        // Get results.
        $collection = $eloquentBuilder->get()->keyBy('id');

        // No model found with already existing attributes. Exit without exception.
        if ($collection->isEmpty()) {
            return $attributes;
        }

        /** @var ModelInterface|EloquentPriorityHandlerAttributesTrait|EloquentModel $model */
        foreach ($collection as $model) {
            $modelId = $model->id;

            foreach ($this->uniqueAttributes as $attributeName) {
                $attributeValue = $model->{$attributeName};

                if ($attributeValue == $attributeValues[$attributeName] && $modelId != $id) {
                    $exception             = new ResourceConflictException(
                        sprintf(
                            $this->errorMessageAttributeNotUnique[$attributeName],
                            $attributeValues[$attributeName],
                            $modelId
                        )
                    );
                    $exception->resourceId = $modelId;

                    throw $exception;
                }
            }
        }

        return $attributes;
    }

    /**
     * Switch priority for given id, and return a collection of changed elements.
     *
     * @param ModelInterface|EloquentModel|EloquentPriorityHandlerAttributesTrait $model
     * @param int $newPriority
     * @return Collection
     * @throws ResourceConflictException
     */
    public function switchPriority(ModelInterface $model, int $newPriority): Collection
    {
        // Throw exception if priority becomes 0 or negative.
        if ($newPriority <= 0) {
            $exception = new ResourceConflictException($this->errorMessageAttributePriorityCannotBe0OrNegative);

            throw $exception;
        }

        return $this->switchValueForOne(
            $model,
            'priority',
            $newPriority,
            function () {
                return $this->findByEnabledOrderedByPriority();
            }
        );
    }
}
