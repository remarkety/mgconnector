<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface CustomerCountCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\CustomerCountInterface
     */
    public function getCount();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\CustomerCountInterface $count
     * @return $this
     */
    public function setCount($count = null);
}
