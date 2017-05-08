<?php
namespace Remarkety\Mgconnector\Model\ResourceModel\Queue;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Remarkety\Mgconnector\Model\Queue','Remarkety\Mgconnector\Model\ResourceModel\Queue');
    }
}
