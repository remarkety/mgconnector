<?php
namespace Remarkety\Mgconnector\Block\Adminhtml\Settings;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Webtracking;
use Magento\Customer\Model\Session;

class Form extends \Magento\Framework\View\Element\Template {
    private $formKey;
    private $attributesCollection;
    private $configHelper;

    private $current_pos_id;
    public function __construct(
        Template\Context $context,
        array $data,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Model\ResourceModel\Attribute\Collection $attributesCollection,
        ConfigHelper $configHelper
    )
    {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->attributesCollection = $attributesCollection;
        $this->configHelper = $configHelper;
        $this->current_pos_id = $configHelper->getPOSAttributeCode();
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getPosIdOptions(){
        $attribute_data = [];
        foreach ($this->attributesCollection as $item) {
            $name = $item->getFrontendLabel();
            if(empty($name)){
                continue;
            }
            $attribute_data[$item->getAttributeCode()] = $name;
        }
        return $attribute_data;
    }

    public function getCurrentPOSCode(){
        return $this->current_pos_id;
    }
}
