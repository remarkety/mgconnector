<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface QueueCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface[]
     */
    public function getQueueItems();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\QueueInterface[] $items
     * @return $this
     */
    public function setQueueItems(array $items = null);
}
