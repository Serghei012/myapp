<?php
namespace laravel\AdoreMe\Storage\Models;

class DummyStoreClassTest
{
    protected $test;

    /**
     * DummyStoreClassTest constructor.
     */
    public function __construct()
    {
        $this->test = 0;
    }

    /**
     * @return int
     */
    public function get()
    {
        return $this->test;
    }

    /**
     * @param $value
     */
    public function set($value)
    {
        $this->test = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->test);
    }
}
