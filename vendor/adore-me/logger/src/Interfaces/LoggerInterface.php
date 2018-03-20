<?php
namespace AdoreMe\Logger\Interfaces;

use Illuminate\Log\Writer;

interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    /**
     * Pass the logger and debug mode,
     * from given object, to the current object.
     *
     * @param LoggerInterface $model
     */
    public function inheritLogger(LoggerInterface $model);

    /**
     * Sets a logger instance on the object.
     *
     * @param Writer $logger
     * @return $this
     */
    public function setLogger(Writer $logger);

    /**
     * Gets the logger instance from the object
     *
     * @return null|Writer
     */
    public function getLogger();

    /**
     * Enable/disable debug mode.
     *
     * @param bool $value
     * @return $this
     */
    public function setDebugMode(bool $value = true);

    /**
     * Return if debug mode is enabled or not.
     *
     * @return bool
     */
    public function isDebugMode(): bool;
}
