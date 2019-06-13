<?php
/**
 * Definition of EventsFactory
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events;

use FF\Factories\AbstractFactory;
use FF\Factories\ClassLocators\ClassLocatorInterface;
use FF\Factories\ClassLocators\NamespacePrefixedClassLocator;

/**
 * Class EventsFactory
 *
 * @package FF\Events
 */
class EventsFactory extends AbstractFactory
{
    /**
     * @var EventsFactory
     */
    protected static $instance;

    /**
     * Declared protected to prevent external usage.
     * Uses a NamespacePrefixedClassLocator pre-configured with 'Events' prefix and the FF namespace.
     * @see \FF\Factories\ClassLocators\NamespacePrefixedClassLocator
     */
    protected function __construct()
    {
        parent::__construct(new NamespacePrefixedClassLocator('Events', 'FF'));
    }

    /**
     * Declared protected to prevent external usage
     */
    protected function __clone()
    {

    }

    /**
     * {@inheritDoc}
     * @return NamespacePrefixedClassLocator
     */
    public function getClassLocator(): ClassLocatorInterface
    {
        return parent::getClassLocator();
    }

    /**
     * Retrieves the singleton instance of this class
     *
     * @return EventsFactory
     */
    public static function getInstance(): EventsFactory
    {
        if (is_null(self::$instance)) {
            self::$instance = new EventsFactory();
        }

        return self::$instance;
    }

    /**
     * {@inheritdoc}
     * @return AbstractEvent
     */
    public function create(string $classIdentifier, ...$args)
    {
        /** @var AbstractEvent $event */
        $event = parent::create($classIdentifier, ...$args);
        return $event;
    }
}