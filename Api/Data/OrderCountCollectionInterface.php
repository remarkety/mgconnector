<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface OrderCountCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\OrderCountInterface
     */
    public function getCount();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\OrderCountInterface $count
     * @return $this
     */
    public function setCount($count = null);
}