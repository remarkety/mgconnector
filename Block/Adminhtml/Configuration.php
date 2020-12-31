<?php

namespace Remarkety\Mgconnector\Block\Adminhtml;

use \Magento\Backend\Block\Widget\Form\Container;
use \Magento\Backend\Helper\Data;

class Configuration extends Container
{

    public function _prepareLayout()
    {

        $this->_blockGroup = 'Remarkety_Mgconnector';
        $this->_controller = 'adminhtml_queue';
        $this->_headerText = __('Remarkety Configuration');
        $this->_nameInLayout = 'mgconnectorconfig';

        return parent::_prepareLayout();
    }

    public function getFormHtml()
    {
        $this->getChildBlock('mgconnectorconfig')->setData('action', $this->getSaveUrl());
        return $this->getChildHtml('mgconnectorconfig');
    }

    public function getHeaderHtml()
    {
        return '<h3>test</h3>';
    }
}
