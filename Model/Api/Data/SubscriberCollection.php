<?php
namespace Remarkety\Mgconnector\Model\Api\Data;

class SubscriberCollection implements \Remarkety\Mgconnector\Api\Data\SubscriberCollectionInterface
{
    private $_items;

    /**
     * Get subscribers.
     *
     * @return \Remarkety\Mgconnector\Api\Data\SubscriberInterface[]
     */
    public function getSubscribers()
    {
        return $this->_items;
    }

    /**
     * Set subscribers.
     *
     * @param \Remarkety\Mgconnector\Api\Data\SubscriberInterface[] $items
     * @return $this
     */
    public function setSubscribers(array $items = null)
    {
        $this->_items = $items;
        return $this;
    }
}

