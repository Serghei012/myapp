<?php
namespace AdoreMe\Common\Traits;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use AdoreMe\Common\Traits\Eloquent\Repository\EloquentSwitchValueRepositoryTrait;
use Closure;
use Exception;
use Illuminate\Support\Collection;

/**
 * @property RepositoryInterface|EloquentSwitchValueRepositoryTrait repository
 */
trait SwitchValueTrait
{
    /**
     * Switch value for given entry.
     * Return a collection of updated models.
     *
     * @param ModelInterface $model
     * @param string $column
     * @param int $newValue
     * @param Closure $collectionClosure Closure that should generate the collection used for filtering, and calculate
     * the new values.
     * @return Collection
     * @throws Exception
     */
    protected function switchValueForOne(
        ModelInterface $model,
        string $column,
        int $newValue,
        Closure $collectionClosure
    ): Collection {
        $oldValue = $model->{$column};
        // If the value was not changed do nothing.
        if ($oldValue == $newValue) {
            return Collection::make([]);
        }

        // Check if the value is in use by another model, and calculate the maximum value.
        $collection   = $collectionClosure();
        $maxValue     = 0;
        $valueIsInUse = false;
        /** @var ModelInterface $item */
        foreach ($collection as $item) {
            $itemValue = $item->{$column};
            if ($itemValue == $newValue) {
                $valueIsInUse = true;
            }

            if ($maxValue < $itemValue) {
                $maxValue = $itemValue;
            }
        }

        // Check if value is not in use by any model. We can simply use the given value.
        if (! $valueIsInUse) {
            // Change the new value to maximum + 1, so we don't have big numbers in db.
            // Example: if we request value 555, and maximum is 10, then we will use 11 instead.
            if ($newValue > $maxValue) {
                $newValue = $maxValue + 1;
            }

            // Do not change, if the old value + 1 = new value. This means the value is already the biggest.
            if ($oldValue + 1 == $newValue) {
                return Collection::make([]);
            }

            // Update the value.
            $model->{$column} = $newValue;
            if ($this->repository->saveModel($model)) {
                return Collection::make([$model]);
            }

            return Collection::make([]);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $modelId     = $model->id;
        $minValue    = is_null($oldValue) ? $newValue : min($newValue, $oldValue);
        $maxValue    = is_null($oldValue) ? $maxValue : max($newValue, $oldValue);
        $isIncrement = is_null($oldValue) ? true : ($oldValue > $newValue ? true : false);

        // Programmatically calculate the value for each affected id.
        $modifiedCollection = [];
        /** @var ModelInterface $item */
        foreach ($collection as $item) {
            $itemValue = $item->{$column};
            /** @noinspection PhpUndefinedFieldInspection */
            $itemId = $item->id;

            // Update the requested model to given $newValue.
            if ($itemId == $modelId) {
                $item->{$column}      = $newValue;
                $modifiedCollection[] = $item;

                continue;
            }

            // Affect only values between min and max.
            if ($itemValue < $minValue || $itemValue > $maxValue) {
                continue;
            }

            $item->{$column}      = $isIncrement ? ++$itemValue : --$itemValue;
            $modifiedCollection[] = $item;
        }
        $modifiedCollection = Collection::make($modifiedCollection);

        // Sort collection by $column ASC.
        $modifiedCollection = $modifiedCollection->sortBy($column);

        $switchResult = $this->repository->switchValue(
            $column,
            $modelId,
            $newValue,
            $minValue,
            $maxValue,
            $isIncrement,
            $modifiedCollection
        );

        if (! $switchResult) {
            throw new Exception('Unable to switch values for ' . $column . '.');
        }

        return $modifiedCollection;
    }
}
