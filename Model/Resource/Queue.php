<?php

namespace Remarkety\Mgconnector\Model\Resource;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('mgconnector_queue', 'queue_id');
    }
}
