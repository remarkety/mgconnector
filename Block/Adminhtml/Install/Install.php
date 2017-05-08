<?php
namespace Remarkety\Mgconnector\Block\Adminhtml\Install;


use Remarkety\Mgconnector\Helper\ConfigHelper;

class Install extends \Magento\Framework\View\Element\Template
{
    public $configHelper;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Remarkety\Mgconnector\Helper\Data $remarketyHelper,
                                ConfigHelper $configHelper){
        parent::__construct($context);
        $this->remarketyHelper = $remarketyHelper;
        $this->configHelper = $configHelper;
    }

    public function getStoresGrid(){
        $block = $this->getLayout()->createBlock('Remarkety\Mgconnector\Block\Adminhtml\Install\Grid');
        return $block->toHtml();
    }

    protected function _toHtml()
    {
        $this->setTemplate('a.phtml');

        return parent::_toHtml();
    }

    public function getHelperMode(){
        return $this->remarketyHelper->getMode();
    }
}
