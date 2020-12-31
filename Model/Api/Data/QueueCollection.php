<?php
namespace Remarkety\Mgconnector\Model\Api\Data;

class QueueCollection implements \Remarkety\Mgconnector\Api\Data\QueueCollectionInterface
{
    private $_items;
    public function getQueueItems()
    {
        return $this->_items;
    }

    public function setQueueItems(array $items = null)
    {
        $this->_items = $items;
    }
}
