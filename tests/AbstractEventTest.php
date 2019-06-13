<?php
/**
 * Definition of AbstractEventTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Events;

use FF\Events\AbstractEvent;
use PHPUnit\Framework\TestCase;

/**
 * Test AbstractEventTest
 *
 * @package FF\Tests
 */
class AbstractEventTest extends TestCase
{
    /**
     * @var MyEvent
     */
    protected $uut;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->uut = new MyEvent();
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSetGetIsCanceled()
    {
        $same = $this->uut->setIsCanceled(true);
        $this->assertSame($this->uut, $same);
        $this->assertTrue($this->uut->isCanceled());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testCancel()
    {
        $same = $this->uut->cancel();
        $this->assertSame($this->uut, $same);
        $this->assertTrue($this->uut->isCanceled());
    }
}

class MyEvent extends AbstractEvent
{

}