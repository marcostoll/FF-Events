<?php
/**
 * Definition of EventEmitterTrait
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events;

/**
 * Trait EventEmitterTrait
 *
 * Implements the EventEmitterInterface.
 *
 * @package FF\Events
 * @see EventEmitterInterface
 */
trait EventEmitterTrait
{
    /**
     * Creates an event instance and fires it
     *
     * Delegates the execution to the EventBroker provided by the ServiceFactory.
     *
     * @param string $eventName
     * @param mixed ...$args
     * @return $this
     */
    protected function fire(string $eventName, ...$args)
    {
        EventBroker::getInstance()->fire($eventName, ...$args);

        return $this;
    }
}