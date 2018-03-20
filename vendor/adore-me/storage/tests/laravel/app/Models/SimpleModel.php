<?php
namespace laravel\AdoreMe\Storage\Models;

class SimpleModel
{
    /** @var string */
    public $name;

    /**
     * SimpleModel constructor.
     *
     * @param string $name
     */
    function __construct(string $name)
    {
        $this->name = $name;
    }
}
