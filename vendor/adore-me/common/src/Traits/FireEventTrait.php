<?php
namespace AdoreMe\Common\Traits;

use AdoreMe\Common\Helpers\ProviderHelper;
use AdoreMe\Common\Interfaces\FireEventInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * @see FireEventInterface
 */
trait FireEventTrait
{
    /** @var bool */
    protected $dispatcherInitialized = false;

    /**
     * The event dispatcher implementation.
     *
     * @var null|Dispatcher
     */
    protected $dispatcher;

    /**
     * Pass the dispatcher from given object,
     * to the current object.
     *
     * @see FireEventInterface::inheritDispatcher()
     * @param FireEventInterface $model
     * @return void
     */
    public function inheritDispatcher(FireEventInterface $model)
    {
        $this->setEventDispatcher($model->getEventDispatcher());
    }

    /**
     * Set the event dispatcher instance.
     *
     * @see FireEventInterface::setEventDispatcher()
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the event dispatcher instance set on the object.
     *
     * @see FireEventInterface::getEventDispatcher()
     * @return null|Dispatcher
     */
    public function getEventDispatcher()
    {
        if (is_null($this->dispatcher) && ! $this->dispatcherInitialized) {
            $this->dispatcher = null;

            // Set the default event dispatcher, if possible.
            if (ProviderHelper::app()->bound(Dispatcher::class)) {
                $this->dispatcher = ProviderHelper::make(Dispatcher::class);
            }

            $this->dispatcherInitialized = true;
        }

        return $this->dispatcher;
    }
}
