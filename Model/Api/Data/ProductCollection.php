<?php
namespace Remarkety\Mgconnector\Model\Api\Data;

class ProductCollection implements \Remarkety\Mgconnector\Api\Data\ProductCollectionInterface
{
    private $_items;
    public function getProducts()
    {
        return $this->_items;
    }

    public function setProducts($items = null)
    {
        $this->_items = $items;
    }
}
