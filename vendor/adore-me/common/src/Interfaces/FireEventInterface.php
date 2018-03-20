<?php
namespace AdoreMe\Common\Interfaces;

use Illuminate\Contracts\Events\Dispatcher;
use AdoreMe\Common\Traits\FireEventTrait;

/**
 * @see FireEventTrait
 */
interface FireEventInterface
{
    /**
     * Pass the dispatcher from given object,
     * to the current object.
     *
     * @see FireEventTrait::inheritDispatcher()
     * @param FireEventInterface $model
     * @return void
     */
    public function inheritDispatcher(FireEventInterface $model);

    /**
     * Set the event dispatcher instance.
     *
     * @see FireEventTrait::setEventDispatcher()
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function setEventDispatcher(Dispatcher $dispatcher);

    /**
     * Get the event dispatcher instance set on the object.
     *
     * @see FireEventTrait::getEventDispatcher()
     * @return null|Dispatcher
     */
    public function getEventDispatcher();
}