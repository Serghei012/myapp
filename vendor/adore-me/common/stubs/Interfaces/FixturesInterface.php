<?php
namespace stubs\AdoreMe\Common\Interfaces;

interface FixturesInterface
{
    /**
     * Insert the fixtures to db.
     *
     * @return void
     */
    public function up();

    /**
     * Remove the fixtures from db.
     *
     * @return void
     */
    public function down();
}
