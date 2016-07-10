<?php
namespace Remarkety\Mgconnector\Model\Api\Data;
class CustomerCollection implements \Remarkety\Mgconnector\Api\Data\CustomerCollectionInterface
{
    private $_items;
    public function getCustomers() {
        return $this->_items;
    }

    public function setCustomers(array $items = null) {
        $this->_items = $items;
    }
}