<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface CustomerCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\CustomerInterface[]
     */
    public function getCustomers();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\CustomerInterface[] $items
     * @return $this
     */
    public function setCustomers(array $items = null);
}
