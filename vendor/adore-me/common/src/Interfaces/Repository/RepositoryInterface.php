<?php
namespace AdoreMe\Common\Interfaces\Repository;

use AdoreMe\Common\Interfaces\ModelInterface;
use Illuminate\Support\Collection;

/**
 * @since 2.0.0
 */
interface RepositoryInterface extends ModelProviderRepositoryInterface
{
    /**
     * Create a new model in database, and returns it.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function createModel(array $attributes = []): ModelInterface;

    /**
     * Save the model to database.
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function saveModel(ModelInterface $model): bool;

    /**
     * Update the model in the database.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return bool
     */
    public function updateModel(ModelInterface $model, array $attributes): bool;

    /**
     * Replace the model in the database.
     *
     * @param ModelInterface $model
     * @param array $attributes
     * @return bool
     */
    public function replaceModel(ModelInterface $model, array $attributes): bool;

    /**
     * Delete the model from database.
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function deleteModel(ModelInterface $model): bool;

    /**
     * Return a collection of all models from database.
     *
     * @return Collection of Model
     */
    public function find(): Collection;

    /**
     * Return a collection of models from database that meet the criteria.
     *
     * @param string $attribute
     * @param $value
     * @return Collection
     */
    public function findBy(string $attribute, $value): Collection;

    /**
     * Return first model from database that meet the criteria.
     *
     * @param string $attribute
     * @param mixed $value
     * @return ModelInterface|null
     */
    public function findOneBy(string $attribute, $value);

    /**
     * Return a collection with items having the provided ids.
     *
     * @param array $ids
     * @return Collection
     */
    public function findByIds(array $ids): Collection;

    /**
     * Return one model from database by id.
     * Id can be integer, alpha numeric, or anything else.
     *
     * @param mixed $id
     * @return ModelInterface|null
     */
    public function findOneById($id);
}
