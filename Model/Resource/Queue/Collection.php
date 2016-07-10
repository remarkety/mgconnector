<?php

namespace Remarkety\Mgconnector\Model\Resource\Queue;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Remarkety\Mgconnector\Model\Queue', 'Remarkety\Mgconnector\Model\Resource\Queue');
    }
}