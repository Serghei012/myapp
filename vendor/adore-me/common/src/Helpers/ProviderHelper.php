<?php
namespace AdoreMe\Common\Helpers;

use Illuminate\Support\Str;
use Illuminate\Container\Container;

class ProviderHelper
{
    /** @var self */
    protected static $singleton;

    /** @var array */
    protected static $make = [];

    /**
     * Resolve the given type from the container.
     *
     * @param string $alias
     * @param mixed $value
     */
    public static function addMakeResolver(string $alias, $value)
    {
        self::$make[$alias] = $value;
    }

    /**
     * Get singleton instance of this helper.
     *
     * @return ProviderHelper
     */
    public static function getSingletonInstance(): ProviderHelper
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new static();
        }

        return self::$singleton;
    }

    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        // If is Laravel, or has "env" function, then use it.
        if (function_exists('env')) {
            return env($key, $default);
        }

        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Get the available container instance.
     *
     * @param string $abstract
     * @return mixed|Container
     */
    public static function app($abstract = null)
    {
        // If is Laravel, or has "env" function, then use it.
        if (function_exists('app')) {
            return app($abstract);
        }

        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param $abstract
     * @return mixed
     */
    public static function make($abstract)
    {
        if (is_string($abstract) && array_key_exists($abstract, self::$make)) {
            return self::$make[$abstract];
        }

        return Container::getInstance()->make($abstract);
    }

    /**
     * Get the path to the storage folder.
     *
     * @param string $path
     * @return string
     */
    public static function storagePath($path = ''): string
    {
        // If is Laravel, or has "env" function, then use it.
        if (function_exists('storage_path')) {
            return storage_path($path);
        }

        return self::app('path.storage') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
