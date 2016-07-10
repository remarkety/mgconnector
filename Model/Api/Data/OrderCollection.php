<?php
namespace Remarkety\Mgconnector\Model\Api\Data;
class OrderCollection implements \Remarkety\Mgconnector\Api\Data\OrderCollectionInterface
{
    private $_items;
    public function getOrders() {
        return $this->_items;
    }

    public function setOrders(array $items = null) {
        $this->_items = $items;
    }
}