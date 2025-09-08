<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a collection of subscribers
 */
interface SubscriberCollectionInterface
{
    /**
     * Get subscribers.
     *
     * @return \Remarkety\Mgconnector\Api\Data\SubscriberInterface[]
     */
    public function getSubscribers();

    /**
     * Set subscribers.
     *
     * @param \Remarkety\Mgconnector\Api\Data\SubscriberInterface[] $items
     * @return $this
     */
    public function setSubscribers(array $items = null);
}

