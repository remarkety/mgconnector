<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface StoreOrderStatusesCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\StoreOrderStatusesInterface[]
     */
    public function getItems();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\StoreOrderStatusesInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
