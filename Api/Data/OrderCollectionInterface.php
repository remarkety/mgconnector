<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface OrderCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\OrderInterface[]
     */
    public function getOrders();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\OrderInterface[] $items
     * @return $this
     */
    public function setOrders(array $items = null);
}