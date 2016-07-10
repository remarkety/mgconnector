<?php
namespace Remarkety\Mgconnector\Model\Api\Data;
class QuoteCollection implements \Remarkety\Mgconnector\Api\Data\QuoteCollectionInterface
{
    private $_items;
    public function getCarts() {
        return $this->_items;
    }

    public function setCarts(array $items = null) {
        $this->_items = $items;
    }
}