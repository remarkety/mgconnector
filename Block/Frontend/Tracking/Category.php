<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use Magento\Catalog\Block\Category\View;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Webtracking;
use Magento\Customer\Model\Session;

class Category extends View {
    private $config_helper;
    private $category;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = [],
        ConfigHelper $config_helper
    ) {
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
        $this->config_helper = $config_helper;
    }

    public function isEventCategoryViewActivated() {
        return $this->config_helper->isEventCategoryViewEnabled();
    }

    public function getCategoryId() {

        return $this->getCategory()->getId();
    }

    public function getCategoryName() {

        return $this->getCategory()->getName();
    }
    
    private function getCategory() {
        if (!$this->category) {
            $this->category = $this->getCurrentCategory();
        }

        return $this->category;
    }
}
