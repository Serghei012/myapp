<?php
namespace AdoreMe\Common\Traits\Eloquent\Repository;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;

/**
 * @see ModelProviderRepositoryInterface
 * @since 2.0.0
 * @property int id
 */
trait EloquentRepositoryTrait
{
    use EloquentModelProviderRepositoryTrait;

    /** @var ModelInterface|EloquentModel */
    protected $model;

    /**
     * Create a new model in database, and returns it.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createModel(array $attributes = []): ModelInterface
    {
        $model = $this->createNewModel($attributes);

        $this->saveModel($model);

        return $model;
    }

    /**
     * Save the model to database.
     *
     * @param ModelInterface|EloquentModel $model
     * @return bool
     */
    public function saveModel(ModelInterface $model): bool
    {
        return $model->save();
    }

    /**
     * Update the model in the database.
     *
     * @param ModelInterface|EloquentModel $model
     * @param array $attributes
     * @return bool
     */
    public function updateModel(ModelInterface $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    /**
     * Replace the model in the database.
     *
     * @param ModelInterface|EloquentModel $model
     * @param array $attributes
     * @return bool
     */
    public function replaceModel(ModelInterface $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    /**
     * Delete the model from database.
     *
     * @param ModelInterface|EloquentModel $model
     * @return bool
     * @throws \Exception
     */
    public function deleteModel(ModelInterface $model): bool
    {
        return $model->delete() ?? false;
    }

    /**
     * Return a collection of all models from database.
     *
     * @return Collection of Models
     */
    public function find(): Collection
    {
        return $this->getCollectionFromBuilder(
            $this->model->newQuery()
        );
    }

    /**
     * Return a collection of models from database that meet the criteria.
     *
     * @param string $attribute
     * @param mixed|array $value
     * @return Collection
     */
    public function findBy(string $attribute, $value): Collection
    {
        return $this->getCollectionFromBuilder(
            $this->builderFindBy($attribute, $value)
        );
    }

    /**
     * Return first model from database that meet the criteria.
     *
     * @param string $attribute
     * @param mixed $value
     * @return ModelInterface|EloquentModel|null
     */
    public function findOneBy(string $attribute, $value)
    {
        $builder = $this->builderFindBy($attribute, $value);
        /** @var ModelInterface|EloquentModel $model */
        $model = $builder->first();

        return $model;
    }

    /**
     * Return a collection with items having the provided ids.
     *
     * @param array $ids
     * @return Collection
     */
    public function findByIds(array $ids): Collection
    {
        return $this->model->newQuery()->findMany($ids);
    }

    /**
     * Return one model from database by id.
     * Id can be integer, alpha numeric, or anything else.
     *
     * @param mixed $id
     * @return ModelInterface|EloquentModel|null
     */
    public function findOneById($id)
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Create the eloquent builder, that finds something by given criteria.
     *
     * @param string $attribute
     * @param $value
     * @return Builder
     */
    protected function builderFindBy(string $attribute, $value): Builder
    {
        $eloquentBuilder = $this->model->newQuery();

        // Add condition
        if (is_array($value)) {
            $eloquentBuilder->getQuery()->whereIn($attribute, $value);
        } else {
            $eloquentBuilder->where($attribute, $value);
        }

        return $eloquentBuilder;
    }

    /**
     * Get the collection from builder.
     *
     * @param Builder $builder
     * @return Collection
     */
    protected function getCollectionFromBuilder(Builder $builder): Collection
    {
        return $builder->get()->keyBy($this->model->getKeyName())->values();
    }
}
