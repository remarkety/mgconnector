<?php

namespace Remarkety\Mgconnector\Block\Adminhtml;

class Queue extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct() {
        $this->_controller = 'adminhtml_queue';
        $this->_blockGroup = 'Remarkety_Mgconnector';
        parent::_construct();
        $this->removeButton('add');
    }

    public function getButtonsHtml($region = null)
    {
        return;
    }
}
