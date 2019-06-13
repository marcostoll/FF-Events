<?php
/**
 * Definition of AbstractEvent
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events;

/**
 * Class AbstractEvent
 *
 * @package FF\Events
 */
abstract class AbstractEvent
{
    /**
     * @var bool
     */
    protected $isCanceled = false;

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->isCanceled;
    }

    /**
     * @param bool $isCanceled
     * @return $this
     */
    public function setIsCanceled(bool $isCanceled)
    {
        $this->isCanceled = $isCanceled;
        return $this;
    }

    /**
     * @return $this
     */
    public function cancel()
    {
        return $this->setIsCanceled(true);
    }
}