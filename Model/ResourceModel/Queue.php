<?php
namespace Remarkety\Mgconnector\Model\ResourceModel;
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('mgconnector_queue','queue_id');
    }
}
