<?php
namespace stubs\AdoreMe\Common\Models;

use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use stubs\AdoreMe\Common\Interfaces\FixturesInterface;

abstract class FixturesAbstract implements FixturesInterface
{
    /** @var array */
    public $items = [];

    /**
     * Insert the fixtures to db.
     *
     * @return void
     */
    public function up()
    {
        $repository = $this->getRepository();

        foreach ($this->items as $itemAttributes) {
            $repository->createModel($itemAttributes);
        }
    }

    /**
     * Remove the fixtures from db.
     *
     * @return void
     */
    public function down()
    {
        $repository = $this->getRepository();
        $collection = $repository->find();
        foreach ($collection as $item) {
            $repository->deleteModel($item);
        }
    }

    /**
     * Get repository used.
     *
     * @return RepositoryInterface
     */
    abstract public function getRepository();
}
