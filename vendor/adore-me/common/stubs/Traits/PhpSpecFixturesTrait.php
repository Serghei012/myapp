<?php
namespace stubs\AdoreMe\Common\Traits;

use stubs\AdoreMe\Common\Application\Laravel;
use stubs\AdoreMe\Common\Interfaces\FixturesInterface;

trait PhpSpecFixturesTrait
{
    /**
     * Get the fixtures model.
     *
     * @return FixturesInterface
     */
    abstract protected function getFixturesModel(): FixturesInterface;

    /**
     * Reset database and run fixtures.
     *
     * @return void
     */
    protected function fixturesUp()
    {
        $this->getFixturesModel()->up();
    }

    /**
     * Prepare Laravel for testing, by running the migrations, etc.
     *
     * @return void
     */
    protected function appUp()
    {
        $this->getApp()->setUp();
    }

    /**
     * Remove the fixtures.
     *
     * @return void
     */
    protected function fixturesDown()
    {
        $this->getFixturesModel()->down();
    }

    /**
     * Unprepare Laravel, by resetting the migrations, dropping table "migrations", flushing caches, storage, etc.
     *
     * @return void
     */
    protected function appDown()
    {
        $this->getApp()->setDown();
    }

    /**
     * Return laravel app.
     *
     * @return Laravel
     */
    protected function getApp(): Laravel
    {
        /** @var Laravel $app */
        $app = app();

        return $app;
    }
}
