<?php
namespace AdoreMe\Factory\Interfaces;

use AdoreMe\Logger\Interfaces\LoggerInterface;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

interface ModelInterface extends Arrayable, Jsonable, LoggerInterface
{
    /**
     * Set the config.
     *
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * Get the config.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Lets say we have an config with $config = ['a' => 'test', 'b' => ['config' => 'another_test']]
     * setConfig function can handle the "a" and "b" validation, but the "b" additional config might not be handled,
     * because the "b" was not yet executed, and might not even be executed due to various conditions, or is expensive,
     * and needs to be called only when is really needed.
     *
     * This function is to be used when we want to validate api call with an array of data, and validate that all
     * the config is valid, so we can save into database.
     *
     * This function is never to be called during normal execution, but only for admin purpose validation.
     *
     * @return bool
     */
    public function adminValidateAllConfigLevels(): bool;

    /**
     * Get the config without defaults. This should be used for persistence.
     *
     * @return array
     */
    public function getConfigWithoutDefaults(): array;
}
