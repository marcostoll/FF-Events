<?php
/**
 * Definition of EventBroker
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events;

use FF\DataStructures\IndexedCollection;
use FF\DataStructures\OrderedCollection;
use FF\Factories\Exceptions\ClassNotFoundException;

/**
 * Class EventBroker
 *
 * @package FF\Events
 */
class EventBroker
{
    /**
     * @var EventBroker
     */
    protected static $instance;

    /**
     * @var IndexedCollection
     */
    protected $subscriptions;

    /**
     * Initializes the subscriptions collection and the events factory
     *
     * Declared protected to prevent external usage.
     */
    protected function __construct()
    {
        $this->subscriptions = new IndexedCollection();
    }

    /**
     * Declared protected to prevent external usage
     */
    protected function __clone()
    {

    }

    /**
     * Retrieves the singleton instance of this class
     *
     * @return EventBroker
     */
    public static function getInstance(): EventBroker
    {
        if (is_null(self::$instance)) {
            self::$instance = new EventBroker();
        }

        return self::$instance;
    }

    /**
     * @return EventsFactory
     */
    public function getEventsFactory(): EventsFactory
    {
        return EventsFactory::getInstance();
    }

    /**
     * @return IndexedCollection
     */
    public function getSubscriptions(): IndexedCollection
    {
        return $this->subscriptions;
    }

    /**
     * @param string $eventName
     * @return OrderedCollection
     */
    public function getSubscribers(string $eventName): OrderedCollection
    {
        $this->initializeSubscribersCollection($eventName);
        return $this->subscriptions->get($eventName);
    }

    /**
     * Appends a listener to the subscribers list of an event
     *
     * Removes any previous subscriptions of the listener first to the named event.
     *
     * @param callable $listener
     * @param string $eventName
     * @return $this
     */
    public function subscribe(callable $listener, string $eventName)
    {
        $this->unsubscribe($listener, $eventName);
        $this->initializeSubscribersCollection($eventName);

        $this->subscriptions->get($eventName)->push($listener);
        return $this;
    }

    /**
     * Prepends a listener to the subscriber list of an event
     *
     * Removes any previous subscriptions of the listener first to the named event.
     *
     * @param callable $listener
     * @param string $eventName
     * @return $this
     */
    public function subscribeFirst(callable $listener, string $eventName)
    {
        $this->unsubscribe($listener, $eventName);
        $this->initializeSubscribersCollection($eventName);

        $this->subscriptions->get($eventName)->unshift($listener);
        return $this;
    }

    /**
     * Unsubscribes a listener
     *
     * If $name is omitted, the listener will be unsubscribed from each event it was subscribed to.
     *
     * @param callable $listener
     * @param string $eventName
     * @return $this
     */
    public function unsubscribe(callable $listener, string $eventName = null)
    {
        /** @var OrderedCollection $listenerCollection */
        foreach ($this->subscriptions as $name => $listenerCollection) {
            if (!is_null($eventName) && $eventName != $name) continue;

            $index = $listenerCollection->search($listener, true);
            if (is_null($index)) continue;

            // remove listener from event
            unset($listenerCollection[$index]);
        }

        return $this;
    }

    /**
     * Removes all subscriptions for this event
     *
     * @param string $eventName
     * @return $this
     */
    public function unsubscribeAll(string $eventName)
    {
        unset($this->subscriptions[$eventName]);
        return $this;
    }

    /**
     * Checks whether any listeners where subscribed to the named event
     *
     * @param string $eventName
     * @return bool
     */
    public function hasSubscribers(string $eventName): bool
    {
        return $this->subscriptions->has($eventName) && !$this->subscriptions->get($eventName)->isEmpty();
    }

    /**
     * Checks of the listener has been subscribed to the given event
     *
     * @param callable $listener
     * @param string $eventName
     * @return bool
     */
    public function isSubscribed(callable $listener, string $eventName): bool
    {
        if (!$this->hasSubscribers($eventName)) return false;

        return !is_null($this->subscriptions->get($eventName)->search($listener));
    }

    /**Notifies all listeners of events of the given type
     *
     * Listeners will be notified in the order of their subscriptions.
     * Does nothing if no listeners subscribed to the type of the event.
     *
     * Creates an event instance and fires it.
     * Does nothing if no suitable event model could be created.
     *
     * Any given $args will be passed to the constructor of the suitable event
     * model class in the given order.
     *
     * @param string $eventName
     * @param mixed ...$args
     * @return $this
     */
    public function fire(string $eventName, ...$args)
    {
        $event = $this->createEvent($eventName, ...$args);
        if (is_null($event)) return $this;

        foreach ($this->getSubscribers($eventName) as $listener) {
            $this->notify($listener, $event);

            if ($event->isCanceled()) {
                // stop notifying further listeners if event has been canceled
                break;
            }
        }

        return $this;
    }

    /**
     * Initialize listener collection if necessary
     *
     * @param string $eventName
     */
    protected function initializeSubscribersCollection(string $eventName)
    {
        if ($this->subscriptions->has($eventName)) return;

        $this->subscriptions->set($eventName, new OrderedCollection());
    }

    /**
     * Create a fresh event instance
     *
     * @param string $eventName
     * @param mixed ...$args
     * @return AbstractEvent|null
     */
    protected function createEvent(string $eventName, ...$args): ?AbstractEvent
    {
        try {
            return EventsFactory::getInstance()->create($eventName, ...$args);
        } catch (ClassNotFoundException $e) {
            return null;
        }
    }

    /**
     * Passes the event to the listener
     *
     * The listener will be invoked with the event as the first and only argument.
     * Any return values of the listener will be discarded.
     *
     * @param callable $listener
     * @param AbstractEvent $event
     */
    protected function notify(callable $listener, AbstractEvent $event)
    {
        call_user_func($listener, $event);
    }
}