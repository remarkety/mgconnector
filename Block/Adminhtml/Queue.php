<?php

namespace Remarkety\Mgconnector\Block\Adminhtml;

class Queue extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct() {
        $this->_controller = 'adminhtml_queue';
        $this->_blockGroup = 'Remarkety_Mgconnector';
        $this->removeButton('add_button');
        parent::_construct();
    }

    public function getButtonsHtml($region = null)
    {
        return;
    }
}
