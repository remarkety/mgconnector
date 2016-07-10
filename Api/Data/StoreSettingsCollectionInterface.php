<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
 */
interface StoreSettingsCollectionInterface
{
    /**
     * Get rules.
     *
     * @return \Remarkety\Mgconnector\Api\Data\StoreSettingsInterface[] $configData
     */
    public function getItems();

    /**
     * Set rules .
     *
     * @param \Remarkety\Mgconnector\Api\Data\StoreSettingsInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}