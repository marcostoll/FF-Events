<?php
/**
 * Definition of EventBrokerTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Events;

use FF\Events\AbstractEvent;
use FF\Events\EventBroker;
use FF\Events\EventsFactory;
use FF\DataStructures\IndexedCollection;
use FF\DataStructures\OrderedCollection;
use PHPUnit\Framework\TestCase;

/**
 * Test EventBrokerTest
 *
 * @package FF\Tests
 */
class EventBrokerTest extends TestCase
{
    /**
     * @var EventBroker
     */
    protected $uut;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->uut = EventBroker::getInstance();
        $this->uut->getEventsFactory()
            ->getClassLocator()
            ->prependNamespaces('FF\Tests');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->uut->unsubscribeAll('EventA');
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSingleton()
    {
        $instanceA = EventBroker::getInstance();
        $instanceB = EventBroker::getInstance();

        $this->assertSame($instanceA, $instanceB);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetEventsFactory()
    {
        $this->assertInstanceOf(EventsFactory::class, $this->uut->getEventsFactory());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetSubscriptions()
    {
        $this->assertInstanceOf(IndexedCollection::class, $this->uut->getSubscriptions());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetSubscribers()
    {
        $this->assertInstanceOf(OrderedCollection::class, $this->uut->getSubscribers('EventA'));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSubscribe()
    {
        $callback = [new ListenerA(), 'shoutA'];

        $same = $this->uut->subscribe($callback, 'EventA');
        $this->assertSame($this->uut, $same);
        $this->assertEquals(1, count($this->uut->getSubscribers('EventA')));
        $this->assertSame($callback, $this->uut->getSubscribers('EventA')->getFirst());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSubscribeRepeated()
    {
        $callback = [new ListenerA(), 'shoutA'];

        $this->uut->subscribe($callback, 'EventA')->subscribe($callback, 'EventA');
        $this->assertEquals(1, count($this->uut->getSubscribers('EventA')));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSubscribeAppend()
    {
        $callback1 = [new ListenerA(), 'shoutA'];
        $callback2 = [new ListenerA(), 'shoutA'];

        $this->uut->subscribe($callback1, 'EventA')
            ->subscribe($callback2, 'EventA');
        $this->assertSame($callback1, $this->uut->getSubscribers('EventA')->get(0));
        $this->assertSame($callback2, $this->uut->getSubscribers('EventA')->get(1));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSubscribeFirst()
    {
        $callback1 = [new ListenerA(), 'shoutA'];
        $callback2 = [new ListenerA(), 'shoutA'];

        $same = $this->uut->subscribe($callback1, 'EventA')
            ->subscribeFirst($callback2, 'EventA');
        $this->assertSame($this->uut, $same);
        $this->assertSame($callback1, $this->uut->getSubscribers('EventA')->get(1));
        $this->assertSame($callback2, $this->uut->getSubscribers('EventA')->get(0));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testUnsubscribe()
    {
        $callback1 = [new ListenerA(), 'shoutA'];

        $this->uut->subscribe($callback1, 'EventA')
            ->subscribe($callback1, 'EventB')
            ->unsubscribe($callback1);
        $this->assertTrue($this->uut->getSubscribers('EventA')->isEmpty());
        $this->assertTrue($this->uut->getSubscribers('EventB')->isEmpty());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testUnsubscribeNamed()
    {
        $callback1 = [new ListenerA(), 'shoutA'];
        $callback2 = [new ListenerA(), 'shoutA'];

        $same = $this->uut->subscribe($callback1, 'EventA')
            ->subscribe($callback2, 'EventA')
            ->unsubscribe($callback1, 'EventA');
        $this->assertSame($this->uut, $same);
        $this->assertEquals(1, count($this->uut->getSubscribers('EventA')));
        $this->assertSame($callback2, $this->uut->getSubscribers('EventA')->get(0));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testUnsubscribeAll()
    {
        $callback1 = [new ListenerA(), 'shoutA'];
        $callback2 = [new ListenerA(), 'shoutA'];

        $same = $this->uut->subscribe($callback1, 'EventA')
            ->subscribe($callback2, 'EventA')
            ->unsubscribeAll('EventA');
        $this->assertSame($this->uut, $same);
        $this->assertTrue($this->uut->getSubscribers('EventA')->isEmpty());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testHasSubscribers()
    {
        $callback1 = [new ListenerA(), 'shoutA'];

        $this->uut->subscribe($callback1, 'EventA');

        $this->assertTrue($this->uut->hasSubscribers('EventA'));
        $this->assertFalse($this->uut->hasSubscribers('EventB'));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testIsSubscribed()
    {
        $callback1 = [new ListenerA(), 'shoutA'];
        $callback2 = [new ListenerA(), 'shoutB'];

        $this->uut->subscribe($callback1, 'EventA');

        $this->assertTrue($this->uut->isSubscribed($callback1, 'EventA'));
        $this->assertFalse($this->uut->isSubscribed($callback1, 'EventB'));
        $this->assertFalse($this->uut->isSubscribed($callback2, 'EventA'));
        $this->assertFalse($this->uut->isSubscribed($callback2, 'EventB'));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testFire()
    {
        $same = $this->uut->fire('EventA', 'foo');
        $this->assertSame($this->uut, $same);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testFireWithListener()
    {
        $this->expectOutputString('foo');

        $this->uut->subscribe([new ListenerA(), 'shoutA'], 'EventA')
            ->fire('EventA', 'foo');
    }

    /**
     * Tests the namesake method/feature
     */
    public function testFireCancel()
    {
        $this->expectOutputString('foo'); // output must be 'foofoo' if event canceling does not work

        $this->uut->subscribe([new ListenerA(), 'cancelA'], 'EventA')
            ->subscribe([new ListenerB(), 'neverToReach'], 'EventA')
            ->fire('EventA', 'foo');
    }
}

class EventA extends AbstractEvent
{
    public $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }
}

class EventB extends EventA
{

}

class ListenerA
{
    public function shoutA(EventA $event)
    {
        print $event->content;
    }

    public function shoutB(EventB $event)
    {
        print $event->content;
    }

    public function cancelA(EventA $event)
    {
        print $event->content;
        $event->cancel();
    }
}

class ListenerB extends ListenerA
{
    public function shoutB(EventB $event)
    {
        print $event->content;
    }

    public function neverToReach(EventA $event)
    {
        print $event->content;
    }
}