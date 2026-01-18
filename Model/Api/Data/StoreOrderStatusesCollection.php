<?php
namespace Remarkety\Mgconnector\Model\Api\Data;

class StoreOrderStatusesCollection implements \Remarkety\Mgconnector\Api\Data\StoreOrderStatusesCollectionInterface
{
    private $_items;
    public function getItems()
    {
        return $this->_items;
    }

    public function setItems(?array $items = null)
    {
        $this->_items = $items;
    }
}
