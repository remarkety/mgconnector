<?php
namespace Remarkety\Mgconnector\Block\Adminhtml\Install;


class Install extends \Magento\Framework\View\Element\Template
{
    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Remarkety\Mgconnector\Helper\Data $remarketyHelper){
        parent::__construct($context);
        $this->remarketyHelper = $remarketyHelper;
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