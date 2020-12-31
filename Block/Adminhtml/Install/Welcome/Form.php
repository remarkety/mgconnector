<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Welcome;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Form extends Container
{
    protected $_coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    public function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Remarkety_Mgconnector';
        $this->_controller = 'adminhtml_install';
        $this->_headerText = __('remarkety test');
        $this->_mode = 'welcome';
        $this->removeButton('reset');
        $this->removeButton('back');
        $this->removeButton('save');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
}
