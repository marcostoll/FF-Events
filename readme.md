FF\Events | Fast Forward Components Collection
===============================================================================

by Marco Stoll

- <marco.stoll@rocketmail.com>
- <http://marcostoll.de>
- <https://github.com/marcostoll>
- <https://github.com/marcostoll/FF-Common>
------------------------------------------------------------------------------------------------------------------------

# What is the Fast Forward Components Collection?
The Fast Forward Components Collection, in short **Fast Forward** or **FF**, is a loosely coupled collection of code 
repositories each addressing common problems while building web application. Multiple **FF** components may be used 
together if desired. And some more complex **FF** components depend on other rather basic **FF** components.

**FF** is not a framework in and of itself and therefore should not be called so. 
But you may orchestrate multiple **FF** components to build an web application skeleton that provides the most common 
tasks.

# Introduction

This package provides a basic observer/observable pattern implementations. The key class is the `EventBroker`. Any
other class may act as an observable by firing events using the `EventBroker`'s api. Other classes my act as observers
by subscribing to specific types of events and being notified by the `EventBroker` in each time this type of event was
fired. 

# The EventBroker

The `EventBroker` provides an api for subscribing observers to specific events. Each subscription as based on a callback
(a callable function/method to invoke by the `EventBroker`) and a class identifier for the event class to listen to.

## Fire Events

If an observable wants to notify potential observers of notable changes it simply fires a suitable event using the
`EventBroker`'s api.

Example:

    use FF\Events\EventBroker;

    class MyExceptionHandler
    {
        /**
         * Generic exception handler callback
         *
         * @param \Throwable $e
         * @see http://php.net/set_exception_handler
         */
        public function onException(\Throwable $e)
        {
            try {
                EventBroker::getInstance()->fire('Runtime\OnException', $e);
            } catch (\Exception $e) {
                // do not handle exceptions thrown while
                // processing the on-exception event
            }
        }
    }
    
The `fire` method of the `EventBroker` uses the `EventsFactory` to instantiate the actual event object passing any
additional argument provided to the event class's constructor.

For additional information on the usage of the **FF** factories and class locators consult the documentation of the
**FF-Factories** component.    

## Subscribe to Events

On valid event handling method must meet the following requirements:

- must be public
- must not be static or abstract
- accept exactly one argument: the event classes instance

Example:

    use FF\Events\EventBroker;
    use FF\Runtime\Events\OnError;

    class MyErrorObserver
    {
        /**
         * Event handling callback
         *
         * @param OnError $event
        public function handleOnRuntimeError(OnError $event)
        {
            // handle the OnError event
            var_dump(
                $event->getErrNo(), 
                $event->getErrMsg(), 
                $event->getErrFile(),
                $event->getErrLine()
            );
        }
    }
    
    // subscribe to the Runtime\OnError event
    $myEventBroker = EventBroker::getInstance()
        ->subscribe([new MyErrorObserver, 'handleOnRuntimeError'], 'Runtime\OnError');
        
The subscription is bases on the class identifier of the event class. This is exactly the same string to use by the
observable when firing the event.        

# Defining Custom Events

Some of the **FF** components make use of the `EventBroker` and define their own events.
If you want to define custom made events within your project you have to make the following two steps:

## Registering your Event Classes

Event classes must be concrete children of `AbstractEvent`. The may accept an arbitrary amount of arguments with their
constructors. This arguments (the event data) must be passed with the call to `EventBroker::fire()` in addition to the
event's class identifier.

The `EventsFactory` uses a pre-configured instance of an `FF\Factories\ClassLocators\NamespacePrefixedClassLocator` to
find the event class definition identified by the class identifier. 

In short, if your project's namespace would be `MyProject\` then your event classes should be stored in 
`MyProject\Events\`.
 
Example:

    namespace MyProject\Events;
    
    use FF\Events\AbstractEvent;
    
    /**
     * This event's class identifier would just be 'OnLogoff'
     */
    class OnLogoff extends AbstractEvent
    {
        /**
         * Define constructor arguments (the event data) to meet your needs.
         */
        public function __construct($eventData)
        {
            var_dump($eventData);
        }
    }
    
In this example you would register your base namespace `MyProject` to the `EventsFactory`.    

Example:

    use FF\Events\EventBroker;
    
    EventBroker::getInstance()
        ->getEventsFactory()
        ->getCLassLocator()
        ->prependNamespaces('MyProject');
        
## Dividing Custom to Sub Namespaces            
        
If your project is composed of multiple packages sharing a common base namespace and you want to distribute your event
definitions their sub package context, then only the event's class identifiers would become a little longer.

Let's assume your project's structure looks something like this:

    |- MyProject\
    |- MyProject\PackageOne\
    |- MyProject\PackageOne\Events\
    |- MyProject\PackageOne\Events\OnLogoff                 -> class identifer: 'PackageOne\OnLogoff'
    |- MyProject\PackageTwo\
    |- MyProject\PackageThree\
    |- MyProject\PackageThree\Events\
    |- MyProject\PackageThree\Events\OnShoppingCartClear    -> class identifer: 'PackageThree\OnShoppingCartClear'
    
Each package which should receive its one event definition must be provided with the `Events\' sub namespace to store
the event definitions of this package.      

Now you would register your project's namespace at the `EventsFactory` just like shown above only using the base
namespace `MyProject`. The class identifiers of your events now are composed of the package name (e.g. `PackageThree`)
and the event's class name (e.g. `OnShoppingCartClear`) divided by an backslash (\).

So use 'PackageThree\OnShoppingCartClear' as class identifier to fire or subscribe to this event.
 
## Overriding FF Events

Because the **FF** namespace class locators defined in **FF-Factories** search their registered namespace in a specific 
order for any suitable class definitions you may also override predefined **FF** component events on demand. You just 
have to mimic the **FF** namespacing.

Example:

    namespace MyProject\Runtime\Events; // same relative namespacing as the

    use FF\Runtime\OnException as FFOnException;
    
    class OnException extends FFOnException
    {
        /**
         * BEWARE: Do not change the constructor signature!
         *
         * @param \Throwable $exception
         */
        public function __construct(\Throwable $exception)
        {
            parent::__constuct($exception);
            
            // add your custom logic
        }
    }
    
Now prepending your project's namespace would lead to the `EventsFactory` searching your namespace first for a class
definition meeting the `Runtime\OnException` event class identifier.
    
    use FF\Events\EventBroker;
        
    EventBroker::getInstance()
        ->getEventsFactory()
        ->getCLassLocator()
        ->prependNamespaces('MyProject'); 
    

# Road map

- mimic ProcessWire's hook implementation