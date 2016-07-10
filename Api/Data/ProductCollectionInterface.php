<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface ProductCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\ProductInterface
     */
    public function getProducts();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\ProductInterface $items
     * @return $this
     */
    public function setProducts($items = null);
}