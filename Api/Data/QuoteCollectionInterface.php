<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface QuoteCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\QuoteInterface[]
     */
    public function getCarts();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\QuoteInterface[] $items
     * @return $this
     */
    public function setCarts(array $items = null);
}
